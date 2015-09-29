<?php
use OpenConext\Component\EngineBlockMetadata\Entity\AbstractRole;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\Disassembler\CortoDisassembler;

class EngineBlock_Attributes_Manipulator_ServiceRegistry
{
    const TYPE_SP  = 'sp';
    const TYPE_IDP = 'idp';

    protected $_entityType;

    function __construct($entityType)
    {
        $this->_entityType = $entityType;
    }

    public function manipulate(
        AbstractRole $entity,
        &$subjectId,
        array &$attributes,
        EngineBlock_Saml2_ResponseAnnotationDecorator &$responseObj,
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ) {
        $manipulationCode = $this->_getMetadataRepository()->fetchEntityManipulation($entity);
        if (empty($manipulationCode)) {
            return false;
        }

        // Note that this can be removed when all references to the old format have been removed from
        // the attribute manipulations.
        $translator = new EngineBlock_Corto_Mapper_Legacy_ResponseTranslator();
        $response = $translator->fromNewFormat($responseObj);

        $metadataTranslator = new CortoDisassembler();
        $idpMetadataLegacy = $metadataTranslator->translateIdentityProvider($identityProvider);
        $spMetadataLegacy  = $metadataTranslator->translateServiceProvider($serviceProvider);

        $this->_doManipulation(
            $manipulationCode,
            $entity->entityId,
            $subjectId,
            $attributes,
            $response,
            $responseObj,
            $idpMetadataLegacy,
            $spMetadataLegacy
        );

        $responseObj = $translator->fromOldFormat($response);
        return true;
    }

    protected function _doManipulation(
        $manipulationCode,
        $entityId,
        &$subjectId,
        array &$attributes,
        array &$response,
        EngineBlock_Saml2_ResponseAnnotationDecorator $responseObj,
        array $idpMetadata,
        array $spMetadata
    ) {
        $entityType = $this->_entityType;

        EngineBlock_ApplicationSingleton::getInstance()->getErrorHandler()->withExitHandler(
            // Try
            function()
                use (
                    $manipulationCode,
                    $entityId,
                    &$subjectId,
                    &$attributes,
                    &$response,
                    $responseObj,
                    $idpMetadata,
                    $spMetadata
            ) {
                eval($manipulationCode);
            },
            // Should an error occur, log the input, if nothing happens, then don't
            function(EngineBlock_Exception $exception)
                use (
                    $entityType,
                    $manipulationCode,
                    $entityId,
                    $subjectId,
                    $attributes,
                    $response,
                    $responseObj,
                    $idpMetadata,
                    $spMetadata
            ) {
                EngineBlock_ApplicationSingleton::getLog()->error(
                    'An error occurred while running service registry manipulation code',
                    array(
                        'manipulation_code' => array(
                            'EntityID'          => $entityId,
                            'Manipulation code' => $manipulationCode,
                            'Subject NameID'    => $subjectId,
                            'Attributes'        => $attributes,
                            'Response'          => $response,
                            'IdPMetadata'       => $idpMetadata,
                            'SPMetadata'        => $spMetadata,
                        )
                    )
                );
                if ($entityType === 'sp') {
                    $exception->spEntityId = $entityId;
                }
                else if ($entityType === 'idp') {
                    $exception->idpEntityId = $entityId;
                }
                $exception->userId = $subjectId;
                $exception->description = $entityType;
            }
        );
    }

    /**
     * @return \OpenConext\Component\EngineBlockMetadata\MetadataRepository\MetadataRepositoryInterface
     */
    protected function _getMetadataRepository()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getMetadataRepository();
    }
}
