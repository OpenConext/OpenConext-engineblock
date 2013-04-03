<?php

class EngineBlock_Corto_ProxyServer
{
    const ID_PREFIX = 'CORTO';

    const MODULE_BINDINGS   = 'Bindings';
    const MODULE_SERVICES   = 'Services';

    const TEMPLATE_SOURCE_FILESYSTEM = 'filesystem';
    const TEMPLATE_SOURCE_MEMORY     = 'memory';

    const MESSAGE_TYPE_REQUEST  = 'SAMLRequest';
    const MESSAGE_TYPE_RESPONSE = 'SAMLResponse';

    const VO_CONTEXT_PFX          = 'voContext';

    protected $_serviceToControllerMapping = array(
        'singleSignOnService'               => '/authentication/idp/single-sign-on',
        'debugSingleSignOnService'          => '/authentication/sp/debug',
        'continueToIdP'                     => '/authentication/idp/process-wayf',

        'assertionConsumerService'          => '/authentication/sp/consume-assertion',
        'continueToSP'                      => '/authentication/sp/process-consent',
        'provideConsentService'             => '/authentication/idp/provide-consent',
        'processConsentService'             => '/authentication/idp/process-consent',
        'processedAssertionConsumerService' => '/authentication/proxy/processed-assertion',

        'idpMetadataService'                => '/authentication/idp/metadata',
        'spMetadataService'                 => '/authentication/sp/metadata',
    );

    protected $_headers = array();
    protected $_output;

    protected $_voContext = null;

    protected $_requestArray;
    protected $_responseArray;

    protected $_server;
    protected $_systemLog;
    protected $_sessionLog;
    protected $_sessionLogDefault;

    protected $_configs;


    /**
     * @var array
     *
     * Remote are all SP's and IdP's except Engineblock itself. Metadata of engineblock as IdP and SP is stored
     * separately in current entities. This is done EngineBlock can never remove it's own metadata by filtering etc.
     * It is recommended to use getCurrentEntity() to get Engineblock metadata however using getRemoteEntity() is also possible
     * since this will proxy current entity information
     *
     */
    protected $_entities = array(
        'current'=>array(),
        'hosted'=>array(),
        'remote'=>array(),
    );
    protected $_modules = array();
    protected $_templateSource;
    protected $_processingMode = false;

    public function __construct()
    {
        $this->_server = $this;
    }

//////// GETTERS / SETTERS /////////


    public function setVirtualOrganisationContext($voContext)
    {
        $this->_voContext = $voContext;
    }

    public function getVirtualOrganisationContext()
    {
        return $this->_voContext;
    }

    public function getOutput()
    {
        return $this->_output;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function sendOutput($rawOutput)
    {
        $this->_output = $rawOutput;
    }

    public function sendHeader($name, $value)
    {
        $this->_headers[$name] = $value;
    }

    public function setProcessingMode()
    {
        $this->_processingMode = true;
        return $this;
    }

    public function unsetProcessingMode()
    {
        $this->_processingMode = false;
        return $this;
    }

    public function isInProcessingMode()
    {
        return $this->_processingMode;
    }

    /**
     * Does the request have an "unsolicited" flag
     *
     * @param array $request
     * @return type
     */
    public function isUnsolicitedRequest(array $request)
    {
        return !empty($request[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Unsolicited']);
    }

    /**
     * @return EngineBlock_Corto_Module_Bindings
     */
    public function getBindingsModule()
    {
        return $this->_getModule(self::MODULE_BINDINGS);
    }

    /**
     * @param EngineBlock_Corto_Module_Bindings $bindingsInstance
     * @return EngineBlock_Corto_ProxyServer
     */
    public function setBindingsModule(EngineBlock_Corto_Module_Bindings $bindingsInstance)
    {
        return $this->_setModule(self::MODULE_BINDINGS, $bindingsInstance);
    }

    /**
     * @return EngineBlock_Corto_Module_Services
     */
    public function getServicesModule()
    {
        return $this->_getModule(self::MODULE_SERVICES);
    }

    /**
     * @param EngineBlock_Corto_Module_Services $servicesInstance
     * @return EngineBlock_Corto_ProxyServer
     */
    public function setServicesModule(EngineBlock_Corto_Module_Services $servicesInstance)
    {
        return $this->_setModule(self::MODULE_SERVICES, $servicesInstance);
    }

    /**
     * @param string $name
     * @return EngineBlock_Corto_Module_Abstract
     */
    protected function _getModule($name)
    {
        return $this->_modules[$name];
    }

    /**
     * @param  $name
     * @param  $moduleInstance
     * @return EngineBlock_Corto_ProxyServer
     */
    protected function _setModule($name, EngineBlock_Corto_Module_Abstract $moduleInstance)
    {
        $this->_modules[$name] = $moduleInstance;
        return $this;
    }

    public function getConfig($name, $default = null)
    {
        if (isset($this->_configs[$name])) {
            return $this->_configs[$name];
        }
        return $default;
    }

    public function getConfigs()
    {
        return $this->_configs;
    }

    public function setConfig($name, $value)
    {
        $this->_configs[$name] = $value;
        return $this;
    }

    public function setConfigs($configs)
    {
        $this->_configs = $configs;
        return $this;
    }

    public function getAttributeName($attributeId, $ietfLanguageTag = 'en', $fallbackToId = true)
    {
        $metadata = new EngineBlock_Attributes_Metadata();
        return $metadata->getName($attributeId, $ietfLanguageTag, $fallbackToId);
    }

    public function getAttributeDescription($attributeId, $ietfLanguageTag = 'en', $fallbackToId = true)
    {
        $metadata = new EngineBlock_Attributes_Metadata();
        return $metadata->getDescription($attributeId, $ietfLanguageTag);
    }

    /**
     * Return the url of the Static vhost containing media, script and css files
     *
     * @example <?php echo $this->staticUrl(); ?>
     *
     * @return string
     */
    public static function staticUrl($path = "")
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $settings = $application->getConfiguration();
        return $settings->static->protocol . '://'. $settings->static->host . $path;
    }

    public function getUrl($serviceName = "", $remoteEntityId = "", $request = "")
    {
        if (!isset($this->_serviceToControllerMapping[$serviceName])) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                "Unable to map service '$serviceName' to a controller!"
            );
        }

        $scheme = 'http';
        if (isset($_SERVER['HTTPS'])) {
            $scheme = 'https';
        }

        $host = $_SERVER['HTTP_HOST'];

        $mappedUri = $this->_serviceToControllerMapping[$serviceName];

        $isImplicitVo = false;
        $remoteEntity = false;
        if ($remoteEntityId) {
            $remoteEntity = $this->getRemoteEntity($remoteEntityId);
        }
        if ($remoteEntity && isset($remoteEntity['VoContext'])) {
            if ($request && !isset($request['__'][EngineBlock_Corto_ProxyServer::VO_CONTEXT_PFX])) {
                $isImplicitVo = true;
            }
        }
        if (!$this->_processingMode && $this->_voContext !== null && $serviceName != "spMetadataService" && !$isImplicitVo) {
            $mappedUri .= '/' . "vo:" . $this->_voContext;
        }
        if (!$this->_processingMode && $serviceName !== 'idpMetadataService' && $remoteEntityId) {
            $mappedUri .= '/' . md5($remoteEntityId);
        }

        return $scheme . '://' . $host . $mappedUri;
    }

