<?php

class EngineBlock_Corto_Filter_Output
{
    const VO_NAME_ATTRIBUTE = 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.2';
    const URN_OID_COLLAB_PERSON_ID = 'urn:oid:1.3.6.1.4.1.1076.20.40.40.1';
    const URN_IS_MEMBER_OF = 'urn:oid:1.3.6.1.4.1.5923.1.5.1.1';
    const URN_VO_PREFIX = 'urn:collab:org:';

    const PERSISTENT_NAMEID_SALT = 'COIN:';

    const SAML2_NAME_ID_FORMAT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified';
    const SAML2_NAME_ID_FORMAT_TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
    const SAML2_NAME_ID_FORMAT_PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';

    private $SUPPORTED_NAMEID_FORMATS = array(
        self::SAML2_NAME_ID_FORMAT_UNSPECIFIED,
        self::SAML2_NAME_ID_FORMAT_TRANSIENT,
        self::SAML2_NAME_ID_FORMAT_PERSISTENT,
    );

    private $_adapter;

    public function __construct(EngineBlock_Corto_Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * Called by Corto just as it prepares to send the response to the SP
     *
     * Note that we HAVE to do response fiddling here because the filterInputAttributes only operates on the 'original'
     * response we got from the Idp, after that a NEW response gets created.
     * The filterOutputAttributes actually operates on this NEW response, destined for the SP.
     *
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
        if ($this->_adapter->getProxyServer()->isInProcessingMode()) {
            return;
        }

        $collabPersonId = $_SESSION['subjectId'];
        $responseAttributes[self::URN_OID_COLLAB_PERSON_ID] = array($collabPersonId);

        $voContext = $this->_handleVirtualOrganizationResponse($request, $collabPersonId, $idpEntityMetadata["EntityId"]);

        if (!is_null($voContext)) {
            $responseAttributes = $this->_addVoNameAttribute(
                $responseAttributes,
                $this->_adapter->getVirtualOrganisationContext()
            );
        }

        $this->_trackLogin($spEntityMetadata, $idpEntityMetadata, $collabPersonId, $voContext);

        /*
         * Here we call the external ValidationManager to check what the license information is. An extra SAML response
         * attribute is added to inform the SP what the license status is.
         */
        $responseAttributes = $this->_validateLicense($responseAttributes, $spEntityMetadata, $idpEntityMetadata, $collabPersonId);

        // Attribute Aggregation
        $responseAttributes = $this->_enrichAttributes(
            $idpEntityMetadata["EntityId"],
            $spEntityMetadata["EntityId"],
            $collabPersonId,
            $responseAttributes
        );

        //just in case the attribute manipulations change the $collabPersonId
        $collabPersonIdShallowCopy = $collabPersonId;

        // Attribute / NameId / Response manipulation / mangling
        $this->_manipulateAttributes(
            $collabPersonId,
            $responseAttributes,
            $response
        );

        $response = $this->_setNameId($request, $response, $responseAttributes,
                                      $spEntityMetadata, $collabPersonIdShallowCopy);

        // Always return both OID's and URN's
        $oidResponseAttributes = $this->_mapUrnsToOids($responseAttributes, $spEntityMetadata);
        $responseAttributes = array_merge($responseAttributes, $oidResponseAttributes);

        if ($spEntityMetadata['ProvideIsMemberOf']) {
            $this->_addIsMemberOf($responseAttributes, $idpEntityMetadata);
        }


        /**
         * We can set overrides of the private key in the Service Registry,
         * allowing EngineBlock to switch to a different private key without requiring all SPs to switch at once too.
         */
        if (isset($spEntityMetadata['AlternatePrivateKey']) && $spEntityMetadata['AlternatePublicKey']) {
            $currentEntity = $this->_adapter->getProxyServer()->getCurrentEntity();
            $hostedEntities = $this->_adapter->getProxyServer()->getHostedEntities();
            $hostedEntities[$currentEntity['EntityId']]['certificates']['private'] = $spEntityMetadata['AlternatePrivateKey'];
            $hostedEntities[$currentEntity['EntityId']]['certificates']['public'] = $spEntityMetadata['AlternatePublicKey'];
            $this->_adapter->getProxyServer()->setHostedEntities($hostedEntities);
            $this->_adapter->getProxyServer()->setCurrentEntity($currentEntity['EntityCode']);
        }
    }

