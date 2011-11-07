<?php

class EngineBlock_Corto_Filter_Input
{
    const SAML2_STATUS_CODE_SUCCESS = 'urn:oasis:names:tc:SAML:2.0:status:Success';
    const SAML2_NAMEID_FORMAT_PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
    const SURF_PERSON_AFFILIATION_OID = 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.1';

    private $_adapter;

    public function __construct(EngineBlock_Corto_Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * Called by Corto whenever it receives an Assertion with attributes from an Identity Provider.
     *
     * Note we have to do everything that relies on the actual idpEntityMetadata here, because in the
     * filterOutputAttributes the idp metadata points to us (Corto / EngineBlock) not the actual idp we received
     * the response from.
     *
     * @throws EngineBlock_Exception_ReceivedErrorStatusCode
     * @param array $response
     * @param array $responseAttributes
     * @param array $request
     * @param array $spEntityMetadata
     * @param array $idpEntityMetadata
     * @return void
     */
    public function filter(
        array &$response,
        array &$responseAttributes,
        array $request,
        array $spEntityMetadata,
        array $idpEntityMetadata
    )
    {
        if ($response['samlp:Status']['samlp:StatusCode']['_Value'] !== self::SAML2_STATUS_CODE_SUCCESS) {
            // Idp returned an error
            throw new EngineBlock_Corto_Exception_ReceivedErrorStatusCode(
                'Response received with Status: ' .
                $response['samlp:Status']['samlp:StatusCode']['_Value'] .
                ' - ' .
                $response['samlp:Status']['samlp:StatusMessage']['__v']
            );
        }

        // validate if the IDP sending this response is allowed to connect to the SP that made the request.
        $this->validateSpIdpConnection($spEntityMetadata["EntityId"], $idpEntityMetadata["EntityId"]);

        // map oids to URNs
        $responseAttributes = $this->_mapOidsToUrns($responseAttributes, $idpEntityMetadata);

        $this->_validateAttributes($responseAttributes);

        $responseAttributes = $this->_supplementAttributes($responseAttributes, $idpEntityMetadata);

        // Provisioning of the user account
        $subjectId = $this->_provisionUser($responseAttributes, $spEntityMetadata, $idpEntityMetadata);
        $_SESSION['subjectId'] = $subjectId;

        // Adjust the NameID in the OLD response (for consent), set the collab:person uid
        $response['saml:Assertion']['saml:Subject']['saml:NameID'] = array(
            '_Format' => self::SAML2_NAMEID_FORMAT_PERSISTENT,
            '__v'     => $subjectId
        );
    }

    public function validateSpIdpConnection($spEntityId, $idpEntityId)
    {
        $serviceRegistryAdapter = $this->_adapter->getServiceRegistryAdapter();
        if (!$serviceRegistryAdapter->isConnectionAllowed($spEntityId, $idpEntityId)) {
            throw new EngineBlock_Corto_Exception_InvalidConnection(
                "Received a response from an IDP that is not allowed to connect to the requesting SP"
            );
        }
    }

    protected function _mapOidsToUrns(array $responseAttributes, array $idpEntityMetadata)
    {
        $mapper = new EngineBlock_AttributeMapper_Oid2Urn();
        return $mapper->map($responseAttributes);
    }

    protected function _validateAttributes(array $responseAttributes)
    {
        $errors = array();

        $error = $this->_requireValidSchacHomeOrganization($responseAttributes);
        if ($error) {
            $errors[] = $error;
        }

        $error = $this->_requireValidUid($responseAttributes);
        if ($error) {
            $errors[] = $error;
        }

        if (!empty($errors)) {
            throw new EngineBlock_Corto_Exception_MissingRequiredFields(
                "Errors validating attributes, errors: " . print_r($errors, true) .
                        ' attributes: ' . print_r($responseAttributes, true)
            );
        }
    }

    protected function _requireValidSchacHomeOrganization($responseAttributes)
    {
        if (!isset($responseAttributes['urn:mace:terena.org:attribute-def:schacHomeOrganization'])) {
            return "urn:mace:terena.org:attribute-def:schacHomeOrganization missing in attributes!";
        }

        $schacHomeOrganizationValues = $responseAttributes['urn:mace:terena.org:attribute-def:schacHomeOrganization'];

        if (count($schacHomeOrganizationValues) === 0) {
            return "urn:mace:terena.org:attribute-def:schacHomeOrganization has no values";
        }

        if (count($schacHomeOrganizationValues) > 1) {
            return  "urn:mace:terena.org:attribute-def:schacHomeOrganization has too many values";
        }

        $schacHomeOrganization = $schacHomeOrganizationValues[0];

        $uri = Zend_Uri_Http::fromString('http://' . $schacHomeOrganization);
        $validHostName = $uri->validateHost($schacHomeOrganization);
        if (!$validHostName) {
            return "urn:mace:terena.org:attribute-def:schacHomeOrganization is not a valid hostname!";
        }
        return false;
    }

    protected function _requireValidUid($responseAttributes)
    {
        if (!isset($responseAttributes['urn:mace:dir:attribute-def:uid'])) {
            return "urn:mace:dir:attribute-def:uid missing in attributes!";
        }

        if (count($responseAttributes['urn:mace:dir:attribute-def:uid']) === 0) {
            return "urn:mace:dir:attribute-def:uid has no values";
        }

        if (count($responseAttributes['urn:mace:dir:attribute-def:uid']) > 1) {
            return "urn:mace:dir:attribute-def:uid has more than one value";
        }

        $uid = $responseAttributes['urn:mace:dir:attribute-def:uid'][0];
        // Note that this is a bit naive as the spec states that this can be 256 characters
        // of UTF-8, so in theory a person could have a uid of 100 kanji characters (which take up 3 bytes)
        // and our check would fail, even though it is completely valid.
        // But we'll cross that ブリッジ when we get to it.
        if (strlen($uid) > 256) {
            return "urn:mace:dir:attribute-def:uid is more than 256 characters long!";
        }
        return false;
    }

    protected function _supplementAttributes(array $responseAttributes, $idpEntityMetadata)
    {
        // If we don't have a commonName, determine one from the attributes
        if (!isset($responseAttributes['urn:mace:dir:attribute-def:cn'][0])) {
            $responseAttributes['urn:mace:dir:attribute-def:cn'] = array(
                $this->_determineCommonNameFromAttributes($responseAttributes)
            );
        }
        // If we don't have a displayName, use the commonName
        if (!isset($responseAttributes['urn:mace:dir:attribute-def:displayName'][0])) {
            $responseAttributes['urn:mace:dir:attribute-def:displayName'] = array(
                $responseAttributes['urn:mace:dir:attribute-def:cn'][0]
            );
        }
        // If we don't have a surName use the commonName
        if (!isset($responseAttributes['urn:mace:dir:attribute-def:sn'])) {
            $responseAttributes['urn:mace:dir:attribute-def:sn'] = array(
                $responseAttributes['urn:mace:dir:attribute-def:cn'][0]
            );
        }
        // Is a guest user?
        $responseAttributes = $this->_addSurfPersonAffiliationAttribute($responseAttributes, $idpEntityMetadata);

        return $responseAttributes;
    }

    protected function _determineCommonNameFromAttributes(array $responseAttributes)
    {
        if (isset($responseAttributes['urn:mace:dir:attribute-def:givenName'][0]) &&
            isset($responseAttributes['urn:mace:dir:attribute-def:sn'][0])) {
            return $responseAttributes['urn:mace:dir:attribute-def:givenName'][0] . ' ' .
                   $responseAttributes['urn:mace:dir:attribute-def:sn'][0];
        }

        if (isset($responseAttributes['urn:mace:dir:attribute-def:sn'][0])) {
            return $responseAttributes['urn:mace:dir:attribute-def:sn'][0];
        }

        if (isset($responseAttributes['urn:mace:dir:attribute-def:displayName'][0])) {
            return $responseAttributes['urn:mace:dir:attribute-def:displayName'][0];
        }

        if (isset($responseAttributes['urn:mace:dir:attribute-def:mail'][0])) {
            return $responseAttributes['urn:mace:dir:attribute-def:mail'][0];
        }

        if (isset($responseAttributes['urn:mace:dir:attribute-def:givenName'][0])) {
            return $responseAttributes['urn:mace:dir:attribute-def:givenName'][0];
        }

        return $responseAttributes['urn:mace:dir:attribute-def:uid'][0];
    }

    protected function _addSurfPersonAffiliationAttribute($responseAttributes, $idpEntityMetadata)
    {
        // Determine guest status
        if (!isset($idpEntityMetadata['GuestQualifier'])) {
            ebLog()->warn(
                'No GuestQualifier for IdP: ' . var_export($idpEntityMetadata, true) .
                        'Setting it to "All" and continuing.'
            );
            $idpEntityMetadata['GuestQualifier'] = 'All';
        }

        switch ($idpEntityMetadata['GuestQualifier']) {
            case 'None':
                $responseAttributes[static::SURF_PERSON_AFFILIATION_OID] = array(
                    0 => 'member',
                );
                break;

            case 'Some':
                if (!isset($responseAttributes[static::SURF_PERSON_AFFILIATION_OID][0])) {
                    ebLog()->warn(
                        "Idp guestQualifier is set to 'Some' however, ".
                        "the surfPersonAffiliation attribute was not provided, ".
                        "setting it to 'guest' and continuing" .
                        var_export($idpEntityMetadata, true) .
                        var_export($responseAttributes, true)
                    );
                    $responseAttributes[static::SURF_PERSON_AFFILIATION_OID] = array(
                        0 => 'guest',
                    );
                }
                break;

            case 'All':
            default:
                $responseAttributes[static::SURF_PERSON_AFFILIATION_OID] = array(
                    0 => 'guest',
                );
                break;
        }
        return $responseAttributes;
    }

    protected function _provisionUser($attributes, $spEntityMetadata, $idpEntityMetadata)
    {
        return $this->_getProvisioning()->provisionUser($attributes, $spEntityMetadata, $idpEntityMetadata);
    }

    protected function _getProvisioning()
    {
        return new EngineBlock_Provisioning();
    }
}