    public function hasRemoteEntity($entityId)
    {
        return isset($this->_entities['remote'][$entityId]);
    }

    public function getRemoteEntity($entityId)
    {
        if (!isset($this->_entities['remote'][$entityId])) {
            $entity = $this->findRemoteEntityInCurrentEntities($entityId);
            if (empty($entity)) {
                throw new EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException($entityId);
            }
            return $entity;
        }
        $entity = $this->_entities['remote'][$entityId];
        $entity['EntityId'] = $entityId;
        return $entity;
    }

    public function getRemoteEntities()
    {
        return $this->_entities['remote'];
    }

    /**
     * Gets current entity by name
     *
     * @param string $name spMetadataService|idpMetadataService
     * @return array mixed
     * @throws EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException
     */
    public function getCurrentEntity($name)
    {
        if (!isset($this->_entities['current'][$name])) {
            throw new EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException($name);
        }
        $entity = $this->_entities['current'][$name];
        return $entity;
    }

    /**
     * Tries to find the requested in the current entity list
     *
     * @param $name string
     * @return null|array
     */
    public function findRemoteEntityInCurrentEntities($name)
    {
        foreach($this->_entities['current'] as $currentEntity) {
            if ($name == $currentEntity['EntityID']) {
                return $currentEntity;
            }
        }
    }

    public function getIdpEntityIds()
    {
        $idps = array();
        foreach ($this->_server->getRemoteEntities() as $remoteEntityId => $remoteEntity) {
            if (isset($remoteEntity['SingleSignOnService'])) {
                $idps[] = $remoteEntityId;
            }
        }
        return $idps;
    }

    /**
     * @param array $entities
     */
    public function setCurrentEntities(array $entities)
    {
        $this->_entities['current'] = $entities;
    }

    public function setRemoteEntities($entities)
    {
        $this->_entities['remote'] = $entities;
    }

    public function setTemplateSource($type, $arguments)
    {
        $this->_templateSource = array(
            'type'      => $type,
            'arguments' => $arguments,
        );
        return $this;
    }

    public function getTemplateSource()
    {
        return $this->_templateSource;
    }

//////// MAIN /////////

    public function serve($serviceName, $remoteIdpMd5 = "")
    {
        if (!empty($remoteIdpMd5)) {
            $this->setRemoteIdpMd5($remoteIdpMd5);
        }

        $this->startSession();
        $this->getSessionLog()->info("Started request with parameters: ". var_export(func_get_args(), true));

        $this->getSessionLog()->info("Calling service '$serviceName'");
        $this->getServicesModule()->serve($serviceName);
        $this->getSessionLog()->info("Done calling service '$serviceName'");
    }

    public function setRemoteIdpMd5($remoteIdPMd5)
    {
        $remoteEntityIds = array_keys($this->_entities['remote']);
        foreach ($remoteEntityIds as $remoteEntityId) {
            if (md5($remoteEntityId) === $remoteIdPMd5) {
                $this->_configs['Idp'] = $remoteEntityId;
                $this->_configs['TransparentProxy'] = true;
                $this->getSessionLog()->info("Detected pre-selection of $remoteEntityId as IdP, switching to transparant mode");
                break;
            }
        }
        // Patch Migration BACKLOG-915 Begin
        foreach ($remoteEntityIds as $remoteEntityId) {
            if (substr($remoteEntityId, -8) == "/migrate") {

                if (md5(substr($remoteEntityId, 0, -8)) === $remoteIdPMd5) {
                    $this->_configs['Idp'] = $remoteEntityId;
                    $this->_configs['TransparentProxy'] = true;
                    $this->getSessionLog()->info("Re detected pre-selection of $remoteEntityId as IdP, switching to IdP EntityID with Alias");
                    break;
                }
            }
        }
        // Patch Migration BACKLOG-915 End
        if (!isset($this->_configs['Idp'])) {
            $this->getSessionLog()->warn("Unable to map remote IdpMD5 '$remoteIdPMd5' to a remote entity!");
        }

        return $this;
    }

////////  REQUEST HANDLING /////////

