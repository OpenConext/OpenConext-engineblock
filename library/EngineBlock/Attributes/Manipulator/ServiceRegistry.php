<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Entity\Disassembler\CortoDisassembler;

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
        ServiceProvider $serviceProvider,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $requestObj
    ) {
        $manipulationCode = $entity->getManipulation();
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
            $spMetadataLegacy,
            $requestObj
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
        array $spMetadata,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $requestObj
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
                    $spMetadata,
                    $requestObj
            ) {
                eval($manipulationCode);
            },
            // Should an error occur, log the input, if nothing happens, then don't
            function($exception)
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
     * @return \OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface
     */
    protected function _getMetadataRepository()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getMetadataRepository();
    }
}
