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

class EngineBlock_Corto_ServiceRegistry_Adapter
{
    /**
     * @var Janus_Client
     */
    protected $_serviceRegistry;

    public function __construct($serviceRegistry)
    {
        $this->_serviceRegistry = $serviceRegistry;
    }

    /**
     * Given a list of (SAML2) entities, filter out the idps that are not allowed
     * for the given Service Provider.
     *
     * @param array $entities
     * @param string $spEntityId
     * @return array Filtered entities
     */
    public function filterEntitiesBySp(array $entities, $spEntityId)
    {
        $allowedEntities = $this->_serviceRegistry->getAllowedIdps($spEntityId);
        foreach ($entities as $entityId => $entityData) {
            if (isset($entityData['SingleSignOnService'])) {
                // entity is an idp
                if (in_array($entityId, $allowedEntities)) {
                    $entities[$entityId]['Access'] = true;
                } else {
                    unset($entities[$entityId]);
                }
            }
        }
        return $entities;
    }

    /**
     * Given a list of (SAML2) entities, mark those idps that are not allowed
     * for the given Service Provider.
     *
     * @param array $entities
     * @param string $spEntityId
     * @return array the entities
     */
    public function markEntitiesBySp(array $entities, $spEntityId)
    {
        $allowedEntities = $this->_serviceRegistry->getAllowedIdps($spEntityId);
        foreach ($entities as $entityId => $entityData) {
            if (isset($entityData['SingleSignOnService'])) {
                // entity is an idp
                $entities[$entityId]['Access'] = in_array($entityId, $allowedEntities);
            }
        }
        return $entities;
    }

    /**
     * Given a list of (SAML2) entities, filter out the entities that do not have the requested workflow state
     *
     * @param array $entities
     * @param string $workflowState
     * @return array Filtered entities
     */
    public function filterEntitiesByWorkflowState(array $entities, $workflowState) {
        foreach ($entities as $entityId => $entityData) {
            if (!isset($entityData['WorkflowState']) || $entityData['WorkflowState'] !== $workflowState) {
                unset($entities[$entityId]);
            }
        }

        return $entities;
    }

    public function isConnectionAllowed($spEntityId, $idpEntityId)
    {
        return $this->_serviceRegistry->isConnectionAllowed($spEntityId, $idpEntityId);
    }

    public function getRemoteMetaData()
    {
        return $this->_getRemoteSPsMetaData() + $this->_getRemoteIdPsMetadata();
    }

    public function getEntity($entityId)
    {
        return $this->_serviceRegistry->getEntity($entityId);
    }

    public function getArp($spEntityId)
    {
        return $this->_serviceRegistry->getArp($spEntityId);
    }

    protected function _getRemoteIdPsMetadata()
    {
        $metadata = array();
        $idPs = $this->_serviceRegistry->getIdpList();
        foreach ($idPs as $idPEntityId => $idP) {
            try {
                $idP = self::convertServiceRegistryEntityToCortoEntity($idP);
                $idP['EntityID'] = $idPEntityId;
                $metadata[$idPEntityId] = $idP;
            } catch (Exception $e) {
                // Whoa, something went wrong trying to convert the SR entity to a Corto entity
                // We can't use this entity, but we can continue after we've reported
                // this serious error
                $application = EngineBlock_ApplicationSingleton::getInstance();
                $application->reportError($e);
                continue;
            }
        }
        return $metadata;
    }

    protected function _getRemoteSPsMetaData()
    {
        $metadata = array();
        $sPs = $this->_serviceRegistry->getSPList();
        foreach ($sPs as $spEntityId => $sp) {
            try {
                $sp = self::convertServiceRegistryEntityToCortoEntity($sp);
                $sp['EntityID'] = $spEntityId;
                $metadata[$spEntityId] = $sp;
            } catch (Exception $e) {
                // Whoa, something went wrong trying to convert the SR entity to a Corto entity
                // We can't use this entity, but we can continue after we've reported
                // this serious error
                $application = EngineBlock_ApplicationSingleton::getInstance();
                $application->reportError($e);
                continue;
            }
        }
        return $metadata;
    }