    public function sendAuthenticationRequest(array $request, $idpEntityId, $scope = null)
    {
        $cookieExpiresStamp = null;
        if (isset($this->_configs['rememberIdp'])) {
            $cookieExpiresStamp = strtotime($this->_configs['rememberIdp']);
        }
        $this->setCookie('selectedIdp', $idpEntityId, $cookieExpiresStamp);

        $originalId = $request['_ID'];

        $newRequest = $this->createEnhancedRequest($request, $idpEntityId, $scope);
        $newId = $newRequest['_ID'];

        // Store the original Request
        $_SESSION[$originalId]['SAMLRequest'] = $request;

        // Store the mapping from the new request ID to the original request ID
        $_SESSION[$newId] = array();
        $_SESSION[$newId]['SAMLRequest'] = $request;
        $_SESSION[$newId]['_InResponseTo'] = $originalId;

        $this->getBindingsModule()->send($newRequest, $this->getRemoteEntity($idpEntityId));
    }

    /**
     *
     *
     * @param string $idp
     * @param array|null $scoping
     * @return array
     */
    public function createEnhancedRequest($originalRequest, $idp, array $scoping = null)
    {
        $remoteMetaData = $this->getRemoteEntity($idp);

        $nameIdPolicy = array('_AllowCreate'  => 'true');
        /**
         * Name policy is not required, so it is only set if configured, SAML 2.0 spec
         * says only following values are allowed:
         *  - urn:oasis:names:tc:SAML:2.0:nameid-format:transient
         *  - urn:oasis:names:tc:SAML:2.0:nameid-format:persistent.
         *
         * Note: Some IDP's like those using ADFS2 do not understand those, for these cases the format can be 'configured as empty
         * or set to an older version.
         */
        // @todo check why it is empty
        if (!empty($remoteMetaData['NameIDFormat'])) {
            $nameIdPolicy['_Format'] = $remoteMetaData['NameIDFormat'];
        }

        $request = array(
            EngineBlock_Corto_XmlToArray::TAG_NAME_PFX       => 'samlp:AuthnRequest',
            EngineBlock_Corto_XmlToArray::PRIVATE_PFX => array(
                'paramname'         => 'SAMLRequest',
                'destinationid'     => $idp,
                'ProtocolBinding'   => $remoteMetaData['SingleSignOnService']['Binding'],
            ),
            '_xmlns:saml'                       => 'urn:oasis:names:tc:SAML:2.0:assertion',
            '_xmlns:samlp'                      => 'urn:oasis:names:tc:SAML:2.0:protocol',

            '_ID'                               => $this->getNewId(),
            '_Version'                          => '2.0',
            '_IssueInstant'                     => $this->timeStamp(),
            '_Destination'                      => $remoteMetaData['SingleSignOnService']['Location'],
            '_ForceAuthn'                       => ($originalRequest['_ForceAuthn']) ? 'true' : 'false',
            '_IsPassive'                        => ($originalRequest['_IsPassive']) ? 'true' : 'false',

            // Send the response to us.
            '_AssertionConsumerServiceURL'      => $this->getUrl('assertionConsumerService'),
            '_ProtocolBinding'                  => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',

            'saml:Issuer' => array('__v' => $this->getUrl('spMetadataService')),
            'ds:Signature' => '__placeholder__',

            'samlp:NameIDPolicy' => $nameIdPolicy
        );

        if (isset($originalRequest['_AttributeConsumingServiceIndex'])) {
            $request['_AttributeConsumingServiceIndex'] = $originalRequest['_AttributeConsumingServiceIndex'];
        }

        if (empty($remoteMetaData['DisableScoping'])) {
            if ($scoping) {
                $scoping = (array) $scoping;
                foreach ($scoping as $scopedIdP) {
                    $request['samlp:Scoping']['samlp:IDPList']['samlp:IDPEntry'][] = array('_ProviderID' => $scoping);
                }
                return $request;
            }

            // Copy original scoping rules
            if (isset($originalRequest['samlp:Scoping'])) {
                $request['samlp:Scoping'] = $originalRequest['samlp:Scoping'];
            }
            else {
                $request['samlp:Scoping'] = array();
            }

            // Decrease or initialize the proxycount
            if (isset($originalRequest['samlp:Scoping']['_ProxyCount'])) {
                $request['samlp:Scoping']['_ProxyCount']--;
            }
            else {
                $request['samlp:Scoping']['_ProxyCount'] = $this->getConfig('max_proxies', 10);
            }

            // Add the issuer of the original request as requester
            if (!isset($request['samlp:Scoping']['samlp:RequesterID'])) {
                $request['samlp:Scoping']['samlp:RequesterID'] = array();
            }
            $request['samlp:Scoping']['samlp:RequesterID'][] = array('__v' => $originalRequest['saml:Issuer']['__v']);
        }

        return $request;
    }

//////// RESPONSE HANDLING ////////

    public function createErrorResponse($request, $errorStatus)
    {
        $response = $this->_createBaseResponse($request);

        $errorCodePrefix = 'urn:oasis:names:tc:SAML:2.0:status:';
        $response['samlp:Status'] = array(
            'samlp:StatusCode' => array(
                '_Value' => 'urn:oasis:names:tc:SAML:2.0:status:Responder',
                'samlp:StatusCode' => array(
                    '_Value' => $errorCodePrefix . $errorStatus,
                ),
            ),
        );
        return $response;
    }

