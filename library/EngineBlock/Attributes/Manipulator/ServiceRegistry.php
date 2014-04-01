<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

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
        $entityId,
        &$subjectId,
        array &$attributes,
        EngineBlock_Saml2_ResponseAnnotationDecorator &$responseObj,
        array $idpMetadata,
        array $spMetadata
    ) {
        $entity = $this->_getServiceRegistryAdapter()->getEntity($entityId);
        if (empty($entity['manipulation'])) {
            return false;
        }

        // Note that this can be removed when all references to the old format have been removed from
        // the attribute manipulations.
        $translator = new EngineBlock_Corto_Mapper_Legacy_ResponseTranslator();
        $response = $translator->fromNewFormat($responseObj);

        $this->_doManipulation(
            $entity['manipulation'],
            $entityId,
            $subjectId,
            $attributes,
            $response,
            $responseObj,
            $idpMetadata,
            $spMetadata
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

    protected function _getServiceRegistryAdapter()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getServiceRegistryAdapter();
    }
}