    /**
     * Adds group the current user is a member of
     *
     * @param array $responseAttributes Response attributes by reference
     * @param array $idpEntityMetadata  IDP entity metadata
     * @return void
     */
    protected function _addIsMemberOf(array &$responseAttributes, array $idpEntityMetadata)
    {
        if (!isset($responseAttributes[self::URN_IS_MEMBER_OF])) {
            $responseAttributes[self::URN_IS_MEMBER_OF] = array();
        }

        // Remove any IDP set urn:collab:org:... only SURFconext is allowed to set these.
        // @todo Throw an exception maybe?
        $groups = &$responseAttributes[self::URN_IS_MEMBER_OF];
        for ($i = 0; $i < count($groups); $i++) {
            if (strpos($groups[$i], self::URN_VO_PREFIX) === 0) {
                //unset($groups[$i]);
            }
        }

        // Load all VO's and check membership for current user
        $voCollection = new EngineBlock_VirtualOrganization_Collection();
        $voValidator = new EngineBlock_VirtualOrganization_Validator();
        $collabPersonId = reset($responseAttributes[self::URN_OID_COLLAB_PERSON_ID]);
        foreach ($voCollection->load() as $vo) {
            $isMember = $voValidator->isMember($vo->getId(), $collabPersonId, $idpEntityMetadata["EntityId"]);
            if ($isMember) {
                $groups[] = self::URN_VO_PREFIX . $vo->getId();
            }
        }
    }

    protected function _handleVirtualOrganizationResponse($request, $subjectId, $idpEntityId)
    {
        // Determine a Virtual Organization context
        $vo = NULL;

        // In filter stage we need to take a look at the VO context
        if (isset($request['__'][EngineBlock_Corto_CoreProxy::VO_CONTEXT_PFX])) {
            $vo = $request['__'][EngineBlock_Corto_CoreProxy::VO_CONTEXT_PFX];
            $this->_adapter->setVirtualOrganisationContext($vo);
        }

        // If in VO context, validate the user's membership
        if (!is_null($vo)) {
            if (!$this->_validateVOMembership($subjectId, $vo, $idpEntityId)) {
                throw new EngineBlock_Corto_Exception_UserNotMember("User not a member of VO $vo");
            }
        }

        return $vo;
    }

    protected function _addVoNameAttribute($responseAttributes, $voContext)
    {
        $responseAttributes[self::VO_NAME_ATTRIBUTE] = $voContext;

        return $responseAttributes;
    }

    protected function _trackLogin($spEntityId, $idpEntityId, $subjectId, $voContext)
    {
        $tracker = new EngineBlock_Tracker();
        $tracker->trackLogin($spEntityId, $idpEntityId, $subjectId, $voContext);
    }

    /**
     * @todo this is pure happy flow
     *
     * @param  $subjectIdentifier
     * @param  $voIdentifier
     * @return boolean
     */
    protected function _validateVOMembership($subjectIdentifier, $voIdentifier, $idpEntityId)
    {
        $validator = new EngineBlock_VirtualOrganization_Validator();
        return $validator->isMember($voIdentifier, $subjectIdentifier, $idpEntityId);
    }

    /**
     * Enrich the current set of attributes with attributes from other sources.
     *
     * @param string $idpEntityId
     * @param string $spEntityId
     * @param string $subjectId
     * @param array $attributes
     * @return array
     */
    protected function _enrichAttributes($idpEntityId, $spEntityId, $subjectId, array $attributes)
    {
        $aggregator = $this->_getAttributeAggregator(
            $this->_getAttributeProviders($spEntityId, isset($attributes[self::VO_NAME_ATTRIBUTE])
                                                             ? $attributes[self::VO_NAME_ATTRIBUTE][0] : null)
        );
        return $aggregator->aggregateFor(
            $attributes,
            $subjectId
        );
    }

    protected function _manipulateAttributes(&$subjectId, array &$attributes, array &$response)
    {
        $manipulators = $this->_getAttributeManipulators();
        foreach ($manipulators as $manipulator) {
            $manipulator->manipulate($subjectId, $attributes, $response);
        }
    }

    protected function _setNameId($request, $response, &$responseAttributes, $spEntityMetadata, $collabPersonId)
    {
        $nameIdFormat = $this->_getNameIdFormat($request, $spEntityMetadata);

        if ($nameIdFormat === self::SAML2_NAME_ID_FORMAT_UNSPECIFIED) {
            $nameId = $collabPersonId;
        }
        else {
            $nameId = $this->_getPersistentNameId($collabPersonId, $spEntityMetadata['EntityId']);
        }

        // Adjust the NameID in the NEW response, set the collab:person uid
        $response['saml:Assertion']['saml:Subject']['saml:NameID'] = array(
            '_Format' => $nameIdFormat,
            '__v' => $nameId,
        );

        // Add the eduPersonTargetedId
        $responseAttributes['urn:oid:1.3.6.1.4.1.5923.1.1.1.10'] = array(
            array(
                "saml:NameID" => $response['saml:Assertion']['saml:Subject']['saml:NameID'],
            )
        );

        return $response;
    }