    public function createEnhancedResponse($request, $sourceResponse)
    {
        $response = $this->_createBaseResponse($request);

        // Store the Origin response (from the IdP)
        if (isset($sourceResponse['__']['OriginalResponse'])) {
            $response['__']['OriginalResponse'] = $sourceResponse['__']['OriginalResponse'];
        }
        else {
            $response['__']['OriginalResponse'] = $sourceResponse;
        }

        $inTransparentMode = isset($request[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Transparent']) &&
                $request[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Transparent'];

        if (isset($sourceResponse['__']['OriginalIssuer'])) {
            $response['__']['OriginalIssuer'] = $sourceResponse['__']['OriginalIssuer'];
        }
        else {
            $response['__']['OriginalIssuer'] = $sourceResponse['saml:Issuer']['__v'];
        }

        if (!$this->isInProcessingMode() && $inTransparentMode) {
            // Patch Migration BACKLOG-915 Begin
            if (substr($response['__']['OriginalIssuer'],-8) == '/migrate') {
                $response['__']['OriginalIssuer'] = substr($response['__']['OriginalIssuer'],0,-8);
            }
            // Patch Migration BACKLOG-915 End
            $response['saml:Issuer']['__v']                   = $response['__']['OriginalIssuer'];
            $response['saml:Assertion']['saml:Issuer']['__v'] = $response['__']['OriginalIssuer'];
        }

        if (isset($sourceResponse['_Consent'])) {
            $response['_Consent'] = $sourceResponse['_Consent'];
        }

        $response['samlp:Status']   = $sourceResponse['samlp:Status'];
        $response['saml:Assertion'] = $sourceResponse['saml:Assertion'];

        // remove us from the list otherwise we will as a proxy be there multiple times
        // as the assertion passes through multiple times ???
        $authenticatingAuthorities = &$response['saml:Assertion']['saml:AuthnStatement']['saml:AuthnContext']['saml:AuthenticatingAuthority'];
        foreach ((array) $authenticatingAuthorities as $key => $authenticatingAuthority) {
            if ($authenticatingAuthority['__v'] === $this->getUrl('idpMetadataService')) {
                unset($authenticatingAuthorities[$key]);
            }
        }
        if ($this->getUrl('idpMetadataService') !== $sourceResponse['saml:Issuer']['__v']) {
            $authenticatingAuthorities[] = array('__v' => $sourceResponse['saml:Issuer']['__v']);
        }

        $acs = $this->getRequestAssertionConsumer($request);

        $subjectConfirmation = &$response['saml:Assertion']['saml:Subject']['saml:SubjectConfirmation']['saml:SubjectConfirmationData'];
        $subjectConfirmation['_Recipient']    = $acs['Location'];

        // only set InResponseTo attribute when request was solicited
        if (!$this->isUnsolicitedRequest($request)) {
            $subjectConfirmation['_InResponseTo'] = $request['_ID'];
        }

        $subjectConfirmation['_NotOnOrAfter'] = $this->timeStamp($this->getConfig('NotOnOrAfter', 300));

        $response['saml:Assertion']['_ID'] = $this->getNewId();
        $response['saml:Assertion']['_IssueInstant'] = $this->timeStamp();
        $response['saml:Assertion']['saml:Conditions']['_NotBefore']    = $this->timeStamp();
        $response['saml:Assertion']['saml:Conditions']['_NotOnOrAfter'] = $this->timeStamp($this->getConfig('NotOnOrAfter', 300));

        $entity = $this->getRemoteEntity(
            $request['saml:Issuer']['__v']
        );

        if (empty($entity['TransparantIssuer'])) {
            $response['saml:Assertion']['saml:Issuer'] = array('__v' => $response['saml:Issuer']['__v']);
        }

        $response['saml:Assertion']['saml:Conditions']['saml:AudienceRestriction']['saml:Audience']['__v'] = $request['saml:Issuer']['__v'];

        return $response;
    }

    public function createNewResponse($request, $attributes = array())
    {
        $response = $this->_createBaseResponse($request);

        $soon       = $this->timeStamp($this->getConfig('NotOnOrAfter', 300));
        $sessionEnd = $this->timeStamp($this->getConfig('SessionEnd'  , 60 * 60 * 12));

        $response['saml:Assertion'] = array(
            '_xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            '_xmlns:xs' => 'http://www.w3.org/2001/XMLSchema',
            '_xmlns:samlp' => 'urn:oasis:names:tc:SAML:2.0:protocol',
            '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',

            '_ID'           => $this->getNewId(),
            '_Version'      => '2.0',
            '_IssueInstant' => $response['_IssueInstant'],

            'saml:Issuer' => array('__v' => $response['saml:Issuer']['__v']),
            'ds:Signature' => '__placeholder__',
            'saml:Subject' => array(
                'saml:NameID' => array(
                    '_SPNameQualifier'  => $this->getUrl('idpMetadataService'),
                    '_Format'           => EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_TRANSIENT,
                    '__v'               => $this->getNewId(),
                ),
                'saml:SubjectConfirmation' => array(
                    '_Method' => 'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    'saml:SubjectConfirmationData' => array(
                        '_NotOnOrAfter' => $soon,
                        '_Recipient'    => $request['_AssertionConsumerServiceURL'], # req issuer
                        '_InResponseTo' => $request['_ID'],
                    ),
                ),
            ),
            'saml:Conditions' => array(
                '_NotBefore'    => $response['_IssueInstant'],
                '_NotOnOrAfter' => $soon,
                'saml:AudienceRestriction' => array(
                    'saml:Audience' => array('__v' => $request['saml:Issuer']['__v']),
                ),
            ),
            'saml:AuthnStatement' => array(
                '_AuthnInstant'         => $response['_IssueInstant'],
                '_SessionNotOnOrAfter'  => $sessionEnd,
                'saml:SubjectLocality' => array(
                    '_Address' => $_SERVER['REMOTE_ADDR'],
                    '_DNSName' => $_SERVER['REMOTE_HOST'],
                ),
                'saml:AuthnContext' => array(
                    'saml:AuthnContextClassRef' => array('__v' => 'urn:oasis:names:tc:SAML:2.0:ac:classes:Password'),
                ),
            ),
        );

        if (!isset($attributes['binding'])) {
            $attributes['binding'] = array();
        }
        $attributes['binding'][] = $response['__']['ProtocolBinding'];
        foreach ((array) $attributes as $key => $vs) {
            foreach ($vs as $v) {
                $attributeStatement[$key][] = $v;
            }
        }

        $attributeConsumingServiceIndex = $request['_AttributeConsumingServiceIndex'];
        if ($attributeConsumingServiceIndex) {
            $attributeStatement['AttributeConsumingServiceIndex'] = "AttributeConsumingServiceIndex: $attributeConsumingServiceIndex";
        }
        else {
            $attributeStatement['AttributeConsumingServiceIndex'] = '-no AttributeConsumingServiceIndex given-';
        }

        $attributes = EngineBlock_Corto_XmlToArray::array2attributes($attributeStatement);
        if (!empty($attributes)) {
            $response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute'] = $attributes;
        }
        else {
            unset($response['saml:Assertion']['saml:AttributeStatement']);
        }

        return $response;
    }

    protected function _createBaseResponse($request)
    {
        if (isset($request['__'][EngineBlock_Corto_ProxyServer::VO_CONTEXT_PFX])) {
            $vo = $request['__'][EngineBlock_Corto_ProxyServer::VO_CONTEXT_PFX];
            $this->setVirtualOrganisationContext($vo);
        }

        $now = $this->timeStamp();
        $destinationID = $request['saml:Issuer']['__v'];

        $response = array(
            EngineBlock_Corto_XmlToArray::TAG_NAME_PFX => 'samlp:Response',
            EngineBlock_Corto_XmlToArray::PRIVATE_PFX => array(
                'paramname' => 'SAMLResponse',
                'RelayState'=> $request['__']['RelayState'],
                'destinationid' => $destinationID,
            ),
            '_xmlns:samlp' => 'urn:oasis:names:tc:SAML:2.0:protocol',
            '_xmlns:saml'  => 'urn:oasis:names:tc:SAML:2.0:assertion',

            '_ID'           => $this->getNewId(),
            '_Version'      => '2.0',
            '_IssueInstant' => $now,
            '_InResponseTo' => $request['_ID'],

            'saml:Issuer' => array('__v' => $this->getUrl('idpMetadataService', $destinationID, $request)),
            'samlp:Status' => array(
                'samlp:StatusCode' => array(
                    '_Value' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
                ),
            ),
        );

        // the original request was unsolicited, remove InResponseTo attribute
        if ($this->isUnsolicitedRequest($request)) {
            unset($response['_InResponseTo']);
        }

        $acs = $this->getRequestAssertionConsumer($request);
        $response['_Destination']           = $acs['Location'];
        $response['__']['ProtocolBinding']  = $acs['Binding'];

        if (!$response['_Destination']) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                "No Destination in request or metadata for: $destinationID"
            );
        }

        return $response;
    }

