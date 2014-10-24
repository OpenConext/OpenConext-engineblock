<?php
use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProviderEntity;
use OpenConext\Component\EngineBlockMetadata\Legacy\EntityTranslator;

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
        AbstractConfigurationEntity $entity,
        &$subjectId,
        array &$attributes,
        EngineBlock_Saml2_ResponseAnnotationDecorator &$responseObj,
        IdentityProviderEntity $identityProvider,
        ServiceProviderEntity $serviceProvider
    ) {
        $manipulationCode = $this->_getMetadataRepository()->fetchEntityManipulation($entity);
        if (empty($manipulationCode)) {
            return false;
        }

        // Note that this can be removed when all references to the old format have been removed from
        // the attribute manipulations.
        $translator = new EngineBlock_Corto_Mapper_Legacy_ResponseTranslator();
        $response = $translator->fromNewFormat($responseObj);

        $metadataTranslator = new EntityTranslator();
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
                EngineBlock_ApplicationSingleton::getLog()->attach(
                    array(
                        'EntityID'          => $entityId,
                        'Manipulation code' => $manipulationCode,
                        'Subject NameID'    => $subjectId,
                        'Attributes'        => $attributes,
                        'Response'          => $response,
                        'IdPMetadata'       => $idpMetadata,
                        'SPMetadata'        => $spMetadata,
                    ),
                    'manipulation data'
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
     * @return \OpenConext\Component\EngineBlockMetadata\Entity\MetadataRepository\MetadataRepositoryInterface
     */
    protected function _getMetadataRepository()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getMetadataRepository();
    }
}