    protected function _getNameIdFormat($request, $spEntityMetadata)
    {
        // Persistent is our default
        $defaultNameIdFormat = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';

        // If a NameIDFormat was explicitly set in the ServiceRegistry, use that...
        if (isset($spEntityMetadata['NameIDFormat'])) {
            return $spEntityMetadata['NameIDFormat'];
        }
            // If the SP requests a specific NameIDFormat in their AuthnRequest
        else if (isset($request['samlp:NameIDPolicy']['_Format'])) {
            $requestedNameIdFormat = $request['samlp:NameIDPolicy']['_Format'];
            if (in_array($requestedNameIdFormat, $this->SUPPORTED_NAMEID_FORMATS)) {
                return $request['samlp:NameIDPolicy']['_Format'];
            }
            else {
                EngineBlock_ApplicationSingleton::getLog()->warn(
                    "Whoa, SP '{$spEntityMetadata['EntityID']}' requested '{$requestedNameIdFormat}' " .
                    "however we don't support that format, opting to try '$defaultNameIdFormat' " .
                    "instead of sending an error. SP might not be happy with that..."
                );
                return $defaultNameIdFormat;
            }
        }
        return $defaultNameIdFormat;
    }

    protected function _getPersistentNameId($collabPersonId, $spEntityId)
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        $db = $factory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);

        $serviceProviderUuid = $this->_getServiceProviderUuid($spEntityId, $db);
        $userUuid = $this->_getUserUuid($collabPersonId);

        $statement = $db->prepare(
            "SELECT persistent_id FROM saml_persistent_id WHERE service_provider_uuid = ? AND user_uuid = ?"
        );
        $statement->execute(array($serviceProviderUuid, $userUuid));
        $rows = $statement->fetchAll();

        if (empty($rows)) {
            $persistentId = sha1(self::PERSISTENT_NAMEID_SALT . $userUuid . $serviceProviderUuid);
            $statement = $db->prepare(
                "INSERT INTO saml_persistent_id (persistent_id, service_provider_uuid, user_uuid) VALUES (?,?,?)"
            );
            $result = $statement->execute(array($persistentId, $serviceProviderUuid, $userUuid));
            if (!$result) {
                throw new EngineBlock_Exception(
                    'Unable to store new persistent id for SP UUID: ' . $serviceProviderUuid .
                    ' and user uuid: ' . $userUuid
                );
            }
            return $persistentId;
        }
        else if (count($rows) > 1) {
            throw new EngineBlock_Exception(
                'Multiple persistent IDs found? For: SPUUID: ' . $serviceProviderUuid . ' and user UUID: ' . $userUuid
            );
        }
        else {
            return $rows[0]['persistent_id'];
        }
    }

    protected function _getServiceProviderUuid($spEntityId, $db)
    {
        $statement = $db->prepare("SELECT uuid FROM service_provider_uuid WHERE service_provider_entity_id=?");
        $statement->execute(array($spEntityId));
        $result = $statement->fetchAll();

        if (empty($result)) {
            $uuid = (string)Surfnet_Zend_Uuid::generate();
            $statement = $db->prepare("INSERT INTO service_provider_uuid (uuid, service_provider_entity_id) VALUES (?,?)");
            $statement->execute(
                array(
                     $uuid,
                     $spEntityId,
                )
            );
        }
        else {
            $uuid = $result[0]['uuid'];
        }
        return $uuid;
    }

    protected function _getUserUuid($collabPersonId)
    {
        $userDirectory = new EngineBlock_UserDirectory(
            EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->ldap
        );
        $users = $userDirectory->findUsersByIdentifier($collabPersonId);
        if (count($users) > 1) {
            throw new EngineBlock_Exception('Multiple users found for collabPersonId: ' . $collabPersonId);
        }

        if (count($users) < 1) {
            throw new EngineBlock_Exception('No users found for collabPersonId: ' . $collabPersonId);
        }

        return $users[0]['collabpersonuuid'];
    }

    protected function _mapUrnsToOids(array $responseAttributes, array $spEntityMetadata)
    {
        $mapper = new EngineBlock_AttributeMapper_Urn2Oid();
        return $mapper->map($responseAttributes);
    }

    protected function _getAttributeProviders($spEntityId, $voContext = null)
    {
        $providers = array();
        if ($voContext) {
            $providers[] = new EngineBlock_AttributeProvider_VoManage($voContext, $spEntityId);
        }
        return $providers;
    }

    protected function _getAttributeManipulators()
    {
        return array(
            new EngineBlock_AttributeManipulator_File()
        );
    }

    protected function _getAttributeAggregator($providers)
    {
        return new EngineBlock_AttributeAggregator($providers);
    }

    protected function _validateLicense($responseAttributes, $spEntityMetadata, $idpEntityMetadata, $userId)
    {
        $config = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
        $licenseEngine = new EngineBlock_LicenseEngine_ValidationManager($config);
        $responseAttributes[EngineBlock_LicenseEngine_ValidationManager::LICENSE_SAML_ATTRIBUTE] =
                $licenseEngine->validate($userId, $spEntityMetadata, $idpEntityMetadata);
        return $responseAttributes;
    }
}