    /**
     * Returns the a custom ACS location when provided in the request
     * or the default ACS location when omitted.
     *
     * @param array $request
     */
    public function getRequestAssertionConsumer(array $request)
    {
        $remoteEntity = $this->getRemoteEntity($request['saml:Issuer']['__v']);

        // parse and validate custom ACS location
        $custom = $this->getCustomAssertionConsumer($request, $remoteEntity);
        if (is_array($custom)) {
            return $custom;
        }

        // return default ACS or fail
        return $this->getDefaultAssertionConsumer($remoteEntity);
    }

    /**
     * Returns the default ACS location for given entity
     *
     * @param array $remoteEntity
     * @return array
     * @throws EngineBlock_Corto_ProxyServer_Exception
     */
    public function getDefaultAssertionConsumer($remoteEntity)
    {
        // find first ACS URL that has a binding supported by EB
        foreach ($remoteEntity['AssertionConsumerServices'] as $acs) {
            if ($this->getBindingsModule()->isSupportedBinding($acs['Binding'])) {
                return $acs;
            }
        }

        $this->getSystemLog()
            ->attach($remoteEntity['AssertionConsumerServices'], 'AssertionConsumerServices');

        throw new EngineBlock_Corto_ProxyServer_Exception('No supported binding found for ACS');
    }

    /**
     * Returns a custom ACS location from request or false when
     * none is specified
     *
     * @param array $request
     * @param array $remoteEntity
     */
    public function getCustomAssertionConsumer(array $request, array $remoteEntity)
    {
        $requestWasSigned    = (isset($request['__']['WasSigned']) && $request['__']['WasSigned']===true);
        $requestHasCustomAcs = $this->hasCustomAssertionConsumer($request);
        $requestHasAcsIndex  = $this->hasCustomAssertionConsumerIndex($request);

        // Custom ACS Location & ProtocolBinding goes first
        if ($requestHasCustomAcs) {
            if ($requestWasSigned) {
                $this->_server->getSessionLog()->info(
                    "Using AssertionConsumerServiceLocation '{$request['_AssertionConsumerServiceURL']}' " .
                        "and ProtocolBinding '{$request['_ProtocolBinding']}' from signed request. "
                );
                return array(
                    'Location' => $request['_AssertionConsumerServiceURL'],
                    'Binding'  => $request['_ProtocolBinding'],
                );
            }
            else {
                $requestAcsIsRegisteredInMetadata = false;
                foreach ($remoteEntity['AssertionConsumerServices'] as $entityAcs) {
                    $requestAcsIsRegisteredInMetadata = (
                        $entityAcs['Location'] === $request['_AssertionConsumerServiceURL'] &&
                        $entityAcs['Binding']  === $request['_ProtocolBinding']
                    );
                    if ($requestAcsIsRegisteredInMetadata) {
                        break;
                    }
                }
                if ($requestAcsIsRegisteredInMetadata) {
                    $this->_server->getSessionLog()->info(
                        "Using AssertionConsumerServiceLocation '{$request['_AssertionConsumerServiceURL']}' " .
                            "and ProtocolBinding '{$request['_ProtocolBinding']}' from unsigned request, " .
                            "it's okay though, the ACSLocation and Binding were registered in the metadata"
                    );
                    return array(
                        'Location' => $request['_AssertionConsumerServiceURL'],
                        'Binding'  => $request['_ProtocolBinding'],
                    );
                }
                else {
                    $this->_server->getSessionLog()->notice(
                        "AssertionConsumerServiceLocation '{$request['_AssertionConsumerServiceURL']}' " .
                            "and ProtocolBinding '{$request['_ProtocolBinding']}' were mentioned in request, " .
                            "but the AuthnRequest was not signed, and the ACSLocation and Binding were not found in " .
                            "the metadata for the SP, so I am disallowed from acting upon it." .
                            "Trying the default endpoint.."
                    );
                }
            }
        }

        if ($requestHasAcsIndex) {
            $index = (int)$request['_AssertionConsumerServiceIndex'];
            if (isset($remoteEntity['AssertionConsumerServices'][$index])) {
                $this->_server->getSessionLog()->info(
                    "Using AssertionConsumerServiceIndex '$index' from request"
                );
                return $remoteEntity['AssertionConsumerServices'][$index];
            }
            else {
                $this->_server->getSessionLog()->notice(
                    "AssertionConsumerServiceIndex was mentioned in request, but we don't know any ACS by ".
                        "index '$index'? Maybe the metadata was updated and we don't have that endpoint yet? " .
                        "Trying the default endpoint.."
                );
            }
        }
    }

