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
                if (!in_array($entityId, $allowedEntities)) {
                    unset($entities[$entityId]);
                }
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

    protected function _getRemoteIdPsMetadata()
    {
        $metadata = array();
        $idPs = $this->_serviceRegistry->getIdpList();
        foreach ($idPs as $idPEntityId => $idP) {
            try {
                $metadata[$idPEntityId] = self::convertServiceRegistryEntityToCortoEntity($idP);
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
        foreach ($sPs as $sPEntityId => $sP) {
            try {
                $metadata[$sPEntityId] = self::convertServiceRegistryEntityToCortoEntity($sP);
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
        $serviceRegistryEntity = self::convertServiceRegistryEntityToMultiDimensionalArray($serviceRegistryEntity);
        $cortoEntity = array();

        // For SPs
        if (isset($serviceRegistryEntity['AssertionConsumerService'][0]['Location'])) {
            $cortoEntity['WantsAssertionsSigned'] = true;

            $cortoEntity['AssertionConsumerService'] = array(
                'Binding'  => $serviceRegistryEntity['AssertionConsumerService'][0]['Binding'],
                'Location' => $serviceRegistryEntity['AssertionConsumerService'][0]['Location'],
            );

            if (isset($serviceRegistryEntity['coin']['default_vo_id'])) {
                $cortoEntity['VoContext'] = $serviceRegistryEntity['coin']['default_vo_id'];
            }

            // Only for SPs
            if (isset($serviceRegistryEntity['coin']['alternate_private_key']) && $serviceRegistryEntity['coin']['alternate_private_key']) {
                $cortoEntity['AlternatePrivateKey'] = EngineBlock_X509Certificate::getPrivatePemCertFromCertData(
                    $serviceRegistryEntity['coin']['alternate_private_key']
                );
            }
            if (isset($serviceRegistryEntity['coin']['alternate_public_key']) && $serviceRegistryEntity['coin']['alternate_public_key']) {
                $cortoEntity['AlternatePublicKey'] = EngineBlock_X509Certificate::getPublicPemCertFromCertData(
                    $serviceRegistryEntity['coin']['alternate_public_key']
                );
            }

            // External provisioning
            $cortoEntity['MustProvisionExternally'] = false;
            if (isset($serviceRegistryEntity['coin']['is_provision_sp']) && $serviceRegistryEntity['coin']['is_provision_sp']) {
                $cortoEntity['MustProvisionExternally'] = true;

                if (isset($serviceRegistryEntity['coin']['provision_type'])) {
                    $cortoEntity['ExternalProvisionType'] = $serviceRegistryEntity['coin']['provision_type'];
                }
                if (isset($serviceRegistryEntity['coin']['provision_domain'])) {
                    $cortoEntity['ExternalProvisionDomain'] = $serviceRegistryEntity['coin']['provision_domain'];
                }
                if (isset($serviceRegistryEntity['coin']['provision_admin'])) {
                    $cortoEntity['ExternalProvisionAdmin'] = $serviceRegistryEntity['coin']['provision_admin'];
                }
                if (isset($serviceRegistryEntity['coin']['provision_password'])) {
                    $cortoEntity['ExternalProvisionPassword'] = $serviceRegistryEntity['coin']['provision_password'];
                }
            }
        }

        // For Idps
        if (isset($serviceRegistryEntity['SingleSignOnService'][0]['Location'])) {
            $cortoEntity['SingleSignOnService'] = array(
                'Binding'   => $serviceRegistryEntity['SingleSignOnService'][0]['Binding'],
                'Location'  => $serviceRegistryEntity['SingleSignOnService'][0]['Location'],
            );

            // Only for IdPs
            $cortoEntity['GuestQualifier'] = 'All';
            if (isset($serviceRegistryEntity['coin']['guest_qualifier'])) {
                if (in_array($serviceRegistryEntity['coin']['guest_qualifier'], array('All', 'Some', 'None'))) {
                    $cortoEntity['GuestQualifier'] = $serviceRegistryEntity['coin']['guest_qualifier'];
                }
            }
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
        if (isset($serviceRegistryEntity['name'])) {
            $cortoEntity['Name'] = $serviceRegistryEntity['name'];
        }
        if (isset($serviceRegistryEntity['description'])) {
            $cortoEntity['Description'] = $serviceRegistryEntity['description'];
        }
        if (isset($serviceRegistryEntity['displayName'])) {
            $cortoEntity['DisplayName'] = $serviceRegistryEntity['displayName'];
        }
        if (isset($serviceRegistryEntity['logo'][0]['url'])) {
            $cortoEntity['Logo'] = array(
                'Height' => $serviceRegistryEntity['logo'][0]['height'],
                'Width'  => $serviceRegistryEntity['logo'][0]['width'],
                'URL'    => $serviceRegistryEntity['logo'][0]['url'],
            );
        }
        if (isset($serviceRegistryEntity['geoLocation'])) {
            $cortoEntity['GeoLocation'] = $serviceRegistryEntity['geoLocation'];
        }
        if (isset($serviceRegistryEntity['redirect']['sign'])) {
            $cortoEntity['AuthnRequestsSigned'] = (bool)$serviceRegistryEntity['redirect']['sign'];
        }
        if (isset($serviceRegistryEntity['organization']['OrganizationName'])) {
            $cortoEntity['Organization']['Name'] = $serviceRegistryEntity['organization']['OrganizationName'];
        }
        if (isset($serviceRegistryEntity['organization']['OrganizationDisplayName'])) {
            $cortoEntity['Organization']['DisplayName'] = $serviceRegistryEntity['organization']['OrganizationDisplayName'];
        }
        if (isset($serviceRegistryEntity['organization']['OrganizationURL'])) {
            $cortoEntity['Organization']['URL'] = $serviceRegistryEntity['organization']['OrganizationURL'];
        }
        // The Keywords for the WAYF
        if (isset($serviceRegistryEntity['keywords'])) {
            $cortoEntity['Keywords'] = $serviceRegistryEntity['keywords'];
        }
        if (isset($serviceRegistryEntity['NameIDFormat'])) {
            $cortoEntity['NameIDFormat'] = $serviceRegistryEntity['NameIDFormat'];
        }

        // Map contacts
        if(array_key_exists('contacts', $serviceRegistryEntity)) {
            foreach($serviceRegistryEntity['contacts'] as $contactIndex => $contact) {
                foreach($contact as $contactDetailCode => $contactDetail) {
                    $cortoEntity['ContactPersons'][$contactIndex][ucfirst($contactDetailCode)] = $contactDetail;
                }
            }
        }

        return $cortoEntity;
    }

    /**
     * Convert an array that has been formatted like this:
     * array('ns1:ns2:key' => 'val') to array('ns1'=>array('ns2'=>array('key'=>'val')))
     *
     * @static
     * @param $entity
     * @return array
     */
    public static function convertServiceRegistryEntityToMultiDimensionalArray($entity)
    {
        $newEntity = array();
        foreach ($entity as $name => $value) {
            $colonSeparatedParts = explode(':', $name);
            if (count($colonSeparatedParts) > 1) {
                $arrayPointer = &$newEntity;
                foreach ($colonSeparatedParts as $subId) {
                    if (!isset($arrayPointer[$subId])) {
                        $arrayPointer[$subId] = array();
                    }
                    $arrayPointer = &$arrayPointer[$subId];
                }
                $arrayPointer = $value;
                unset($arrayPointer);
                continue;
            }

            $dotSeparatedParts = explode('.', $name);
            if (count($dotSeparatedParts) > 1) {
                $arrayPointer = &$newEntity;
                foreach ($dotSeparatedParts as $subId) {
                    if (!isset($arrayPointer[$subId])) {
                        $arrayPointer[$subId] = array();
                    }
                    $arrayPointer = &$arrayPointer[$subId];
                }
                $arrayPointer = $value;
                unset($arrayPointer);
                continue;
            }

            $newEntity[$name] = $value;
        }
        return $newEntity;
    }
}
