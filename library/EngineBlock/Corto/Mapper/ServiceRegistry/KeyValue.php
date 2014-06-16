<?php

class EngineBlock_Corto_Mapper_ServiceRegistry_KeyValue
{
    /**
     * Map a Service Registry key/value (JANUS) array to a Corto Entity Metadata array.
     *
     * @param array $serviceRegistryEntity
     * @return array
     */
    public function fromKeyValue(array $serviceRegistryEntity)
    {
        $cortoEntity = array();

        // Publish in edugain
        if (isset($serviceRegistryEntity['coin:publish_in_edugain'])) {
            $cortoEntity['PublishInEdugain'] = $serviceRegistryEntity['coin:publish_in_edugain'];
        }
        if (isset($serviceRegistryEntity['coin:publish_in_edugain_date'])) {
            $cortoEntity['PublishInEdugainDate'] = $serviceRegistryEntity['coin:publish_in_edugain_date'];
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
                $cortoEntity['TransparentIssuer'] = $serviceRegistryEntity['coin:transparant_issuer'];
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

            $cortoEntity['shibmd:scopes'] = array();
            for ($i = 0; $i < 10; $i++) {
                if (isset($serviceRegistryEntity["shibmd:scope:$i:allowed"])) {
                    $regexp = isset($serviceRegistryEntity["shibmd:scope:$i:regexp"]) ? $serviceRegistryEntity["shibmd:scope:$i:regexp"] : false;
                    $cortoEntity['shibmd:scopes'][$i] = array(
                        'allowed'  => $serviceRegistryEntity["shibmd:scope:$i:allowed"],
                        'regexp' => $regexp
                    );
                }
            }

        }

        $cortoEntity = $this->mapCertificates($serviceRegistryEntity, $cortoEntity);

        $this->_multilang($cortoEntity, $serviceRegistryEntity, array(
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
        $this->_multilang($cortoEntity['Organization'], $serviceRegistryEntity, array(
            'OrganizationName'         => 'Name',
            'OrganizationDisplayName'  => 'DisplayName',
            'OrganizationURL'          => 'URL',
        ));
        if (empty($cortoEntity['Organization'])) {
            unset($cortoEntity['Organization']);
        }

        // Keywords for searching in the WAYF
        $this->_multilang(
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

    /**
     * @param array $serviceRegistryEntity
     * @param $cortoEntity
     * @return array
     */
    private function mapCertificates(array $serviceRegistryEntity, $cortoEntity)
    {
        $publicKeyFactory = new EngineBlock_X509_PublicKeyFactory();
        $cortoEntity['certificates'] = array();

        // certData
        if (!isset($serviceRegistryEntity['certData']) || !$serviceRegistryEntity['certData']) {
            return $cortoEntity;
        }
        $cortoEntity['certificates']['public'] = $publicKeyFactory->fromCertData(
            $serviceRegistryEntity['certData']
        );

        // certData2
        if (!isset($serviceRegistryEntity['certData2']) || !$serviceRegistryEntity['certData2']) {
            return $cortoEntity;
        }
        $cortoEntity['certificates']['public-fallback'] = $publicKeyFactory->fromCertData(
            $serviceRegistryEntity['certData2']
        );

        // certData3
        if (!isset($serviceRegistryEntity['certData3']) || $serviceRegistryEntity['certData3']) {
            return $cortoEntity;
        }
        $cortoEntity['certificates']['public-fallback2'] = $publicKeyFactory->fromCertData(
            $serviceRegistryEntity['certData3']
        );

        return $cortoEntity;
    }

    protected function _multiLang(&$cortoEntity, $serviceRegistryEntity, $mapping)
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
