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

define('ENGINEBLOCK_FOLDER_LIBRARY_CORTO', ENGINEBLOCK_FOLDER_LIBRARY . 'Corto/library/');
require ENGINEBLOCK_FOLDER_LIBRARY_CORTO . 'Corto/ProxyServer.php';

spl_autoload_register(array('EngineBlock_Corto_Adapter', 'cortoAutoLoad'));

class EngineBlock_Exception_UserNotMember extends EngineBlock_Exception
{
}

class EngineBlock_Exception_InvalidConnection extends EngineBlock_Exception
{

}

class EngineBlock_Exception_ReceivedErrorStatusCode extends EngineBlock_Exception
{

}

class EngineBlock_Corto_Adapter 
{
    const DEFAULT_HOSTED_ENTITY = 'main';

    const IDENTIFYING_MACE_ATTRIBUTE = 'urn:mace:dir:attribute-def:uid';

    const VO_NAME_ATTRIBUTE = 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.2';

    protected $_collaborationAttributes = array();

    /**
     * @var EngineBlock_Corto_CoreProxy
     */
    protected $_proxyServer;
    
    /**
     * @var String The name of the currently hosted Corto hosted entity.
     */
    protected $_hostedEntity;
    
    /**
     * @var String the name of the Virtual Organisation context (if any)
     */
    protected $_voContext = NULL;

    /**
     * @var mixed Callback called on Proxy server after configuration
     */
    protected $_remoteEntitiesFilter = NULL;
    
    public function __construct($hostedEntity = NULL) {
        
        if ($hostedEntity == NULL) {
            $hostedEntity = self::DEFAULT_HOSTED_ENTITY;
        }

        $this->_hostedEntity = $hostedEntity;
    }

    /**
     * Simple autoloader for Corto, tries to autoload all classes with Corto_ from the Corto/library folder.
     *
     * @static
     * @param string $className Class name to autoload
     * @return bool Whether autoloading succeeded
     */
    public static function cortoAutoLoad($className)
    {
        if (strpos($className, 'Corto_') !== 0) {
            return false;
        }

        $classParts = explode('_', $className);
        $filePath = implode('/', $classParts) . '.php';

        include ENGINEBLOCK_FOLDER_LIBRARY_CORTO . $filePath;

        return true;
    }

    public function singleSignOn($idPProviderHash)
    {
        $this->_setRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesByRequestSp'));
        $this->_callCortoServiceUri('singleSignOnService', $idPProviderHash);
    }

    protected function _filterRemoteEntitiesByRequestSp(array $entities, EngineBlock_Corto_CoreProxy $proxyServer)
    {
        /**
         * Use the binding module to get the request, then
         * store it in _REQUEST so Corto will think it has received it
         * from an internal binding, because if Corto would try to
         * get the request again from the binding module, it would fail.
         */
        $request = $_REQUEST['SAMLRequest'] = $proxyServer->getBindingsModule()->receiveRequest();
        $spEntityId = $request['saml:Issuer']['__v'];
        return $this->_getServiceRegistryAdapter()->filterEntitiesBySp(
            $entities,
            $spEntityId
        );
    }

    protected function _filterRemoteEntitiesBySpQueryParam(array $entities, EngineBlock_Corto_CoreProxy $proxyServer)
    {
        $claimedSpEntityId = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getQueryParameter('sp-entity-id');
        if (!$claimedSpEntityId) {
            return $entities;
        }

        return $this->_getServiceRegistryAdapter()->filterEntitiesBySp(
            $entities,
            $claimedSpEntityId
        );
    }

    public function idPMetadata()
    {
        $this->_callCortoServiceUri('idPMetadataService');
    }

    public function sPMetadata()
    {
        $this->_callCortoServiceUri('sPMetadataService');
    }

    public function consumeAssertion()
    {
        $this->_callCortoServiceUri('assertionConsumerService');
    }

    public function idPsMetadata()
    {
        $this->_setRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesBySpQueryParam'));
        $this->_callCortoServiceUri('idPsMetaDataService');
    }

    public function processWayf()
    {
        $this->_callCortoServiceUri('continueToIdp');
    }

    public function processConsent()
    {
        $this->_callCortoServiceUri('processConsentService');
    }

    public function processedAssertionConsumer()
    {
        $this->_callCortoServiceUri('processedAssertionConsumerService');
    }

    public function getVirtualOrganisationContext()
    {
        return $this->_voContext;
    }
    
    public function setVirtualOrganisationContext($virtualOrganisation)
    {
        $this->_voContext = $virtualOrganisation;
    }