    /**
     * See if request specifies a custom ACS location
     *
     * @param array $request
     * @return bool
     */
    public function hasCustomAssertionConsumer(array $request)
    {
        return (isset($request['_ProtocolBinding']) &&
            isset($request['_AssertionConsumerServiceURL']));
    }

    /**
     * See if request specifies a custom ACS index
     *
     * @param array $request
     * @return bool
     */
    public function hasCustomAssertionConsumerIndex(array $request)
    {
        return isset($request['_AssertionConsumerServiceIndex']);
    }

    public function sendResponseToRequestIssuer($request, $response)
    {
        $requestIssuer = $request['saml:Issuer']['__v'];
        $sp = $this->getRemoteEntity($requestIssuer);

        if ($response['samlp:Status']['samlp:StatusCode']['_Value'] == 'urn:oasis:names:tc:SAML:2.0:status:Success') {

            $this->filterOutputAssertionAttributes($response, $request);

            return $this->getBindingsModule()->send($response, $sp);
        }
        else {
            unset($response['saml:Assertion']);
            return $this->getBindingsModule()->send($response, $sp);
        }
    }

    public function getReceivedRequestFromResponse($id)
    {
        // Check the session for a AuthnRequest with the given ID
        // Expect to get back an AuthnRequest issued by EngineBlock and destined for the IdP
        if (!$id || !isset($_SESSION[$id])) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                "Trying to find a AuthnRequest (we made and sent) with id '$id' but it is not known in this session? ".
                "This could be an unsolicited Response (which we do not support) but more likely the user lost their session",
                EngineBlock_Corto_ProxyServer_Exception::CODE_NOTICE
            );
        }

        // Get the ID of the original request (from the SP)
        if (!isset($_SESSION[$id]['_InResponseTo'])) {
            $log = $this->_server->getSessionLog();
            $log->attach($_SESSION, 'SESSION');

            throw new EngineBlock_Corto_ProxyServer_Exception(
                "ID `$id` does not have a _InResponseTo?!?",
                EngineBlock_Corto_ProxyServer_Exception::CODE_NOTICE
            );
        }
        $originalRequestId = $_SESSION[$id]['_InResponseTo'];

        if (!isset($_SESSION[$originalRequestId]['SAMLRequest'])) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                'Response has no known Request',
                EngineBlock_Corto_ProxyServer_Exception::CODE_NOTICE
            );
        }
        return $_SESSION[$originalRequestId]['SAMLRequest'];
    }

////////  ATTRIBUTE FILTERING /////////

    public function filterInputAssertionAttributes(&$response, $request)
    {
        $responseIssuer = $response['saml:Issuer']['__v'];
        $idpEntityMetadata = $this->getRemoteEntity($responseIssuer);

        $requestIssuer = $request['saml:Issuer']['__v'];
        $spEntityMetadata = $this->getRemoteEntity($requestIssuer);

        if (isset($idpEntityMetadata['filter'])) {
            $this->callAttributeFilter($idpEntityMetadata['filter'], $response, $request, $spEntityMetadata, $idpEntityMetadata);
        }
        if (isset($this->_configs['infilter'])) {
            $this->callAttributeFilter($this->_configs['infilter'], $response, $request, $spEntityMetadata, $idpEntityMetadata);
        }
    }

    public function filterOutputAssertionAttributes(&$response, $request)
    {
        $hostedMetaData = $this->_configs;
        $responseIssuer = $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['OriginalIssuer'];
        $idpEntityMetadata = $this->getRemoteEntity($responseIssuer);

        $requestIssuer = $request['saml:Issuer']['__v'];
        $spEntityMetadata = $this->getRemoteEntity($requestIssuer);

        if (isset($idpEntityMetadata['filter'])) {
            $this->callAttributeFilter($idpEntityMetadata['filter'], $response, $request, $spEntityMetadata, $idpEntityMetadata);
        }
        if (isset($hostedMetaData['outfilter'])) {
            $this->callAttributeFilter($hostedMetaData['outfilter'], $response, $request, $spEntityMetadata, $idpEntityMetadata);
        }
    }

    protected function callAttributeFilter($callback, array &$response, array $request, array $spEntityMetadata, array $idpEntityMetadata)
    {
        if (!$callback || !is_callable($callback)) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                'callback: ' . var_export($callback, true) . ' isn\'t callable'
            );
        }

        $responseAssertionAttributes = &$response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute'];

        // Take the attributes out
        $responseAttributes = EngineBlock_Corto_XmlToArray::attributes2array($responseAssertionAttributes);
        // Pass em along
        call_user_func_array($callback, array(&$response, &$responseAttributes, $request, $spEntityMetadata, $idpEntityMetadata));
        // Put em back where they belong
        $responseAssertionAttributes = EngineBlock_Corto_XmlToArray::array2attributes($responseAttributes);
        if (empty($responseAssertionAttributes)) {
            unset($response['saml:Assertion']['saml:AttributeStatement']);
        }
    }

