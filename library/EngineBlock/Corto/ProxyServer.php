<?php

/**
 *
 */
require 'XmlToArray.php';
require 'Log/Dummy.php';

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

    protected $_headers = array();
    protected $_output;

    protected $_voContext = null;

    protected $_requestArray;
    protected $_responseArray;

    protected $_server;
    protected $_systemLog;
    protected $_sessionLog;
    protected $_sessionLogDefault;

    protected $_hostedPath = "/";

    protected $_configs;
    protected $_entities = array(
        'current'=>array(),
        'hosted'=>array(),
        'remote'=>array(),
    );
    protected $_attributes = array();
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

    public function setConfigs($configs)
    {
        $this->_configs = $configs;
        return $this;
    }

    public function setAttributeMetadata(array $attributes)
    {
        $this->_attributes = $attributes;
        return $this;
    }

    public function getAttributeName($uid, $ietfLanguageTag = 'en_US')
    {
        $name = $this->_getAttributeDataType('Name', $uid, $ietfLanguageTag);
        if (!$name) {
            $name = $uid;
        }
        return $name;
    }

    public function getAttributeDescription($uid, $ietfLanguageTag = 'en_US')
    {
        $description = $this->_getAttributeDataType('Description', $uid, $ietfLanguageTag);
        if (!$description) {
            $description = '';
        }
        return $description;
    }

    protected function _getAttributeDataType($type, $name, $ietfLanguageTag = 'en_US')
    {
        if (isset($this->_attributes[$name][$type][$ietfLanguageTag])) {
            return $this->_attributes[$name][$type][$ietfLanguageTag];
        }
        // @todo warn the system! requested a unkown UID or langauge...
        return $name;
    }

    public function getCurrentEntity()
    {
        return $this->_entities['current'];
    }

    public function setHostedEntities($entities)
    {
        $this->_entities['hosted'] = $entities;
    }

    public function setHostedPath($path)
    {
        $this->_hostedPath = $path;
    }

    public function getHostedEntities()
    {
        return $this->_entities['hosted'];
    }

    public function getHostedEntity($entityId)
    {
        if (isset($this->_entities['hosted'][$entityId])) {
            return $entityId;
        }
        return false;
    }

    public function getCurrentEntityUrl($serviceName = "", $remoteEntityId = "", $request = "")
    {
        return $this->getHostedEntityUrl(
            $this->_entities['current']['EntityCode'],
            $serviceName,
            $remoteEntityId,
            $request
        );
    }

    public function getCurrentEntitySetting($name, $default = null)
    {
        if (isset($this->_entities['current'][$name])) {
            return $this->_entities['current'][$name];
        }
        return $default;
    }

    public function selfUrl($entityid = null)
    {
        return  'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $this->selfPath($entityid);
    }

	public function selfPath($entityid = null)
	{
		return $_SERVER['SCRIPT_NAME'] . ($entityid ? '/' . $entityid : '');
	}

	public function selfDestination() {
		return self::selfUrl() . $_SERVER['PATH_INFO'];
	}

    public function getHostedEntityUrl($entityCode, $serviceName = "", $remoteEntityId = "")
    {
        $entityPart = $entityCode;
        if ($remoteEntityId) {
            $entityPart .= '_' . md5($remoteEntityId);
        }
        return 'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HTTP_HOST']
            . $_SERVER['SCRIPT_NAME'] . '/' . $entityPart  . ($serviceName ? '/': '') . $serviceName;
    }

    public function getRemoteEntity($entityId)
    {
        if (!isset($this->_entities['remote'][$entityId])) {
            throw new EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException($entityId);
        }
        $entity = $this->_entities['remote'][$entityId];
        $entity['EntityId'] = $entityId;
        return $entity;
    }

    public function getRemoteEntities()
    {
        return array_intersect($this->_entities['remote'], array($this->_entities['current']));
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

    public function serveRequest($uri)
    {
        $parameters = $this->_getParametersFromUri($uri);
        $this->setCurrentEntity($parameters['EntityCode'], $parameters['RemoteIdPMd5']);

        $this->startSession();
        $this->getSessionLog()->debug("Started request with $uri, resulting in parameters: ". var_export($parameters, true));

        $serviceName = $parameters['ServiceName'];
        $this->getSessionLog()->debug("Calling service '$serviceName'");
        $this->getServicesModule()->serve($serviceName);
        $this->getSessionLog()->debug("Done calling service '$serviceName'");
    }

    protected function _getParametersFromUri($uri)
    {
        $parameters = array(
            'EntityCode'    => '',
            'ServiceName'   => '',
            'RemoteIdPMd5'  => '',
        );

        if ($uri) {
            // From /hostedEntity/requestedService get the hosted entity code and the requested service
            $entityCodeAndService = preg_split('/\//', $uri, 0, PREG_SPLIT_NO_EMPTY);
            if (isset($entityCodeAndService[0])) {
                // From the hosted entity name like entity name_myidp, get a hosted IDP identifier (myIdp in the example).
                $entityComponents = preg_split('/_/', $entityCodeAndService[0], 0, PREG_SPLIT_NO_EMPTY);

                $parameters['EntityCode'] = $entityComponents[0];
                if (isset($entityComponents[1])) {
                    $parameters['RemoteIdPMd5'] = $entityComponents[1];
                }
            }
            if (isset($entityCodeAndService[1])) {
                $parameters['ServiceName'] = $entityCodeAndService[1];
            }
        }

        // Defaults
        if (!$parameters['EntityCode']) {
            $parameters['EntityCode'] = 'main';
        }
        if (!$parameters['ServiceName']) {
            $parameters['ServiceName'] = 'demoApp';
        }

        return $parameters;
    }

    public function setCurrentEntity($entityCode, $remoteIdPMd5 = "")
    {
        $entityId = $this->getHostedEntityUrl($entityCode);
        $hostedEntity = array();
        if (isset($this->_entities['hosted'][$entityId])) {
            $hostedEntity = $this->_entities['hosted'][$entityId];
        }

        $hostedEntity['EntityId']   = $entityId;
        $hostedEntity['EntityCode'] = $entityCode;

        if ($remoteIdPMd5) {
            $remoteEntityIds = array_keys($this->_entities['remote']);
            foreach ($remoteEntityIds as $remoteEntityId) {
                if (md5($remoteEntityId) === $remoteIdPMd5) {
                    $hostedEntity['Idp'] = $remoteEntityId;
                    $hostedEntity['TransparentProxy'] = true;
                    $this->getSessionLog()->debug("Detected pre-selection of $remoteEntityId as IdP, switching to transparant mode");
                    break;
                }
            }
            if (!isset($hostedEntity['Idp'])) {
                $this->getSessionLog()->warn("Unable to map $remoteIdPMd5 to a remote entity!");
            }
        }

        $this->_entities['current'] = $hostedEntity;
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
            '_AssertionConsumerServiceURL'      => $this->getCurrentEntityUrl('assertionConsumerService'),
            '_ProtocolBinding'                  => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',

            'saml:Issuer' => array('__v' => $this->getCurrentEntityUrl('sPMetadataService')),
            'ds:Signature' => '__placeholder__',
            'samlp:NameIDPolicy' => array(
                '_Format'       => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
                '_AllowCreate'  => 'true',
            ),
        );

        if (isset($originalRequest['_AttributeConsumingServiceIndex'])) {
            $request['_AttributeConsumingServiceIndex'] = $originalRequest['_AttributeConsumingServiceIndex'];
        }

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
            if ($authenticatingAuthority['__v'] === $this->getCurrentEntity()) {
                unset($authenticatingAuthorities[$key]);
            }
        }
        if ($this->getCurrentEntityUrl('idPMetadataService') !== $sourceResponse['saml:Issuer']['__v']) {
            $authenticatingAuthorities[] = array('__v' => $sourceResponse['saml:Issuer']['__v']);
        }

        $acs = $this->_getRequestAssertionConsumer($request);

        $subjectConfirmation = &$response['saml:Assertion']['saml:Subject']['saml:SubjectConfirmation']['saml:SubjectConfirmationData'];
        $subjectConfirmation['_Recipient']    = $acs['Location'];
        $subjectConfirmation['_InResponseTo'] = $request['_ID'];
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
                    '_SPNameQualifier'  => $this->getCurrentEntityUrl(),
                    '_Format'           => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
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
        if (isset($request['__'][EngineBlock_Corto_CoreProxy::VO_CONTEXT_PFX])) {
            $vo = $request['__'][EngineBlock_Corto_CoreProxy::VO_CONTEXT_PFX];
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

            'saml:Issuer' => array('__v' => $this->getCurrentEntityUrl('idPMetadataService', $destinationID, $request)),
            'samlp:Status' => array(
                'samlp:StatusCode' => array(
                    '_Value' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
                ),
            ),
        );

        $acs = $this->_getRequestAssertionConsumer($request);
        $response['_Destination']           = $acs['Location'];
        $response['__']['ProtocolBinding']  = $acs['Binding'];

        if (!$response['_Destination']) {
            throw new EngineBlock_Corto_ProxyServer_Exception("No Destination in request or metadata for: $destinationID");
        }

        return $response;
    }

    protected function _getRequestAssertionConsumer(array $request)
    {
        $acs = array();
        if (isset($request['_AssertionConsumerServiceURL']) &&
            isset($request['_ProtocolBinding']) &&
            isset($request['__']['WasSigned']) &&
            $request['__']['WasSigned']===true) {

            $acs['Location'] = $request['_AssertionConsumerServiceURL'];
            $acs['Binding']  = $request['_ProtocolBinding'];
        } else {
            $remoteEntity = $this->getRemoteEntity($request['saml:Issuer']['__v']);
            $index = 0;
            if (isset($request['_AssertionConsumerServiceIndex'])) {
                $index = (int)$request['_AssertionConsumerServiceIndex'];
                if (isset($remoteEntity['AssertionConsumerServices'][$index])) {
                    return $remoteEntity['AssertionConsumerServices'][$index];
                }
                else {
                    $index = 0;
                    $this->_server->getSessionLog()->warn(
                        "AssertionConsumerServiceIndex was mentioned in request, but we don't know any ACS by ".
                            "index '$index'? Maybe the metadata was updated and we don't have that endpoint yet? " .
                            "Trying the default endpoint.."
                    );
                }
            }
            $acs = $remoteEntity['AssertionConsumerServices'][$index];
        }
        return $acs;
    }

    function sendResponseToRequestIssuer($request, $response)
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
        if (!$id || !isset($_SESSION[$id])) {
            throw new EngineBlock_Corto_ProxyServer_Exception("Unknown id ($id) in InResponseTo attribute?!?");
        }

        // Get the ID of the original request (from the SP)
        if (!isset($_SESSION[$id]['_InResponseTo'])) {
            $this->_server->getSessionLog()->debug(print_r($_SESSION, true));
            throw new EngineBlock_Corto_ProxyServer_Exception("ID `$id` does not have a _InResponseTo?!?");
        }
        $originalRequestId = $_SESSION[$id]['_InResponseTo'];

        if (!isset($_SESSION[$originalRequestId]['SAMLRequest'])) {
            throw new EngineBlock_Corto_ProxyServer_Exception('Response has no known Request');
        }
        return $_SESSION[$originalRequestId]['SAMLRequest'];
    }