    protected function _callCortoServiceUri($serviceName, $idPProviderHash = "")
    {
        $cortoUri = $this->_getCortoUri($serviceName, $idPProviderHash);

        $this->_initProxy();

        $this->_proxyServer->serveRequest($cortoUri);
        $this->_processProxyServerResponse();

        unset($this->_proxyServer);
    }

    protected function _getCortoUri($cortoServiceName, $idPProviderHash = "")
    {
        $cortoHostedEntity  = $this->_getHostedEntity();
        $cortoIdPHash       = $idPProviderHash;
        $result =  '/' . $cortoHostedEntity . ($cortoIdPHash ? '_' . $cortoIdPHash : '') . '/' . $cortoServiceName;
        
        return $result;
    }

    protected function _initProxy()
    {
        if (isset($this->_proxyServer)) {
            return;
        }

        $proxyServer = $this->_getCoreProxy();

        $this->_configureProxyServer($proxyServer);

        $this->_proxyServer = $proxyServer;
    }

    protected function _getCoreProxy()
    {
        return new EngineBlock_Corto_CoreProxy();
    }

    protected function _configureProxyServer(EngineBlock_Corto_CoreProxy $proxyServer)
    {
        $proxyServer->setSystemLog($this->_getSystemLog());
        $proxyServer->setSessionLogDefault($this->_getSessionLog());

        $application = EngineBlock_ApplicationSingleton::getInstance();
        
        if ($this->_voContext!=null) {
            $proxyServer->setVirtualOrganisationContext($this->_voContext);
        }

        $proxyServer->setConfigs(array(
            'debug' => $application->getConfigurationValue('debug', false),
            'trace' => $application->getConfigurationValue('debug', false),
            'ConsentStoreValues' => $this->_getConsentConfigurationValue('storeValues', true),
            'NoSupportedIDPError' => 'user',
        ));

        $attributes = array();
        require ENGINEBLOCK_FOLDER_APPLICATION . 'configs/attributes.inc.php';
        $proxyServer->setAttributeMetadata($attributes);

        $proxyServer->setHostedEntities(array(
            $proxyServer->getHostedEntityUrl($this->_hostedEntity) => array(
                'certificates' => array(
                    'public'    => $application->getConfiguration()->encryption->key->public,
                    'private'   => $application->getConfiguration()->encryption->key->private,
                ),
                // Note that we use an input filter because consent requires a presistent NameID
                // and we only get that after provisioning
                'infilter'  => array($this, 'filterInputAttributes'),
                'outfilter' => array($this, 'filterOutputAttributes'),
                'Processing' => array(
                    'Consent' => array(
                        'Binding'  => 'INTERNAL',
                        'Location' => $proxyServer->getHostedEntityUrl($this->_hostedEntity, 'provideConsentService'),
                    ),
                ),
                'keepsession' => true,
                'idpMetadataValidUntilSeconds' => 86400, // This sets the time (in seconds) the entity metadata is valid.
            ),
        ));

        /**
         * Add ourselves as valid IdP
         */
        $engineBlockEntities = array(
            $proxyServer->getHostedEntityUrl($this->_hostedEntity, 'idPMetadataService') => array(
                'certificates' => array(
                    'public'    => $application->getConfiguration()->encryption->key->public,
                    'private'   => $application->getConfiguration()->encryption->key->private,
                ),
            )
        );
        $remoteEntities = $this->_getRemoteEntities();
        $proxyServer->setRemoteEntities($remoteEntities + $engineBlockEntities);

        $proxyServer->setTemplateSource(
            Corto_ProxyServer::TEMPLATE_SOURCE_FILESYSTEM,
            array('FilePath'=>ENGINEBLOCK_FOLDER_MODULES . 'Authentication/View/Proxy/')
        );
        
        $proxyServer->setBindingsModule(new EngineBlock_Corto_Module_Bindings($proxyServer));
        $proxyServer->setServicesModule(new EngineBlock_Corto_Module_Services($proxyServer));

        if ($this->_remoteEntitiesFilter) {
            $proxyServer->setRemoteEntities(call_user_func_array(
                $this->_remoteEntitiesFilter,
                array(
                    $proxyServer->getRemoteEntities(),
                    $proxyServer
                )
            ));
        }
    }

    protected function _getSystemLog()
    {
        return $this->_getLog();
    }

    protected function _getSessionLog()
    {
        return $this->_getLog();
    }

    protected function _getLog()
    {
        return new EngineBlock_Corto_Log_Adapter();
    }