////////  TEMPLATE RENDERING /////////

    public function renderTemplate($templateName, $vars = array(), $parentTemplates = array())
    {
        $this->getSessionLog()->info("Rendering template '$templateName'");
        if (!is_array($vars)) {
            $vars = array('content' => $vars);
        }

        $templateFileName = $templateName . '.phtml';

        ob_start();

        $this->_renderTemplate($templateFileName, $vars);

        $content = ob_get_contents();
        ob_end_clean();

        foreach ($parentTemplates as $parentTemplate) {
            $content = $this->renderTemplate(
                $parentTemplate,
                array(
                    'content' => $content,
                )
            );
        }

        $layout = $this->layout();
        $layout->content = $content;
        return $layout->render();
    }

    protected function _renderTemplate($templateFileName, $vars)
    {
        extract($vars);

        $source = $this->getTemplateSource();
        switch ($source['type'])
        {
            case self::TEMPLATE_SOURCE_MEMORY:
                if (!isset($source['arguments'][$templateFileName])) {
                    throw new EngineBlock_Corto_ProxyServer_Exception("Unable to load template '$templateFileName' from memory!");
                }

                eval('?>' . $source['arguments'][$templateFileName] . '<?');
                break;

            case self::TEMPLATE_SOURCE_FILESYSTEM;
                if (!isset($source['arguments']['FilePath'])) {
                    throw new EngineBlock_Corto_ProxyServer_Exception('Template path not set, unable to render templates from filesystem!');
                }

                $filePath = $source['arguments']['FilePath'] . $templateFileName;
                if (!file_exists($filePath)) {
                    throw new EngineBlock_Corto_ProxyServer_Exception('Template file does not exist: ' . $filePath);
                }

                include($filePath);
                break;
            default:
                throw new EngineBlock_Corto_ProxyServer_Exception(
                    'No template source set! Please configure a template source with Corto_ProxyServer->setTemplateSource()'
                );
        }
    }