    protected static function convertServiceRegistryEntityToCortoEntity($serviceRegistryEntity)
    {
        $cortoEntity = array();

        // Publish in edugain
        if (isset($serviceRegistryEntity['coin:publish_in_edugain'])) {
            $cortoEntity['PublishInEdugain'] = $serviceRegistryEntity['coin:publish_in_edugain'];
        }

        // Disable SAML scoping
        if (isset($serviceRegistryEntity['coin:disable_scoping'])) {
            $cortoEntity['DisableScoping'] = $serviceRegistryEntity['coin:disable_scoping'];
        }

        // Enable additional logging
        if (isset($serviceRegistryEntity['coin:additional_logging'])) {
            $cortoEntity['AdditionalLogging'] = $serviceRegistryEntity['coin:additional_logging'];
        }

        // For SPs
        if (isset($serviceRegistryEntity['AssertionConsumerService:0:Location'])) {
            // Transparant issuer
            if (isset($serviceRegistryEntity['coin:transparant_issuer'])) {
                $cortoEntity['TransparantIssuer'] = $serviceRegistryEntity['coin:transparant_issuer'];
            }

            // implicit vo
            if (isset($serviceRegistryEntity['coin:implicit_vo_id'])) {
                $cortoEntity['VoContext'] = $serviceRegistryEntity['coin:implicit_vo_id'];
            }

            // show all IdP's in the WAYF
            if (isset($serviceRegistryEntity['coin:display_unconnected_idps_wayf'])) {
                $cortoEntity['DisplayUnconnectedIdpsWayf'] = $serviceRegistryEntity['coin:display_unconnected_idps_wayf'];
            }

            $cortoEntity['AssertionConsumerServices'] = array();
            for ($i = 0; $i < 10; $i++) {
                if (isset($serviceRegistryEntity["AssertionConsumerService:$i:Binding"]) &&
                    isset($serviceRegistryEntity["AssertionConsumerService:$i:Location"])) {
                    $cortoEntity['AssertionConsumerServices'][$i] = array(
                        'Binding'  => $serviceRegistryEntity["AssertionConsumerService:$i:Binding"],
                        'Location' => $serviceRegistryEntity["AssertionConsumerService:$i:Location"]
                    );
                }
            }

            // Only for SPs
            if (isset($serviceRegistryEntity['coin:alternate_private_key']) && $serviceRegistryEntity['coin:alternate_private_key']) {
                $cortoEntity['AlternatePrivateKey'] = EngineBlock_X509Certificate::getPrivatePemCertFromCertData(
                    $serviceRegistryEntity['coin:alternate_private_key']
                );
            }
            if (isset($serviceRegistryEntity['coin:alternate_public_key']) && $serviceRegistryEntity['coin:alternate_public_key']) {
                $cortoEntity['AlternatePublicKey'] = EngineBlock_X509Certificate::getPublicPemCertFromCertData(
                    $serviceRegistryEntity['coin:alternate_public_key']
                );
            }

            // External provisioning
            $cortoEntity['MustProvisionExternally'] = FALSE;
            if (isset($serviceRegistryEntity['coin:is_provision_sp']) && $serviceRegistryEntity['coin:is_provision_sp']) {
                $cortoEntity['MustProvisionExternally'] = TRUE;

                if (isset($serviceRegistryEntity['coin:provision_type'])) {
                    $cortoEntity['ExternalProvisionType'] = $serviceRegistryEntity['coin:provision_type'];
                }
                if (isset($serviceRegistryEntity['coin:provision_domain'])) {
                    $cortoEntity['ExternalProvisionDomain'] = $serviceRegistryEntity['coin:provision_domain'];
                }
                if (isset($serviceRegistryEntity['coin:provision_admin'])) {
                    $cortoEntity['ExternalProvisionAdmin'] = $serviceRegistryEntity['coin:provision_admin'];
                }
                if (isset($serviceRegistryEntity['coin:provision_password'])) {
                    $cortoEntity['ExternalProvisionPassword'] = $serviceRegistryEntity['coin:provision_password'];
                }
                if (isset($serviceRegistryEntity['coin:is_provision_sp_groups'])) {
                    $cortoEntity['ExternalProvisionGroups'] = $serviceRegistryEntity['coin:is_provision_sp_groups'];
                } else {
                    //default we will provision groups
                    $cortoEntity['ExternalProvisionGroups'] = true;
                }
            }

            // Global consent disabling
            if (isset($serviceRegistryEntity['coin:no_consent_required']) && $serviceRegistryEntity['coin:no_consent_required']) {
                $cortoEntity['NoConsentRequired'] = TRUE;
            }

            if (isset($serviceRegistryEntity['coin:eula']) && $serviceRegistryEntity['coin:eula']) {
                $cortoEntity['Eula'] = $serviceRegistryEntity['coin:eula'];
            }

            $cortoEntity['ProvideIsMemberOf'] = !empty($serviceRegistryEntity['coin:provide_is_member_of']);

            if (isset($serviceRegistryEntity['coin:do_not_add_attribute_aliases']) && $serviceRegistryEntity['coin:do_not_add_attribute_aliases']) {
                $cortoEntity['SkipDenormalization'] = TRUE;
            }
        }

        // For Idps
        if (isset($serviceRegistryEntity['SingleSignOnService:0:Location'])) {
            $cortoEntity['SingleSignOnService'] = array();

            // Map one or more services
            $serviceIndex = 0;
            while(isset($serviceRegistryEntity["SingleSignOnService:{$serviceIndex}:Binding"]) &&
                isset($serviceRegistryEntity["SingleSignOnService:{$serviceIndex}:Location"]) ) {
                $cortoEntity['SingleSignOnService'][] = array(
                    'Binding'   => $serviceRegistryEntity["SingleSignOnService:{$serviceIndex}:Binding"],
                    'Location'  => $serviceRegistryEntity["SingleSignOnService:{$serviceIndex}:Location"],
                );
                $serviceIndex++;
            }

            // Only for IdPs
            $cortoEntity['GuestQualifier'] = 'All';
            if (isset($serviceRegistryEntity['coin:guest_qualifier'])) {
                if (in_array($serviceRegistryEntity['coin:guest_qualifier'], array('All', 'Some', 'None'))) {
                    $cortoEntity['GuestQualifier'] = $serviceRegistryEntity['coin:guest_qualifier'];
                }
            }

            if (isset($serviceRegistryEntity['coin:schachomeorganization'])) {
                $cortoEntity['SchacHomeOrganization'] = $serviceRegistryEntity['coin:schachomeorganization'];
            }

            // Per SP consent disabling
            $cortoEntity['SpsWithoutConsent'] = array();
            $i = 0;
            while(isset($serviceRegistryEntity["disableConsent:$i"])) {
                $cortoEntity['SpsWithoutConsent'][] = $serviceRegistryEntity["disableConsent:$i"];
                $i++;
            }

            $cortoEntity['isHidden'] = (isset($serviceRegistryEntity['coin:hidden']) && $serviceRegistryEntity['coin:hidden'] === true);
        }

        // In general
        if (isset($serviceRegistryEntity['certData']) && $serviceRegistryEntity['certData']) {
            $cortoEntity['certificates'] = array(
                'public' => EngineBlock_X509Certificate::getPublicPemCertFromCertData($serviceRegistryEntity['certData']),
            );
            if (isset($serviceRegistryEntity['certData2']) && $serviceRegistryEntity['certData2']) {
                $cortoEntity['certificates']['public-fallback'] = EngineBlock_X509Certificate::getPublicPemCertFromCertData(
                    $serviceRegistryEntity['certData2']
                );
            }
        }

        self::_multiLang($cortoEntity, $serviceRegistryEntity, array(
            'name'          => 'Name',
            'description'   => 'Description',
            'displayName'   => 'DisplayName',
        ));


        if (isset($serviceRegistryEntity['logo:0:url'])) {
            $cortoEntity['Logo'] = array(
                'Height' => $serviceRegistryEntity['logo:0:height'],
                'Width'  => $serviceRegistryEntity['logo:0:width'],
                'URL'    => $serviceRegistryEntity['logo:0:url'],
            );
        }
        if (isset($serviceRegistryEntity['redirect.sign'])) {
            $cortoEntity['AuthnRequestsSigned'] = (bool)$serviceRegistryEntity['redirect.sign'];
        }

        // Organization info
        $cortoEntity['Organization'] = array();
        self::_multiLang($cortoEntity['Organization'], $serviceRegistryEntity, array(
            'OrganizationName'         => 'Name',
            'OrganizationDisplayName'  => 'DisplayName',
            'OrganizationURL'          => 'URL',
        ));
        if (empty($cortoEntity['Organization'])) {
            unset($cortoEntity['Organization']);
        }

        // Keywords for searching in the WAYF
        self::_multiLang(
            $cortoEntity,
            $serviceRegistryEntity,
            array('keywords' => 'Keywords')
        );

        if (isset($serviceRegistryEntity['SingleLogoutService_Binding']) &&
            isset($serviceRegistryEntity['SingleLogoutService_Location'])) {
            $cortoEntity['SingleLogoutService'] = array(
                array(
                    'Binding' => $serviceRegistryEntity['SingleLogoutService_Binding'],
                    'Location' => $serviceRegistryEntity['SingleLogoutService_Location']
                )
            );
        }

        if (isset($serviceRegistryEntity['NameIDFormat'])) {
            $cortoEntity['NameIDFormat'] = $serviceRegistryEntity['NameIDFormat'];
        }

        $cortoEntity['NameIDFormats'] = array();
        if (isset($serviceRegistryEntity['NameIDFormats:0'])) {
            $cortoEntity['NameIDFormats'][] = $serviceRegistryEntity['NameIDFormats:0'];
        }
        if (isset($serviceRegistryEntity['NameIDFormats:1'])) {
            $cortoEntity['NameIDFormats'][] = $serviceRegistryEntity['NameIDFormats:1'];
        }
        if (isset($serviceRegistryEntity['NameIDFormats:2'])) {
            $cortoEntity['NameIDFormats'][] = $serviceRegistryEntity['NameIDFormats:2'];
        }

        if (empty($cortoEntity['NameIDFormats'])) {
            $cortoEntity['NameIDFormats'] = array(
                EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_TRANSIENT,
                EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_PERSISTENT
            );
        }

        // Contacts
        $cortoEntity['ContactPersons'] = array();
        for ($i = 0; $i < 3; $i++) {
            if (isset($serviceRegistryEntity["contacts:$i:contactType"])) {
                $contactPerson = array(
                    'ContactType'   => $serviceRegistryEntity["contacts:$i:contactType"],
                    'EmailAddress'  => isset($serviceRegistryEntity["contacts:$i:emailAddress"])? $serviceRegistryEntity["contacts:$i:emailAddress"] : '',
                    'GivenName'     => isset($serviceRegistryEntity["contacts:$i:givenName"])   ? $serviceRegistryEntity["contacts:$i:givenName"] : '',
                    'SurName'       => isset($serviceRegistryEntity["contacts:$i:surName"])     ? $serviceRegistryEntity["contacts:$i:surName"] : '',
                );
                $cortoEntity['ContactPersons'][$i] = $contactPerson;
            }
        }
        if (empty($cortoEntity['ContactPersons'])) {
            unset($cortoEntity['ContactPersons']);
        }

        if (isset($serviceRegistryEntity['workflowState'])) {
            $cortoEntity['WorkflowState'] = $serviceRegistryEntity['workflowState'];
        }

        return $cortoEntity;
    }

    protected static function _multiLang(&$cortoEntity, $serviceRegistryEntity, $mapping)
    {
        foreach ($mapping as $from => $to) {
            $hasEnglish = isset($serviceRegistryEntity[$from . ':en']);
            $hasDutch   = isset($serviceRegistryEntity[$from . ':nl']);
            if ($hasDutch || $hasEnglish) {
                $cortoEntity[$to] = array();
                if ($hasDutch) {
                    $cortoEntity[$to]['nl'] = $serviceRegistryEntity[$from . ':nl'];
                }
                if ($hasEnglish) {
                    $cortoEntity[$to]['en'] = $serviceRegistryEntity[$from . ':en'];
                }
            }
        }
    }
}