    protected function _getConsentConfigurationValue($name, $default = null)
    {
        $configuration = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
        if (!isset($configuration->authentication)) {
            return $default;
        }
        if (!isset($configuration->authentication->consent)) {
            return $default;
        }
        if (!isset($configuration->authentication->consent->$name)) {
            return $default;
        }
        return $configuration->authentication->consent->$name;
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
    public function filterInputAttributes(array &$response,
        array &$responseAttributes,
        array $request,
        array $spEntityMetadata,
        array $idpEntityMetadata
    )
    {
        if ($response['samlp:Status']['samlp:StatusCode']['_Value']!=='urn:oasis:names:tc:SAML:2.0:status:Success') {
            // Idp returned an error
            throw new EngineBlock_Exception_ReceivedErrorStatusCode(
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

        // Is a guest user?
        $responseAttributes = $this->_addSurfPersonAffiliationAttribute($responseAttributes, $idpEntityMetadata);

        // Provisioning of the user account
        $subjectId = $this->_provisionUser($responseAttributes, $spEntityMetadata, $idpEntityMetadata);
        $_SESSION['subjectId'] = $subjectId;

        // Adjust the NameID in the OLD response (for consent), set the collab:person uid
        $response['saml:Assertion']['saml:Subject']['saml:NameID'] = array(
            '_Format'          => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
            '__v'              => $subjectId
        );

        $this->_handleVirtualOrganizationResponse($request, $subjectId, $idpEntityMetadata["EntityId"]);

        if ($this->getVirtualOrganisationContext()) {
            $responseAttributes = $this->_addVoNameAttribute($responseAttributes, $this->getVirtualOrganisationContext());
        }

        $this->_trackLogin($spEntityMetadata, $idpEntityMetadata, $subjectId);
    }

    public function validateSpIdpConnection($spEntityId, $idpEntityId)
    {
        $serviceRegistryAdapter = $this->_getServiceRegistryAdapter();
        if (!$serviceRegistryAdapter->isConnectionAllowed($spEntityId, $idpEntityId)) {
            throw new EngineBlock_Exception_InvalidConnection(
                "Received a response from an IDP that is not allowed to connect to the requesting SP"
            );
        }
    }

    protected function _mapOidsToUrns(array $responseAttributes, array $idpEntityMetadata)
    {
        $mapper = new EngineBlock_AttributeMapper_Oid2Urn();
        return $mapper->map($responseAttributes);
    }

    protected function _provisionUser($attributes, $spEntityMetadata, $idpEntityMetadata)
    {
        return $this->_getProvisioning()->provisionUser($attributes, $spEntityMetadata, $idpEntityMetadata);
    }

    protected function _handleVirtualOrganizationResponse($request, $subjectId, $idpEntityId)
    {
        // Determine a Virtual Organization context
        $vo = NULL;

        // In filter stage we need to take a look at the VO context
        if (isset($request['__'][EngineBlock_Corto_CoreProxy::VO_CONTEXT_KEY])) {
            $vo = $request['__'][EngineBlock_Corto_CoreProxy::VO_CONTEXT_KEY];
            $this->setVirtualOrganisationContext($vo);
        }

        // If in VO context, validate the user's membership
        if (!is_null($vo)) {
            if (!$this->_validateVOMembership($subjectId, $vo, $idpEntityId)) {
                throw new EngineBlock_Exception_UserNotMember("User not a member of VO $vo");
            }
        }
    }

    protected function _trackLogin($spEntityId, $idpEntityId, $subjectId)
    {
        $tracker = new EngineBlock_Tracker();
        $tracker->trackLogin($spEntityId, $idpEntityId, $subjectId);
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
    public function filterOutputAttributes(array &$response,
        array &$responseAttributes,
        array $request,
        array $spEntityMetadata,
        array $idpEntityMetadata
    )
    {
        $subjectId = $_SESSION['subjectId'];

        // Attribute Aggregation
        $responseAttributes = $this->_enrichAttributes(
            $idpEntityMetadata["EntityId"],
            $spEntityMetadata["EntityId"],
            $subjectId,
            $responseAttributes
        );

        // Attribute / NameId / Response manipulation / mangling
        $this->_manipulateAttributes(
            $subjectId,
            $responseAttributes,
            $response
        );

        // Adjust the NameID in the NEW response, set the collab:person uid
        $response['saml:Assertion']['saml:Subject']['saml:NameID'] = array(
            '_Format'          => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
            '__v'              => $subjectId
        );

        $responseAttributes = $this->_mapUrnsToOids($responseAttributes, $spEntityMetadata);
    }

    protected function _addSurfPersonAffiliationAttribute($responseAttributes, $idpEntityMetadata)
    {
        // Determine guest status
        if (!isset($idpEntityMetadata['GuestQualifier'])) {
            throw new EngineBlock_Exception('No GuestQualifier for IdP? ' . var_export($idpEntityMetadata, true));
        }

        switch ($idpEntityMetadata['GuestQualifier']) {
            case 'None':
                $responseAttributes['urn:oid:1.3.6.1.4.1.1076.20.100.10.10.1'] = array(
                    0 => 'member',
                );
                return $responseAttributes;

            case 'Some':
                if (!isset($responseAttributes['urn:oid:1.3.6.1.4.1.1076.20.100.10.10.1'][0])) {
                    ebLog()->warn("Idp guestQualifier is set to 'Some' however, the surfPersonAffiliation attribute was not provided, setting it to 'guest' and continuing". var_export($idpEntityMetadata, true) . var_export($responseAttributes, true));
                    $responseAttributes['urn:oid:1.3.6.1.4.1.1076.20.100.10.10.1'] = array(
                        0 => 'guest',
                    );
                }
                return $responseAttributes;

            default:
            case 'All':
                $responseAttributes['urn:oid:1.3.6.1.4.1.1076.20.100.10.10.1'] = array(
                    0 => 'guest',
                );
                return $responseAttributes;
        }
    }

    /**
     * Enrich the attributes with attributes
     *
     * @param  $attributes
     * @return array
     */
    protected function _enrichAttributes($idpEntityId, $spEntityId, $subjectId, array $attributes)
    {
        $aggregator = $this->_getAttributeAggregator(
            $this->_getAttributeProviders($idpEntityId, $spEntityId)
        );
        $aggregatedAttributes = $aggregator->getAttributes(
            $subjectId
        );
        return array_merge_recursive($attributes, $aggregatedAttributes);
    }

    protected function _manipulateAttributes(&$subjectId, array &$attributes, array &$response)
    {
        $manipulators = $this->_getAttributeManipulators();
        foreach ($manipulators as $manipulator) {
            $manipulator->manipulate($subjectId, $attributes, $response);
        }
    }

    protected function _mapUrnsToOids(array $responseAttributes, array $spEntityMetadata)
    {
        if (!isset($spEntityMetadata['ExpectsOids']) || !$spEntityMetadata['ExpectsOids']) {
            return $responseAttributes;
        }

        $mapper = new EngineBlock_AttributeMapper_Urn2Oid();
        return $mapper->map($responseAttributes);
    }

    protected function _getRemoteEntities()
    {
        $serviceRegistry = $this->_getServiceRegistryAdapter();
        $metadata = $serviceRegistry->getRemoteMetaData();
        return $metadata;
    }

    protected function _getServiceRegistryAdapter()
    {
        return new EngineBlock_Corto_ServiceRegistry_Adapter(
            new EngineBlock_ServiceRegistry_CacheProxy()
        );
    }

    protected function _processProxyServerResponse()
    {
        $response = EngineBlock_ApplicationSingleton::getInstance()->getHttpResponse();

        $this->_processProxyServerResponseHeaders($response);
        $this->_processProxyServerResponseBody($response);
    }

    protected function _processProxyServerResponseHeaders(EngineBlock_Http_Response $response)
    {
        $proxyHeaders = $this->_proxyServer->getHeaders();
        foreach ($proxyHeaders as $headerName => $headerValue) {
            if ($headerName === EngineBlock_Http_Response::HTTP_HEADER_RESPONSE_LOCATION) {
                $response->setRedirectUrl($headerValue);
            }
            else {
                $response->setHeader($headerName, $headerValue);
            }
        }
    }

    protected function _processProxyServerResponseBody(EngineBlock_Http_Response $response)
    {
        $proxyOutput = $this->_proxyServer->getOutput();
        $response->setBody($proxyOutput);
    }

    protected function _getAttributeProviders($idpEntityId, $spEntityId)
    {
        $providers = array();
        if (isset($this->_voContext)) {
            $providers[] = new EngineBlock_AttributeProvider_VoManage($this->_voContext, $spEntityId, $idpEntityId);
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

    protected function _getProvisioning()
    {
        return new EngineBlock_Provisioning();
    }
    
    protected function _getHostedEntity()
    {
        return $this->_hostedEntity;
    }

    protected function _setRemoteEntitiesFilter($callback)
    {
        $this->_remoteEntitiesFilter = $callback;
        return $this;
    }

    protected function _addVoNameAttribute($responseAttributes, $voContext)
    {
        $responseAttributes[self::VO_NAME_ATTRIBUTE] = $voContext;

        return $responseAttributes;
    }
}