//////// I/O /////////

    /**
     * Parse the HTTP URL query string and return the (raw) parameters in an array.
     *
     * We need to do this ourselves, so that we get access to the raw (url encoded) values.
     * This is required because different software can url encode to different values.
     *
     * @return array Raw parameters form the query string
     */
    public function getRawGet()
    {
        $rawGet = array();
        foreach (explode("&", $_SERVER['QUERY_STRING']) as $parameter) {
            if (preg_match("/^(.+)=(.*)$/", $parameter, $keyAndValue)) {
                 $rawGet[$keyAndValue[1]] = $keyAndValue[2];
            }
        }
        return $rawGet;
    }

    public function redirect($location, $message)
    {
        $this->getSessionLog()->info("Redirecting to $location");

        if ($this->getConfig('debug', true)) {
            $output = $this->renderTemplate('redirect', array('location'=>$location, 'message' => $message));
            $this->sendOutput($output);
        } else {
            $this->sendHeader('Location', $location);
        }

    }

    public function setCookie($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null)
    {
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    public function getCookie($name, $defaultValue = null)
    {
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        return $defaultValue;
    }

//////// UTILITIES /////////

    /**
     * Sign a Corto_XmlToArray array with XML.
     *
     * @param  $element    Element to sign
     * @return array Signed element
     */
    public function sign(array $element, $alternatePublicKey = null, $alternatePrivateKey = null)
    {
        if ($alternatePublicKey && $alternatePrivateKey) {
            $certificates['public'] = $alternatePublicKey;
            $certificates['private'] = $alternatePrivateKey;
        } else {
            $certificates = $this->getConfig('certificates', array());
        }

        $signature = array(
            '__t' => 'ds:Signature',
            '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
            'ds:SignedInfo' => array(
                '__t' => 'ds:SignedInfo',
                '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                'ds:CanonicalizationMethod' => array(
                    '_Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                ),
                'ds:SignatureMethod' => array(
                    '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
                ),
                'ds:Reference' => array(
                    0 => array(
                        '_URI' => '__placeholder__',
                        'ds:Transforms' => array(
                            'ds:Transform' => array(
                                array(
                                    '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                                ),
                                array(
                                    '_Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                                ),
                            ),
                        ),
                        'ds:DigestMethod' => array(
                            '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#sha1',
                        ),
                        'ds:DigestValue' => array(
                            '__v' => '__placeholder__',
                        ),
                    ),
                ),
            ),
            'ds:SignatureValue' => array(
                '__v' => '__placeholder__',
            ),
            'ds:KeyInfo' => array(
                'ds:X509Data' => array(
                    'ds:X509Certificate' => array(
                        '__v' => $this->getCertDataFromPem($certificates['public']),
                    ),
                ),
            ),
        );

        // Convert the XMl object to actual XML and get a reference to what we're about to sign
        $canonicalXmlDom = new DOMDocument();
        $canonicalXmlDom->loadXML(EngineBlock_Corto_XmlToArray::array2xml($element));

        // Note that the current element may not be the first or last, because we might include comments, so look for
        // the actual XML element
        $xpath = new DOMXPath($canonicalXmlDom);
        $nodes = $xpath->query('/*[@ID="' . $element['_ID'] . '"]');
        if ($nodes->length < 1) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                "Unable to sign message can't find element with id to sign?",
                EngineBlock_Corto_ProxyServer_Exception::CODE_NOTICE
            );
        }
        $canonicalXmlDom = $nodes->item(0);
        // Now do 'exclusive no-comments' XML cannonicalization
        $canonicalXml = $canonicalXmlDom->C14N(true, false);

        // Hash it, encode it in Base64 and include that as the 'Reference'
        $signature['ds:SignedInfo']['ds:Reference'][0]['ds:DigestValue']['__v'] = base64_encode(sha1($canonicalXml, true));
        $signature['ds:SignedInfo']['ds:Reference'][0]['_URI'] = "#" . $element['_ID'];

        // Now we start the actual signing, instead of signing the entire (possibly large) document,
        // we only sign the 'SignedInfo' which includes the 'Reference' hash
        $canonicalXml2Dom = new DOMDocument();
        $canonicalXml2Dom->loadXML(EngineBlock_Corto_XmlToArray::array2xml($signature['ds:SignedInfo']));
        $canonicalXml2 = $canonicalXml2Dom->firstChild->C14N(true, false);

        if (!isset($certificates['private'])) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                'Current entity has no private key, unable to sign message! Please set ["certificates"]["private"]!',
                EngineBlock_Exception::CODE_WARNING
            );
        }
        $privateKey = openssl_pkey_get_private($certificates['private']);
        if ($privateKey === false) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                "Current entity ['certificates']['private'] value is NOT a valid PEM formatted SSL private key?!? Value: " . $certificates['private']
            );
        }

        $signatureValue = null;
        openssl_sign($canonicalXml2, $signatureValue, $privateKey);
        openssl_free_key($privateKey);

        $signature['ds:SignatureValue']['__v'] = base64_encode($signatureValue);

        $element['ds:Signature'] = $signature;
        $element[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Signed'] = true;

        return $element;
    }

    public function getCertDataFromPem($pemKey)
    {
        $mapper = new EngineBlock_Corto_Mapper_CertData_Pem($pemKey);
        return $mapper->map();
    }

    /**
     * For a given url hosted by this Corto installation, get the EntityCode, remoteIdPMd5Hash and ServiceName.
     *
     * Gets the PATH_INFO from a URL like: http://host/path/corto.php/path/info
     *
     * @param string $url
     * @return array Parameters: EntityCode, ServiceName and RemoteIdPMd5Hash
     * @throws EngineBlock_Corto_ProxyServer_Exception
     */
    public function getParametersFromUrl($url)
    {
        $parameters = array(
            'EntityCode'        => 'main',
            'ServiceName'       => '',
            'RemoteIdPMd5Hash'  => '',
        );
        $urlPath = parse_url($url, PHP_URL_PATH); // /authentication/x/ServiceName[/remoteIdPMd5Hash]

        foreach ($this->_serviceToControllerMapping as $serviceName => $controllerUri) {
            if (strstr($urlPath, $controllerUri)) {
                $urlPath = str_replace($controllerUri, $serviceName, $urlPath);
                $urlParts = explode('/', $urlPath);
                $parameters['ServiceName'] = array_shift($urlParts);
                if (isset($urlParts[0])) {
                    $parameters['RemoteIdPMd5Hash'] = array_shift($urlParts);
                }
                return $parameters;
            }
        }

        throw new EngineBlock_Corto_ProxyServer_Exception("Unable to map URL '$url' to EngineBlock URL");
    }

    /**
     * Generate a SAML datetime with a given delta in seconds.
     *
     * Delta 0 gives current date and time, delta 3600 is +1 hour, delta -3600 is -1 hour.
     *
     * @param int $deltaSeconds
     * @return string
     */
    public function timeStamp($deltaSeconds = 0)
    {
        return gmdate('Y-m-d\TH:i:s\Z', time() + $deltaSeconds);
    }

    public function getNewId()
    {
        return self::ID_PREFIX . sha1(uniqid(mt_rand(), true));
    }

    public function startSession()
    {
        session_set_cookie_params(0, $this->getConfig('cookie_path', '/'), '', $this->getConfig('use_secure_cookies', true), true);
        session_name('main');
        session_start();
    }

    public function restartSession($newId, $newName)
    {
        session_write_close();

        session_id($newId);
        session_name($newName);
        session_start();
    }

    /**
     * @return EngineBlock_Log
     */
    public function getSystemLog()
    {
        if (!isset($this->_systemLog)) {
            $this->_systemLog = EngineBlock_ApplicationSingleton::getLog();
        }

        return $this->_systemLog;
    }

    public function getSessionLog()
    {
        if (isset($this->_sessionLog)) {
            return $this->_sessionLog;
        }

        if (!isset($this->_sessionLogDefault)) {
            $this->_sessionLogDefault = EngineBlock_ApplicationSingleton::getLog();
        }

        $this->_sessionLog = $this->_sessionLogDefault;

        return $this->_sessionLog;
    }

    public function setSystemLog(EngineBlock_Log $log)
    {
        $this->_systemLog = $log;
    }

    public function setSessionLogDefault($logDefault)
    {
        $this->_sessionLogDefault = $logDefault;
    }


    /**
     * Translate a string.
     *
     * Alias for 'translate'
     *
     * @example <?php echo $this->t('logged_in_as', $this->user->getDisplayName()); ?>
     *
     * @param string $from Identifier for string
     * @param string $arg1 Argument to parse in with sprintf
     * @return string
     */
    public function t($from, $arg1 = null)
    {
        return call_user_func_array(array($this, 'translate'), func_get_args());
    }

    /**
     * Translate a string.
     *
     * Has an alias called 't'.
     *
     * @example <?php echo $this->translate('logged_in_as', $this->user->getDisplayName()); ?>
     *
     * @param string $from Identifier for string
     * @param string $arg1 Argument to parse in with sprintf
     * @return string
     */
    public function translate($from, $arg1 = null)
    {
        $translator = EngineBlock_ApplicationSingleton::getInstance()->getTranslator()->getAdapter();

        $arguments = func_get_args();
        $arguments[0] = $translator->translate($from);
        return call_user_func_array('sprintf', $arguments);
    }

    /**
     * Return the language.
     *
     * @example <?php echo $this->language(); ?>
     *
     * @return string
     */
    public function language()
    {
        $translator = EngineBlock_ApplicationSingleton::getInstance()->getTranslator()->getAdapter();

        return $translator->getLocale();
    }

    public function layout()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getLayout();
    }
}