////////  ATTRIBUTE FILTERING /////////

    public function filterInputAssertionAttributes(&$response, $request)
    {
        $hostedEntityMetaData = $this->_entities['current'];

        $responseIssuer = $response['saml:Issuer']['__v'];
        $idpEntityMetadata = $this->getRemoteEntity($responseIssuer);

        $requestIssuer = $request['saml:Issuer']['__v'];
        $spEntityMetadata = $this->getRemoteEntity($requestIssuer);

        if (isset($idpEntityMetadata['filter'])) {
            $this->callAttributeFilter($idpEntityMetadata['filter'], $response, $request, $spEntityMetadata, $idpEntityMetadata);
        }
        if (isset($hostedEntityMetaData['infilter'])) {
            $this->callAttributeFilter($hostedEntityMetaData['infilter'], $response, $request, $spEntityMetadata, $idpEntityMetadata);
        }
    }

    public function filterOutputAssertionAttributes(&$response, $request)
    {
        $hostedMetaData = $this->_entities['current'];
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
            // @todo Non existing callbacks shouldn't give an exception, just a warning...
            throw new EngineBlock_Corto_ProxyServer_Exception('callback: ' . var_export($callback, true) . ' isn\'t callable');
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
        $this->getSessionLog()->debug("Rendering template '$templateName'");
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
                throw new EngineBlock_Corto_ProxyServer_Exception('No template source set! Please configure a template source with Corto_ProxyServer->setTemplateSource()');
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
        $this->getSessionLog()->debug("Redirecting to $location");

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
            $certificates = $this->getCurrentEntitySetting('certificates', array());
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
            throw new EngineBlock_Corto_ProxyServer_Exception("Unable to sign message can't find element with id to sign?");
        }
        $canonicalXmlDom = $nodes->item(0);
        // Now do 'exclusive no-comments' XML cannonicalization
        $canonicalXml = $canonicalXmlDom->C14N(true, false);

        // Hash it, encode it in Base64 and include that as the 'Reference'
        $signature['ds:SignedInfo']['ds:Reference'][0]['ds:DigestValue']['__v'] = base64_encode(sha1($canonicalXml, TRUE));
        $signature['ds:SignedInfo']['ds:Reference'][0]['_URI'] = "#" . $element['_ID'];

        // Now we start the actual signing, instead of signing the entire (possibly large) document,
        // we only sign the 'SignedInfo' which includes the 'Reference' hash
        $canonicalXml2Dom = new DOMDocument();
        $canonicalXml2Dom->loadXML(EngineBlock_Corto_XmlToArray::array2xml($signature['ds:SignedInfo']));
        $canonicalXml2 = $canonicalXml2Dom->firstChild->C14N(true, false);

        if (!isset($certificates['private'])) {
            throw new EngineBlock_Corto_ProxyServer_Exception('Current entity has no private key, unable to sign message! Please set ["certificates"]["private"]!');
        }
        $privateKey = openssl_pkey_get_private($certificates['private']);
        if ($privateKey === false) {
            throw new EngineBlock_Corto_ProxyServer_Exception("Current entity ['certificates']['private'] value is NOT a valid PEM formatted SSL private key?!? Value: " . $certificates['private']);
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
     */
    public function getParametersFromUrl($url)
    {
        $urlPath = parse_url($url, PHP_URL_PATH); // /path/corto.php/EntityCode_remoteIdPMd5Hash/ServiceName
        $currentPath = $_SERVER['SCRIPT_NAME']; // /path/corto.php

        if (strpos($urlPath, $currentPath) !== 0) {
            $message = "Unable to get Parameters from URL: '$url' for Corto installation at path: '$currentPath'";
            throw new EngineBlock_Corto_ProxyServer_Exception($message);
        }

        $uri = substr($currentPath, strlen($currentPath));
        return $this->_getParametersFromUri($uri);
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
        session_name($this->_entities['current']['EntityCode']);
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
     * @return EngineBlock_Corto_Log_Interface
     */
    public function getSystemLog()
    {
        if (!isset($this->_systemLog)) {
            $this->_systemLog = new EngineBlock_Corto_Log_Dummy();
        }

        return $this->_systemLog;
    }

    public function getSessionLog()
    {
        if (isset($this->_sessionLog)) {
            return $this->_sessionLog;
        }

        if (!isset($this->_sessionLogDefault)) {
            $this->_sessionLogDefault = new EngineBlock_Corto_Log_Dummy();
        }

        $sessionLog = $this->_sessionLogDefault;
        $sessionLog->setId(session_id());
        $this->_sessionLog =$sessionLog;
        return $this->_sessionLog;
    }

    public function setSystemLog(EngineBlock_Corto_Log_Interface $log)
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
