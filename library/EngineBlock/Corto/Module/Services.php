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

class EngineBlock_Corto_Module_Services_Exception extends EngineBlock_Corto_ProxyServer_Exception
{
}

class EngineBlock_Corto_Module_Services_SessionLostException extends EngineBlock_Corto_ProxyServer_Exception
{
}

class EngineBlock_Corto_Module_Services extends EngineBlock_Corto_Module_Abstract
{
    const DEFAULT_REQUEST_BINDING  = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect';
    const DEFAULT_RESPONSE_BINDING = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';

    const RESPONSE_CACHE_TYPE_IN  = 'in';
    const RESPONSE_CACHE_TYPE_OUT = 'out';

    // @todo move this to translations?
    const META_TOU_COMMENT = 'Use of this metadata is subject to the Terms of Use at http://www.edugain.org/policy/metadata-tou_1_0.txt';

    const INTRODUCTION_EMAIL = 'introduction_email';

    public function serve($service)
    {
        if (method_exists($this, $service)) {
            return $this->$service();
        }
        else if (method_exists($this, $service . 'Service')) {
            $service .= 'Service';
            return $this->$service();
        }
        else if (class_exists('EngineBlock_Corto_Module_Service_' . $service, true)) {
            $className = 'EngineBlock_Corto_Module_Service_' . $service;
            /** @var $service EngineBlock_Corto_Module_Service_Abstract */
            $service = new $className();
            return $service->serve();
        }
        throw new EngineBlock_Corto_Module_Services_Exception("Unable to load service!");
    }

    /**
     * Handle a Single Sign On request (Authentication Request)
     * @return void
     */
    public function singleSignOnService()
    {
    $request = $this->_server->getBindingsModule()->receiveRequest();
        $request[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Transparent'] = $this->_server->getCurrentEntitySetting('TransparentProxy', false);

        // The request may specify it ONLY wants a response from specific IdPs
        // or we could have it configured that the SP may only be serviced by specific IdPs
        $scopedIdps = $this->_getScopedIdPs($request);

        $cacheResponseSent = $this->_sendCachedResponse($request, $scopedIdps);
        if ($cacheResponseSent) {
            return;
        }

        // If the scoped proxycount = 0, respond with a ProxyCountExceeded error
        if (isset($request['samlp:Scoping'][EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ProxyCount']) && $request['samlp:Scoping'][EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ProxyCount'] == 0) {
            $this->_server->getSessionLog()->debug("SSO: Proxy count exceeded!");
            $response = $this->_server->createErrorResponse($request, 'ProxyCountExceeded');
            $this->_server->sendResponseToRequestIssuer($request, $response);
            return;
        }

        // Get all registered Single Sign On Services
        $candidateIDPs = $this->_server->getIdpEntityIds();
        $posOfOwnIdp = array_search($this->_server->getCurrentEntityUrl('idPMetadataService'), $candidateIDPs);
        if ($posOfOwnIdp !== false) {
            unset($candidateIDPs[$posOfOwnIdp]);
        }

        $this->_server->getSessionLog()->debug(
            "SSO: Candidate idps found in metadata: " . print_r($candidateIDPs, 1)
        );

        // If we have scoping, filter out every non-scoped IdP
        if (count($scopedIdps) > 0) {
            $candidateIDPs = array_intersect($scopedIdps, $candidateIDPs);
        }

        $this->_server->getSessionLog()->debug(
            "SSO: Candidate idps found in metadata after scoping: " . print_r($candidateIDPs, 1)
        );

        // No IdPs found! Send an error response back.
        if (count($candidateIDPs) === 0) {
            $this->_server->getSessionLog()->debug("SSO: No Supported Idps!");
            if ($this->_server->getConfig('NoSupportedIDPError')!=='user') {
                $response = $this->_server->createErrorResponse($request, 'NoSupportedIDP');
                $this->_server->sendResponseToRequestIssuer($request, $response);
                return;
            }
            else {
                $output = $this->_server->renderTemplate(
                    'noidps',
                    array(
                    ));
                $this->_server->sendOutput($output);
                return;
            }
        }
        // Exactly 1 candidate found, send authentication request to the first one
        else if (count($candidateIDPs) === 1) {
            $idp = array_shift($candidateIDPs);
            $this->_server->getSessionLog()->debug("SSO: Only 1 candidate IdP: $idp");
            $this->_server->sendAuthenticationRequest($request, $idp);
            return;
        }
        // Multiple IdPs found...
        else {
            // > 1 IdPs found, but isPassive attribute given, unable to show WAYF
            if (isset($request[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'IsPassive']) && $request[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'IsPassive'] === 'true') {
                $this->_server->getSessionLog()->debug("SSO: IsPassive with multiple IdPs!");
                $response = $this->_server->createErrorResponse($request, 'NoPassive');
                $this->_server->sendResponseToRequestIssuer($request, $response);
                return;
            }
            else {
                // Store the request in the session
                $id = $request[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'];
                $_SESSION[$id]['SAMLRequest'] = $request;

                // Show WAYF
                $this->_server->getSessionLog()->debug("SSO: Showing WAYF");
                $this->_showWayf($request, $candidateIDPs);
                return;
            }
        }
    }

    protected function _sendCachedResponse($request, $scopedIdps)
    {
        if (isset($request[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ForceAuthn']) && $request[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ForceAuthn']) {
            return false;
        }

        if (!isset($_SESSION['CachedResponses'])) {
            return false;
        }

        $cachedResponses = $_SESSION['CachedResponses'];

        $requestIssuerEntityId  = $request['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX];

        // First, if there is scoping, we reject responses from idps not in the list
        if (count($scopedIdps) > 0) {
            foreach ($cachedResponses as $key => $cachedResponse) {
                if (!in_array($cachedResponse['idp'], $scopedIdps)) {
                    unset($cachedResponses[$key]);
                }
            }
        }
        if (empty($cachedResponses)) {
            return false;
        }

        $cachedResponse = $this->_pickCachedResponse($cachedResponses, $request, $requestIssuerEntityId);
        if (!$cachedResponse) {
            return false;
        }

        if ($cachedResponse['type'] === self::RESPONSE_CACHE_TYPE_OUT) {
            $this->_server->getSessionLog()->debug("SSO: Cached response found for SP");
            $response = $this->_server->createEnhancedResponse($request, $cachedResponse['response']);
            $this->_server->sendResponseToRequestIssuer($request, $response);
        }
        else {
            $this->_server->getSessionLog()->debug("SSO: Cached response found from Idp");
            // Note that we would like to repurpose the response,
            // but that's tricky as it is probably no longer valid (lifetime is usually something like 5 minutes)
            // so instead we scope the request to that Idp and trust the Idp to do the remembering.
            $this->_server->sendAuthenticationRequest($request, $cachedResponse['idp']);
        }
        return true;
    }

    protected function _pickCachedResponse(array $cachedResponses, array $request, $requestIssuerEntityId)
    {
        // Then we look for OUT responses for this sp
        $idpEntityIds = $this->_server->getIdpEntityIds();
        foreach ($cachedResponses as $cachedResponse) {
            if ($cachedResponse['type'] !== self::RESPONSE_CACHE_TYPE_OUT) {
                continue;
            }

            // Check if it is for the requester
            if ($cachedResponse['sp'] !== $requestIssuerEntityId) {
                continue;
            }

            // Check if it is for a valid idp
            if (!in_array($cachedResponse['idp'], $idpEntityIds)) {
                continue;
            }

            if (isset($cachedResponse['vo'])) {
                $this->_server->setVirtualOrganisationContext($cachedResponse['vo']);
            }

            return $cachedResponse;
        }

        // Then we look for IN responses for this sp
        foreach ($cachedResponses as $cachedResponse) {
            if ($cachedResponse['type'] !== self::RESPONSE_CACHE_TYPE_IN) {
                continue;
            }

            // Check if it is for a valid idp
            if (!in_array($cachedResponse['idp'], $idpEntityIds)) {
                continue;
            }

            if (isset($cachedResponse['vo'])) {
                $this->_server->setVirtualOrganisationContext($cachedResponse['vo']);
            }

            return $cachedResponse;
        }

        return false;
    }

    protected function _getScopedIdPs($request = null)
    {
        $scopedIdPs = array();
        // Add scoped IdPs (allowed IDPs for reply) from request to allowed IdPs for responding
        if (isset($request['samlp:Scoping']['samlp:IDPList']['samlp:IDPEntry'])) {
            foreach ($request['samlp:Scoping']['samlp:IDPList']['samlp:IDPEntry'] as $IDPEntry) {
                $scopedIdPs[] = $IDPEntry[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ProviderID'];
            }
            $this->_server->getSessionLog()->debug("SSO: Request contains scoped idps: " . print_r($scopedIdPs, 1));
        }

        $presetIdPs = $this->_server->getCurrentEntitySetting('IDPList');
        $presetIdP  = $this->_server->getCurrentEntitySetting('Idp');

        // If we have ONE specific IdP pre-configured then we scope to ONLY that Idp
        if ($presetIdP) {
            $scopedIdPs = array($presetIdP);
            $this->_server->getSessionLog()->debug("SSO: Scoped idp found in metadata: " . $scopedIdPs[0]);
        }
        // If we configured an IDPList it overrides the one in the request
        else if ($presetIdPs) {
            $scopedIdPs = $presetIdPs;
            $this->_server->getSessionLog()->debug("SSO: Scoped idps found in metadata: " . print_r($scopedIdPs, 1));
        }
        return $scopedIdPs;
    }

    /**
     * Handle the forwarding of the user to the proper IdP0 after the WAYF screen.
     *
     * @return void
     */
    public function continueToIdP()
    {
        $selectedIdp = urldecode($_REQUEST['idp']);
        if (!$selectedIdp) {
            throw new EngineBlock_Corto_Module_Services_Exception('No IdP selected after WAYF');
        }

        // Retrieve the request from the session.
        $id      = $_POST['ID'];
        if (!isset($_SESSION[$id]['SAMLRequest'])) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException('Session lost after WAYF');
        }
        $request = $_SESSION[$id]['SAMLRequest'];

        $this->_server->sendAuthenticationRequest($request, $selectedIdp);
    }

    /**
     * Receive the assertion from the IdP and send it on to the SP.
     *
     * @throws EngineBlock_Corto_Module_Services_Exception
     * @return void
     */
    public function assertionConsumerService()
    {
        $receivedResponse = $this->_server->getBindingsModule()->receiveResponse();

        // Get the ID of the Corto Request message
        if (!$receivedResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo']) {
            $message = "Unsollicited assertion (no InResponseTo in message) not supported!";
            throw new EngineBlock_Corto_Module_Services_Exception($message);
        }

        $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo']);

        // Cache the response
        if ($this->_server->getCurrentEntitySetting('keepsession', false)) {
            $this->_cacheResponse($receivedRequest, $receivedResponse, self::RESPONSE_CACHE_TYPE_IN);
        }

        $this->_server->filterInputAssertionAttributes($receivedResponse, $receivedRequest);

        $processingEntities = $this->_getReceivedResponseProcessingEntities($receivedRequest, $receivedResponse);
        if (!empty($processingEntities)) {
            $firstProcessingEntity = array_shift($processingEntities);
            $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['RemainingEntities']   = $processingEntities;
            $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalDestination'] = $receivedResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination'];
            $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalIssuer']      = $receivedResponse['saml:Assertion']['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX];
            $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalBinding']     = $receivedResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'];

            $this->_server->setProcessingMode();
            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);

            // Change the destiny of the received response
            $newResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo']          = $receivedResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo'];
            $newResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination']           = $firstProcessingEntity['Location'];
            $newResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding']  = $firstProcessingEntity['Binding'];
            $newResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Return']           = $this->_server->getCurrentEntityUrl('processedAssertionConsumerService');
            $newResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['paramname']        = 'SAMLResponse';

            $responseAssertionAttributes = &$newResponse['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute'];
            $attributes = EngineBlock_Corto_XmlToArray::attributes2array($responseAssertionAttributes);
            $attributes['ServiceProvider'] = array($receivedRequest['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX]);
            $responseAssertionAttributes = EngineBlock_Corto_XmlToArray::array2attributes($attributes);

            $this->_server->getBindingsModule()->send($newResponse, $firstProcessingEntity);
        }
        else {
            // Cache the response
            if ($this->_server->getCurrentEntitySetting('keepsession', false)) {
                $this->_cacheResponse($receivedRequest, $receivedResponse, self::RESPONSE_CACHE_TYPE_OUT);
            }

            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $newResponse);
        }
    }

    protected function _cacheResponse(array $receivedRequest, array $receivedResponse, $type)
    {
        $requestIssuerEntityId  = $receivedRequest['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX];
        $responseIssuerEntityId = $receivedResponse['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX];
        if (!isset($_SESSION['CachedResponses'])) {
            $_SESSION['CachedResponses'] = array();
        }
        $_SESSION['CachedResponses'][] = array(
            'sp'            => $requestIssuerEntityId,
            'idp'           => $responseIssuerEntityId,
            'type'          => $type,
            'response'      => $receivedResponse,
            'vo'            => $this->_server->getVirtualOrganisationContext()
        );
        return $_SESSION['CachedResponses'][count($_SESSION['CachedResponses']) - 1];
    }

    protected function _getReceivedResponseProcessingEntities(array $receivedRequest, array $receivedResponse)
    {
        $currentEntityProcessing = $this->_server->getCurrentEntitySetting('Processing', array());

        $remoteEntity = $this->_server->getRemoteEntity($receivedRequest['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX]);

        $processing = $currentEntityProcessing;
        if (isset($remoteEntity['Processing'])) {
            $processing += $remoteEntity['Processing'];
        }

        return $processing;
    }

    /**
     * Ask the user for consent over all of the attributes being sent to the SP.
     *
     * Note this is part 1/2 of the Corto Consent Internal Response Processing service.
     *
     * @return void
     */
    public function provideConsentService()
    {
        $response = $this->_server->getBindingsModule()->receiveResponse();
        $_SESSION['consent'][$response['_ID']]['response'] = $response;

        $attributes = EngineBlock_Corto_XmlToArray::attributes2array(
            $response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute']
        );

        $serviceProviderEntityId = $attributes['ServiceProvider'][0];
        unset($attributes['ServiceProvider']);
        $spEntityMetadata = $this->_server->getRemoteEntity($serviceProviderEntityId);

        $identityProviderEntityId = $response['__']['OriginalIssuer'];
        $idpEntityMetadata = $this->_server->getRemoteEntity($identityProviderEntityId);

        $commonName = $attributes['urn:mace:dir:attribute-def:cn'][0];

        // Apply ARP
        $arpFilter = new EngineBlock_Corto_Filter_Command_AttributeReleasePolicy();
        $arpFilter->setIdpMetadata($idpEntityMetadata);
        $arpFilter->setSpMetadata($spEntityMetadata);
        $arpFilter->setResponseAttributes($attributes);
        $arpFilter->execute();
        $attributes = $arpFilter->getResponseAttributes();

        $priorConsent = $this->_hasStoredConsent($serviceProviderEntityId, $response, $attributes);
        if ($priorConsent) {
            $response['_Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:prior';

            $response['_Destination'] = $response['__']['Return'];
            $response['__']['ProtocolBinding'] = 'INTERNAL';

            $this->_server->getBindingsModule()->send(
                $response,
                $spEntityMetadata
            );
            return;
        }

        if (isset($spEntityMetadata['NoConsentRequired']) && $spEntityMetadata['NoConsentRequired']) {
            $response['_Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:inapplicable';

            $response['_Destination'] = $response['__']['Return'];
            $response['__']['ProtocolBinding'] = 'INTERNAL';

            $this->_server->getBindingsModule()->send(
                $response,
                $spEntityMetadata
            );
            return;
        }

        $html = $this->_server->renderTemplate(
            'consent',
            array(
                'action'    => $this->_server->getCurrentEntityUrl('processConsentService'),
                'ID'        => $response['_ID'],
                'attributes'=> $attributes,
                'sp'        => $spEntityMetadata,
                'idp'       => $idpEntityMetadata,
                'commonName'=> $commonName,
            ));
        $this->_server->sendOutput($html);
    }

    /**
     * Process consent that is given and sent the user back to the proxy flow
     *
     * Note this is part 2/2 of the Corto Consent Response Processing service.
     *
     * @return void
     */
    public function processConsentService()
    {
        if (!isset($_SESSION['consent'])) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException('Session lost after consent');
        }
        if (!isset($_SESSION['consent'][$_POST['ID']]['response'])) {
            throw new EngineBlock_Corto_Module_Services_Exception("Stored response for ResponseID '{$_POST['ID']}' not found");
        }
        $response = $_SESSION['consent'][$_POST['ID']]['response'];

        $attributes = EngineBlock_Corto_XmlToArray::attributes2array(
            $response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute']
        );
        $serviceProviderEntityId = $attributes['ServiceProvider'][0];
        unset($attributes['ServiceProvider']);

        if (!isset($_POST['consent']) || $_POST['consent'] !== 'yes') {
            $this->_server->redirect('/authentication/feedback/no-consent', 'No consent given...');
            return;
        }

        $this->_storeConsent($serviceProviderEntityId, $response, $attributes);

        $response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:obtained';
        $response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination'] = $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Return'];
        $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'] = 'INTERNAL';

        $this->_server->getBindingsModule()->send(
            $response,
            $this->_server->getRemoteEntity($serviceProviderEntityId)
        );
    }

    /**
     *
     * @return void
     */
    public function processedAssertionConsumerService()
    {
        $response = $this->_server->getBindingsModule()->receiveResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo']);

        $remainingProcessingEntities = &$_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['RemainingEntities'];

        if (!empty($remainingProcessingEntities)) { // Moar processing!
            $nextProcessingEntity = array_shift($remainingProcessingEntities);

            $this->_server->setProcessingMode();

            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $response);

            // Change the destiny of the received response
            $newResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']                    = $response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'];
            $newResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination']           = $nextProcessingEntity['Location'];
            $newResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding']  = $nextProcessingEntity['Binding'];
            $newResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Return']           = $this->_server->getCurrentEntityUrl('processedAssertionConsumerService');
            $newResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['paramname']        = 'SAMLResponse';

            $this->_server->getBindingsModule()->send($newResponse, $nextProcessingEntity);
            return;
        }
        else { // Done processing! Send off to SP
            $response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination']          = $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalDestination'];
            $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'] = $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalBinding'];
            $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['OriginalIssuer']  = $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalIssuer'];

            $responseAssertionAttributes = &$response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute'];
            $attributes = EngineBlock_Corto_XmlToArray::attributes2array($responseAssertionAttributes);
            unset($attributes['ServiceProvider']);
            $responseAssertionAttributes = EngineBlock_Corto_XmlToArray::array2attributes($attributes);

            $this->_server->unsetProcessingMode();

            // Cache the response
            if ($this->_server->getCurrentEntitySetting('keepsession', false)) {
                $this->_cacheResponse($receivedRequest, $response, self::RESPONSE_CACHE_TYPE_OUT);
            }

            $sentResponse = $this->_server->createEnhancedResponse($receivedRequest, $response);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $sentResponse);
            return;
        }
    }

    /**
     * Describes Corto as an IdP to SPs
     *
     * @throws Exception
     * @return void
     */
    public function idPMetadataService()
    {
        // Fetch SP Entity Descriptor for the SP Entity ID that is fetched from the request
        $request = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
        $spEntityId = $request->getQueryParameter('sp-entity-id');
        if ($spEntityId) {
            $spEntity = $this->_server->getRemoteEntity($spEntityId);
        }

        $entityDescriptor = array(
            EngineBlock_Corto_XmlToArray::TAG_NAME_PFX => 'md:EntityDescriptor',
            EngineBlock_Corto_XmlToArray::COMMENT_PFX => self::META_TOU_COMMENT,
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:md' => 'urn:oasis:names:tc:SAML:2.0:metadata',
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:mdui' => 'urn:oasis:names:tc:SAML:metadata:ui',
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'validUntil' => $this->_server->timeStamp($this->_server->getCurrentEntitySetting(
                'idpMetadataValidUntilSeconds', 86400)),
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'entityID' => $this->_server->getCurrentEntityUrl('idPMetadataService'),
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID' => $this->_server->getNewId(),
            'ds:Signature' => EngineBlock_Corto_XmlToArray::PLACEHOLDER_VALUE,
            'md:IDPSSODescriptor' => array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
            ),
        );

        $voContext = $this->_server->getVirtualOrganisationContext();
        $this->_server->setVirtualOrganisationContext(null);
        $canonicalIdpEntityId = $this->_server->getCurrentEntityUrl('idPMetadataService');
        $this->_server->setVirtualOrganisationContext($voContext);
        $entityDetails = $this->_server->getRemoteEntity($canonicalIdpEntityId);

        $this->_addContactPersonsToEntityDescriptor($entityDescriptor, $entityDetails);

        $this->_addDisplayNamesToEntityDescriptor($entityDescriptor['md:IDPSSODescriptor'], $entityDetails);

        $this->_addDescriptionToEntityDescriptor($entityDescriptor['md:IDPSSODescriptor'], $entityDetails);

        // Check if an alternative Public & Private key have been set for a SP
        // If yes, use these in the metadata of Engineblock
        if (isset($spEntity)
            && $spEntity['AlternatePrivateKey']
            && $spEntity['AlternatePublicKey']
        ) {
            $publicCertificate = $spEntity['AlternatePublicKey'];
        } else {
            $certificates = $this->_server->getCurrentEntitySetting('certificates', array());
            $publicCertificate = $certificates['public'];
        }

        if (isset($publicCertificate)) {
            $entityDescriptor['md:IDPSSODescriptor']['md:KeyDescriptor'] = array(
                array(
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'signing',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                EngineBlock_Corto_XmlToArray::VALUE_PFX => $this->_server->getCertDataFromPem($publicCertificate),
                            ),
                        ),
                    ),
                ),
                array(
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'encryption',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                EngineBlock_Corto_XmlToArray::VALUE_PFX => $this->_server->getCertDataFromPem($publicCertificate),
                            ),
                        ),
                    ),
                    'md:EncryptionMethod' => array(
                        array(
                            '_Algorithm' => 'http://www.w3.org/2001/04/xmlenc#rsa-1_5',
                        ),
                    ),
                ),
            );
        }
        $entityDescriptor['md:IDPSSODescriptor']['md:NameIDFormat'] = array(
            array(EngineBlock_Corto_XmlToArray::VALUE_PFX => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent'),
            array(EngineBlock_Corto_XmlToArray::VALUE_PFX => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'),
            array(EngineBlock_Corto_XmlToArray::VALUE_PFX => 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified'),
        );
        $entityDescriptor['md:IDPSSODescriptor']['md:SingleSignOnService'] = array(
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Binding'  => self::DEFAULT_REQUEST_BINDING,
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Location' => $this->_server->getCurrentEntityUrl('singleSignOnService'),
        );

        $entityDescriptor = $this->_server->sign(
            $entityDescriptor,
            (isset($spEntity['AlternatePublicKey'])  ? $spEntity['AlternatePublicKey']  : null),
            (isset($spEntity['AlternatePrivateKey']) ? $spEntity['AlternatePrivateKey'] : null)
        );
        $xml = EngineBlock_Corto_XmlToArray::array2xml($entityDescriptor);

        $this->_validateXml($xml);

        $this->_server->sendHeader('Content-Type', 'application/xml');
        //$this->_server->sendHeader('Content-Type', 'application/samlmetadata+xml');
        $this->_server->sendOutput($xml);
    }

    /**
     * Describes Corto as an SP to IdPs
     *
     * @throws Exception
     * @return void
     */
    public function sPMetadataService()
    {
        $spEntityId = $this->_server->getCurrentEntityUrl('sPMetadataService');

        $entityDescriptor = array(
            EngineBlock_Corto_XmlToArray::TAG_NAME_PFX => 'md:EntityDescriptor',
            EngineBlock_Corto_XmlToArray::COMMENT_PFX => self::META_TOU_COMMENT,
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:md' => 'urn:oasis:names:tc:SAML:2.0:metadata',
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:mdui' => 'urn:oasis:names:tc:SAML:metadata:ui',
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'validUntil' => $this->_server->timeStamp(
                $this->_server->getCurrentEntitySetting('idpMetadataValidUntilSeconds', 86400)
            ),
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'entityID' => $spEntityId,
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID' => $this->_server->getNewId(),
            'ds:Signature' => EngineBlock_Corto_XmlToArray::PLACEHOLDER_VALUE,
            'md:SPSSODescriptor' => array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
            ),
        );

        $voContext = $this->_server->getVirtualOrganisationContext();
        $this->_server->setVirtualOrganisationContext(null);
        $canonicalSpEntityId = $this->_server->getCurrentEntityUrl('sPMetadataService');
        $this->_server->setVirtualOrganisationContext($voContext);

        $entityDetails = $this->_server->getRemoteEntity($canonicalSpEntityId);

        $this->_addContactPersonsToEntityDescriptor($entityDescriptor, $entityDetails);

        $this->_addDisplayNamesToEntityDescriptor($entityDescriptor['md:SPSSODescriptor'], $entityDetails);

        $certificates = $this->_server->getCurrentEntitySetting('certificates', array());
        if (isset($certificates['public'])) {
            $entityDescriptor['md:SPSSODescriptor']['md:KeyDescriptor'] = array(
                array(
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'signing',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                EngineBlock_Corto_XmlToArray::VALUE_PFX => $this->_server->getCertDataFromPem($certificates['public']),
                            ),
                        ),
                    ),
                ),
                array(
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'encryption',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                EngineBlock_Corto_XmlToArray::VALUE_PFX => $this->_server->getCertDataFromPem($certificates['public']),
                            ),
                        ),
                    ),
                    'md:EncryptionMethod' => array(
                        array(
                            '_Algorithm' => 'http://www.w3.org/2001/04/xmlenc#rsa-1_5',
                        ),
                    ),
                ),
            );
        }

        $entityDescriptor['md:SPSSODescriptor']['md:NameIDFormat'] = array(
            EngineBlock_Corto_XmlToArray::VALUE_PFX => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'
        );
        $entityDescriptor['md:SPSSODescriptor']['md:AssertionConsumerService'] = array(
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Binding'  => self::DEFAULT_RESPONSE_BINDING,
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Location' => $this->_server->getCurrentEntityUrl('assertionConsumerService'),
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'index' => '1',
        );

        $entityDescriptor['md:SPSSODescriptor']['md:AttributeConsumingService'] = array(
            // @todo get correct value for index
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'index' => 1,
        );

        $this->_addServiceNamesToAttributeConsumingService(
            $entityDescriptor['md:SPSSODescriptor']['md:AttributeConsumingService'], $entityDetails);

        $this->_addServiceDescriptionsToAttributeConsumingService(
            $entityDescriptor['md:SPSSODescriptor']['md:AttributeConsumingService'], $entityDetails);

        $entityDescriptor['md:SPSSODescriptor']['md:AttributeConsumingService']['md:RequestedAttribute'] = array(
            // Mail (example: john@surfnet.nl)
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:mace:dir:attribute-def:mail'
            ),
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:oid:0.9.2342.19200300.100.1.3',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired' => 'true'
            ),

            // DisplayName (example: John Doe)
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:mace:dir:attribute-def:displayName'
            ),
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:oid:2.16.840.1.113730.3.1.241',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired' => 'true'
            ),

            // Surname (example: Doe)
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:mace:dir:attribute-def:sn'
            ),
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:oid:2.5.4.4',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired' => 'true'
            ),

            // Given name (example: John)
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:mace:dir:attribute-def:givenName'
            ),
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:oid:2.5.4.42',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired' => 'true'
            ),

            // SchachomeOrganization
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:mace:terena.org:attribute-def:schacHomeOrganization'
            ),
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:oid:1.3.6.1.4.1.25178.1.2.9'
            ,EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri'
            , EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired' => 'true'
            ),

            // UID (example: john.doe)
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:mace:dir:attribute-def:uid'
            ),
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:oid:0.9.2342.19200300.100.1.1',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired' => 'true'
            )
        );

        $entityDescriptor = $this->_server->sign($entityDescriptor);

        $xml = EngineBlock_Corto_XmlToArray::array2xml($entityDescriptor);

        $this->_validateXml($xml);

        $this->_server->sendHeader('Content-Type', 'application/xml');
        //$this->_server->sendHeader('Content-Type', 'application/samlmetadata+xml');
        $this->_server->sendOutput($xml);
    }

    public function idpCertificateService()
    {
        $filename = $_SERVER['SERVER_NAME'] . '.pem';
        $certificates = $this->_server->getCurrentEntitySetting('certificates', array());
        $publicCertContents = $certificates['public'];

        header('Content-Type: application/x-pem-file');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($publicCertContents));
        header('Expires: 0');

        // check for IE only headers
        if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        } else {
            header('Pragma: no-cache');
        }

        echo $publicCertContents;
    }

    public function spCertificateService()
    {
        $this->idpCertificateService();
    }

    /**
     * Adds contact persons (if present) to entity Descriptor
     *
     * @param array $entityDescriptor
     * @param array $entityDetails
     * @return void
     */
    protected function _addContactPersonsToEntityDescriptor(array &$entityDescriptor, array $entityDetails)
    {
        if(!array_key_exists('ContactPersons', $entityDetails)) {
            return;
        }

        foreach($entityDetails['ContactPersons'] as $contactPerson) {
            if(empty($contactPerson['EmailAddress'])) {
                continue;
            }

            $mdContactPerson = array();
            $mdContactPerson[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'contactType'] = $contactPerson['ContactType'];
            $mdContactPerson['md:EmailAddress'][][EngineBlock_Corto_XmlToArray::VALUE_PFX] = $contactPerson['EmailAddress'];

            $entityDescriptor['md:ContactPerson'][] = $mdContactPerson;
        }
    }

    /**
     * Adds DisplayName (if present) to entity Descriptor
     *
     * @param array $entitySSODescriptor
     * @param array $entityDetails
     * @return void
     */
    protected function _addDisplayNamesToEntityDescriptor(array &$entitySSODescriptor, array $entityDetails)
    {
        if (!isset($entityDetails['DisplayName'])) {
            return;
        }
        foreach($entityDetails['DisplayName'] as $displayLanguageCode => $displayName) {
            if(empty($displayName)) {
                continue;
            }

            if (!isset($entitySSODescriptor['md:Extensions'])) {
                $entitySSODescriptor['md:Extensions'] = array();
            }
            if (!isset($entitySSODescriptor['md:Extensions']['mdui:UIInfo'])) {
                $entitySSODescriptor['md:Extensions']['mdui:UIInfo'] = array(0=>array());
            }
            $entitySSODescriptor['md:Extensions']['mdui:UIInfo'][0]['mdui:DisplayName'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $displayLanguageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $displayName
            );
        }
    }

    /**
     * Adds DisplayName (if present) to entity Descriptor
     *
     * @param array $entitySSODescriptor
     * @param array $entityDetails
     * @return void
     */
    protected function _addDescriptionToEntityDescriptor(array &$entitySSODescriptor, array $entityDetails)
    {
        if (!isset($entityDetails['Description'])) {
            return;
        }
        foreach($entityDetails['Description'] as $displayLanguageCode => $description) {
            if(empty($description)) {
                continue;
            }

            if (!isset($entitySSODescriptor['md:Extensions'])) {
                $entitySSODescriptor['md:Extensions'] = array();
            }
            if (!isset($entitySSODescriptor['md:Extensions']['mdui:UIInfo'])) {
                $entitySSODescriptor['md:Extensions']['mdui:UIInfo'] = array(0=>array());
            }
            $entitySSODescriptor['md:Extensions']['mdui:UIInfo'][0]['mdui:Description'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $displayLanguageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $description
            );
        }
    }

    /**
     * Adds ServiceName (if present) to AttributeConsumingService
     *
     * @param array $attributeConsumingService
     * @param array $entityDetails
     * @return void
     */
    protected function _addServiceNamesToAttributeConsumingService(array &$attributeConsumingService, array $entityDetails)
    {
        foreach($entityDetails['Name'] as $descriptionLanguageCode => $descriptionName) {
            if(empty($descriptionName)) {
                continue;
            }

            $attributeConsumingService['md:ServiceName'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $descriptionLanguageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $descriptionName
            );
        }
    }

    /**
     * Adds ServiceDescription (if present) to AttributeConsumingService
     *
     * @param array $attributeConsumingService
     * @param array $entityDetails
     * @return void
     */
    protected function _addServiceDescriptionsToAttributeConsumingService(array &$attributeConsumingService, array $entityDetails)
    {
        foreach($entityDetails['Description'] as $descriptionLanguageCode => $descriptionName) {
            if(empty($descriptionName)) {
                continue;
            }

            $attributeConsumingService['md:ServiceDescription'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $descriptionLanguageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $descriptionName
            );
        }
    }

    /**
     * Validates xml against oasis SAML 2 spec
     *
     * @param string $xml
     * @return void
     * @throws Exception in case validating itself fails or if xml does not validate
     */
    protected function _validateXml($xml)
    {
        $inDebugModus = $this->_server->getConfig('debug', false);
        if($inDebugModus) {
            if(!ini_get('allow_url_fopen')) {
                throw new Exception('Failed validating XML, url_fopen is not allowed');
            }

            // Load schema
            $schemaUrl = 'http://docs.oasis-open.org/security/saml/v2.0/saml-schema-metadata-2.0.xsd';
            $schemaXml = @file_get_contents($schemaUrl);
            if($schemaXml === false) {
                throw new Exception('Failed validating XML, schema url could not be opened: "' . $schemaUrl . '"');
            }

            $schemaXml = $this->_absolutizeSchemaLocations($schemaXml, $schemaUrl);

            $dom = new DOMDocument();
            $dom->loadXML($xml);
            if (!@$dom->schemaValidateSource($schemaXml)) {
                $errorInfo = error_get_last();
                $errorMessage = $errorInfo['message'];
                // @todo improve parsing message by creating custom exceptions for which know that structure of messages
                $parsedErrorMessage = preg_replace('/\{[^}]*\}/', '', $errorMessage);
                echo '<pre>'.htmlentities(EngineBlock_Corto_XmlToArray::formatXml($xml)).'</pre>';
                throw new Exception('Metadata XML doesnt validate against XSD at Oasis-open.org: ' . $parsedErrorMessage);
            }
        }
    }

    /**
     * Converts relative schema locations to absolute since php dom validator
     * does not seem to understand relative links
     *
     * @param   string  $schemaXml
     * @param   string  $schemaUrl
     * @return  string  $absoluteSchemaXml
     */
    protected function _absolutizeSchemaLocations($schemaXml, $schemaUrl)
    {
        $allSchemaLocationsRegex = '/schemaLocation="(.*)"/';
        preg_match_all($allSchemaLocationsRegex, $schemaXml, $matches);

        $schemaDir = dirname($schemaUrl) . '/';
        $absoluteSchemaXml =$schemaXml;
        foreach($matches[1] as $schemaLocation) {
            $isRelativeLocation = substr($schemaLocation, 0, 4) != 'http';
            if($isRelativeLocation) {
                $absoluteSchemaXml = str_replace('"' . $schemaLocation . '"', '"' . $schemaDir . $schemaLocation . '"', $schemaXml);
            }
        }

        return $absoluteSchemaXml;
    }

    protected function _showWayf($request, $candidateIdPs)
    {
        // Post to the 'continueToIdp' service
        $action = $this->_server->getCurrentEntityUrl('continueToIdP');

        $requestIssuer = $request['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX];

        $remoteEntity = $this->_server->getRemoteEntity($requestIssuer);

        $idpList = $this->_transformIdpsForWAYF($candidateIdPs);

        $output = $this->_server->renderTemplate(
            'discover',
            array(
                'preselectedIdp'    => $this->_server->getCookie('selectedIdp'),
                'action'            => $action,
                'ID'                => $request[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'],
                'idpList'           => $idpList,
                'metaDataSP'        => $remoteEntity,
            ));
        $this->_server->sendOutput($output);
    }

    protected function _transformIdpsForWayf($idps)
    {
        $wayfIdps = array();
        foreach ($idps as $idp) {
            $remoteEntities = $this->_server->getRemoteEntities();
            $metadata = ($remoteEntities[$idp]);
            $additionalInfo = new EngineBlock_Log_Message_AdditionalInfo(
                null, $idp, null, null
            );

            if (isset($metadata['DisplayName']['nl'])) {
                $nameNl = $metadata['DisplayName']['nl'];
            }
            else if (isset($metadata['Name']['nl'])) {
                $nameNl = $metadata['Name']['nl'];
            }
            else {
                $nameNl = 'Geen naam gevonden';
                EngineBlock_ApplicationSingleton::getLog()->warn('No NL displayName and name found for idp: ' . $idp, $additionalInfo);
            }

            if (isset($metadata['DisplayName']['en'])) {
                $nameEn = $metadata['DisplayName']['en'];
            }
            else if (isset($metadata['Name']['en'])) {
                $nameEn = $metadata['Name']['en'];
            }
            else {
                $nameEn = 'No name found';
                EngineBlock_ApplicationSingleton::getLog()->warn('No EN displayName and name found for idp: ' . $idp, $additionalInfo);
            }

            $wayfIdp = array(
                'Name_nl' => $nameNl,
                'Name_en' => $nameEn,
                'Logo' => isset($metadata['Logo']['URL']) ? $metadata['Logo']['URL']
                    : EngineBlock_View::staticUrl() . '/media/idp-logo-not-found.png',
                'Keywords' => isset($metadata['Keywords']['en']) ? explode(' ', $metadata ['Keywords']['en'])
                    : isset($metadata['Keywords']['nl']) ? explode(' ', $metadata['Keywords']['nl']) : 'Undefined',
                'Access' => '1',
                'ID' => md5($idp),
                'EntityId' => $idp,
            );
            $wayfIdps[] = $wayfIdp;
        }

        return $wayfIdps;
    }

    protected function _hasStoredConsent($serviceProviderEntityId, $response, $responseAttributes)
    {
        try {
            $dbh = $this->_getConsentDatabaseConnection();
            if (!$dbh) {
                return false;
            }

            $attributesHash = $this->_getAttributesHash($responseAttributes);

            $table = $this->_server->getConfig('ConsentDbTable', 'consent');
            $query = "SELECT * FROM {$table} WHERE hashed_user_id = ? AND service_id = ? AND attribute = ?";
            $parameters = array(
                sha1($this->_getConsentUid($response, $responseAttributes)),
                $serviceProviderEntityId,
                $attributesHash
            );

            $statement = $dbh->prepare($query);
            $statement->execute($parameters);
            $rows = $statement->fetchAll();

            if (count($rows) !== 1) {
                // No stored consent found
                return false;
            }

            // Update usage date
            $statement = $dbh->prepare("UPDATE LOW PRIORITY {$table} SET usage_date = NOW() WHERE attribute = ?");
            $statement->execute(array($attributesHash));

            return true;
        } catch (PDOException $e) {
            throw new EngineBlock_Corto_ProxyServer_Exception("Consent retrieval failed! Error: " . $e->getMessage());
        }
    }

    protected function _storeConsent($serviceProviderEntityId, $response, $attributes)
    {
        // Apply ARP
        $arpFilter = new EngineBlock_Corto_Filter_Command_AttributeReleasePolicy();
        $arpFilter->setSpMetadata($this->_server->getRemoteEntity($serviceProviderEntityId));
        $arpFilter->setResponseAttributes($attributes);
        $arpFilter->execute();
        $attributes = $arpFilter->getResponseAttributes();

        $dbh = $this->_getConsentDatabaseConnection();
        if (!$dbh) {
            return false;
        }

        $query = "INSERT INTO consent (usage_date, hashed_user_id, service_id, attribute)
                  VALUES (NOW(), ?, ?, ?)
                  ON DUPLICATE KEY UPDATE usage_date=VALUES(usage_date), attribute=VALUES(attribute)";
        $parameters = array(
            sha1($this->_getConsentUid($response, $attributes)),
            $serviceProviderEntityId,
            $this->_getAttributesHash($attributes)
        );

        $statement = $dbh->prepare($query);
        if (!$statement->execute($parameters)) {
            throw new EngineBlock_Corto_Module_Services_Exception("Error storing consent: " . var_export($statement->errorInfo(), true));
        }

        $this->_sendIntroductionMail($response, $attributes);

        return true;
    }

    protected function _getConsentUid($response, $attributes)
    {
        return $response['saml:Assertion']['saml:Subject']['saml:NameID']['__v'];
    }

    /**
     * @return bool|PDO
     */
    protected function _getConsentDatabaseConnection()
    {
        // We only use the write connection because consent is 3 queries of which only 1 light select query.
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);
    }

    protected function _getAttributesHash($attributes)
    {
        $hashBase = NULL;
        if ($this->_server->getConfig('ConsentStoreValues', true)) {
            ksort($attributes);
            $hashBase = serialize($attributes);
        } else {
            $names = array_keys($attributes);
            sort($names);
            $hashBase = implode('|', $names);
        }
        return sha1($hashBase);
    }

    /**
     * Edugain metadata service
     */
    public function edugainMetadataService()
    {
        $metadataParams = $this->_getEntitiesMetadataParams();
        $entitiesDescriptor = $metadataParams['entities_descriptor'];

        foreach ($this->_server->getRemoteEntities() as $entityId => $entity) {
            if (empty($entity['PublishInEdugain'])) continue;

            $entityDescriptor = $this->_getEntitiesMetadataRemoteEntityDescriptor(
                $entityId, $entity, $metadataParams['sp_entity']
            );

            if (isset($entity['AssertionConsumerServices'])) { // SP
                $entityDescriptorKey = 'md:SPSSODescriptor';
            } else if (isset($entity['SingleSignOnService'])) { // IDP
                $entityDescriptorKey = 'md:IDPSSODescriptor';
            } else {
                // can not determine type (IDP or SP)
                continue;
            }

            $entitiesDescriptor['md:EntityDescriptor'][] = array(
                '_validUntil' => $this->_server->timeStamp(
                    $this->_server->getCurrentEntitySetting('edugainMetadataValidUntilSeconds', 86400)
                ),
                '_entityID' => $entityId,
                $entityDescriptorKey => $entityDescriptor,
            );
        }

        $this->_signAndSendEntitiesMetadata($metadataParams['sp_entity'], $entitiesDescriptor);
    }

    /**
     * IDP metadata service
     */
    public function idPsMetadataService()
    {
        $metadataParams = $this->_getEntitiesMetadataParams();
        $entitiesDescriptor = $metadataParams['entities_descriptor'];

        foreach ($this->_server->getRemoteEntities() as $entityId => $entity) {
            if (!isset($entity['SingleSignOnService'])) continue;

            $entityDescriptor = $this->_getEntitiesMetadataRemoteEntityDescriptor(
                $entityId, $entity, $metadataParams['sp_entity']
            );

            $entitiesDescriptor['md:EntityDescriptor'][] = array(
                '_validUntil' => $this->_server->timeStamp(
                    $this->_server->getCurrentEntitySetting('idpMetadataValidUntilSeconds', 86400)
                ),
                '_entityID' => $entityId,
                'md:IDPSSODescriptor' => $entityDescriptor,
            );
        }

        $this->_signAndSendEntitiesMetadata($metadataParams['sp_entity'], $entitiesDescriptor);
    }

    /**
     * Get base entities descriptor and optional SP entity for
     * edugain and idps metadata service
     *
     * @return array keys sp_entity and entities_descriptor
     * @throws Exception
     */
    protected function _getEntitiesMetadataParams()
    {
        $entitiesDescriptor = array(
            EngineBlock_Corto_XmlToArray::TAG_NAME_PFX => 'md:EntitiesDescriptor',
            '_xmlns:md' => 'urn:oasis:names:tc:SAML:2.0:metadata',
            '_xmlns:mdui' => 'urn:oasis:names:tc:SAML:metadata:ui',
            '_ID' => $this->_server->getNewId(),
            'ds:Signature' => '__placeholder__',
            'md:EntityDescriptor' => array()
        );

        // Fetch SP Entity Descriptor for the SP Entity ID that is fetched from the request
        $request = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
        $spEntityId = $request->getQueryParameter('sp-entity-id');
        if ($spEntityId) {
            $spEntityDescriptor = $this->_getSpEntityDescriptor($spEntityId);
            $spEntity = $this->_server->getRemoteEntity($spEntityId);
            if ($spEntityDescriptor) {
                $entitiesDescriptor['md:EntityDescriptor'][] = $spEntityDescriptor;
            }
        }

        return array(
            'sp_entity' => $spEntity,
            'entities_descriptor' => $entitiesDescriptor
        );
    }

    /**
     *
     * @param string $entityId
     * @param array $entity
     * @param array $spEntity
     * @return array the entity descriptor
     */
    protected function _getEntitiesMetadataRemoteEntityDescriptor($entityId, $entity, $spEntity)
    {
        $descriptor = array();
        $descriptor['_protocolSupportEnumeration'] = "urn:oasis:names:tc:SAML:2.0:protocol";

        if (isset($entity['DisplayName'])) {
            if (!isset($descriptor['md:Extensions'])) {
                $descriptor['md:Extensions'] = array();
            }
            if (!isset($descriptor['md:Extensions']['mdui:UIInfo'])) {
                $descriptor['md:Extensions']['mdui:UIInfo'] = array(0=>array());
            }
            foreach ($entity['DisplayName'] as $lang => $name) {
                if (trim($name)==='') {
                    continue;
                }
                if (!isset($descriptor['md:Extensions']['mdui:UIInfo'][0]['mdui:DisplayName'])) {
                    $descriptor['md:Extensions']['mdui:UIInfo'][0]['mdui:DisplayName'] = array();
                }
                $descriptor['md:Extensions']['mdui:UIInfo'][0]['mdui:DisplayName'][] = array(
                    '_xml:lang' => $lang,
                    '__v' => $name,
                );
            }
        }

        if (isset($entity['Description'])) {
            if (!isset($descriptor['md:Extensions'])) {
                $descriptor['md:Extensions'] = array();
            }
            if (!isset($descriptor['md:Extensions']['mdui:UIInfo'])) {
                $descriptor['md:Extensions']['mdui:UIInfo'] = array(0=>array());
            }
            foreach ($entity['Description'] as $lang => $name) {
                if (trim($name)==='') {
                    continue;
                }
                if (!isset($descriptor['md:Extensions']['mdui:UIInfo'][0]['mdui:Description'])) {
                    $descriptor['md:Extensions']['mdui:UIInfo'][0]['mdui:Description'] = array();
                }
                $descriptor['md:Extensions']['mdui:UIInfo'][0]['mdui:Description'][] = array(
                    '_xml:lang' => $lang,
                    '__v' => $name,
                );
            }
        }

        $hasLogoHeight = (isset($entity['Logo']['Height']) && $entity['Logo']['Height']);
        $hasLogoWidth  = (isset($entity['Logo']['Width'])  && $entity['Logo']['Width']);
        if (isset($entity['Logo']) && $hasLogoHeight && $hasLogoWidth) {
            if (!isset($descriptor['md:Extensions'])) {
                $descriptor['md:Extensions'] = array();
            }
            if (!isset($descriptor['md:Extensions']['mdui:UIInfo'])) {
                $descriptor['md:Extensions']['mdui:UIInfo'] = array(0=>array());
            }
            $descriptor['md:Extensions']['mdui:UIInfo'][0]['mdui:Logo'] = array(
                array(
                    '_height' => $entity['Logo']['Height'],
                    '_width'  => $entity['Logo']['Width'],
                    '__v'     => $entity['Logo']['URL'],
                ),
            );
        }

        if (isset($entity['GeoLocation']) && !empty($entity['GeoLocation'])) {
            if (!isset($descriptor['md:Extensions'])) {
                $descriptor['md:Extensions'] = array();
            }
            if (!isset($descriptor['md:Extensions']['mdui:DiscoHints'])) {
                $descriptor['md:Extensions']['mdui:DiscoHints'] = array(0=>array());
            }
            $descriptor['md:Extensions']['mdui:DiscoHints'][0]['mdui:GeolocationHint'] = array(
                array(
                    '__v' => $entity['GeoLocation'],
                ),
            );
        }

        if (isset($entity['Keywords'])) {
            if (!isset($descriptor['md:Extensions'])) {
                $descriptor['md:Extensions'] = array();
            }
            if (!isset($descriptor['md:Extensions']['mdui:UIInfo'])) {
                $descriptor['md:Extensions']['mdui:UIInfo'] = array(0=>array());
            }
            $uiInfo = &$descriptor['md:Extensions']['mdui:UIInfo'][0];
            foreach ($entity['Keywords'] as $lang => $name) {
                if (trim($name)==='') {
                    continue;
                }
                if (!isset($uiInfo['mdui:Keywords'])) {
                    $uiInfo['mdui:Keywords'] = array();
                }
                $uiInfo['mdui:Keywords'][] = array(
                    array(
                        '_xml:lang' => $lang,
                        '__v' => $name,
                    ),
                );
            }
        }

        // Check if an alternative Public & Private key have been set for a SP
        // If yes, use these in the metadata of Engineblock
        if (isset($spEntity)
            && $spEntity['AlternatePrivateKey']
            && $spEntity['AlternatePublicKey']
        ) {
            $publicCertificate = $spEntity['AlternatePublicKey'];
        } else {
            $certificates = $this->_server->getCurrentEntitySetting('certificates', array());
            $publicCertificate = $certificates['public'];
        }

        if (isset($publicCertificate)) {
            $descriptor['md:KeyDescriptor'] = array(
                array(
                    '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    '_use' => 'signing',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                '__v' => $this->_server->getCertDataFromPem($publicCertificate),
                            ),
                        ),
                    ),
                ),
                array(
                    '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    '_use' => 'encryption',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                '__v' => $this->_server->getCertDataFromPem($publicCertificate),
                            ),
                        ),
                    ),
                ),
            );
        }

        $descriptor['md:NameIDFormat'] = array(
            '__v' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'
        );

        // Set SSO on IDP
        if (isset($entity['SingleSignOnService'])) {
            $descriptor['md:SingleSignOnService'] = array(
                '_Binding' => self::DEFAULT_REQUEST_BINDING,
                '_Location' => $this->_server->getCurrentEntityUrl('singleSignOnService', $entityId),
            );
        }

        // Set consumer service on SP
        if (isset($entity['AssertionConsumerServices'])) {
            $descriptor['md:AssertionConsumerService'] = array(
                '_Binding'  => self::DEFAULT_RESPONSE_BINDING,
                '_Location' => $this->_server->getCurrentEntityUrl('assertionConsumerService', $entityId),
                '_index' => '1',
            );
        }

        return $descriptor;
    }

    /**
     *
     * @param array $spEntity
     * @param array $entitiesDescriptor
     * @throws Exception
     */
    protected function _signAndSendEntitiesMetadata($spEntity, $entitiesDescriptor)
    {
        $alternatePublicKey  = isset($spEntity['AlternatePublicKey']) ? $spEntity['AlternatePublicKey'] : null;
        $alternatePrivateKey = isset($spEntity['AlternatePublicKey']) ? $spEntity['AlternatePublicKey'] : null;
        $entitiesDescriptor = $this->_server->sign($entitiesDescriptor, $alternatePublicKey, $alternatePrivateKey);

        $xml = EngineBlock_Corto_XmlToArray::array2xml($entitiesDescriptor);

        $schemaUrl = 'http://docs.oasis-open.org/security/saml/v2.0/saml-schema-metadata-2.0.xsd';
        if ($this->_server->getConfig('debug', false) && ini_get('allow_url_fopen')) {
            $dom = new DOMDocument();
            $dom->loadXML($xml);
            if (!$dom->schemaValidate($schemaUrl)) {
                echo '<pre>' . htmlentities(EngineBlock_Corto_XmlToArray::formatXml($xml)) . '</pre>';
                throw new Exception('Metadata XML doesnt validate against XSD at Oasis-open.org?!');
            }
        }
        $this->_server->sendHeader('Content-Type', 'application/xml');
        //$this->_server->sendHeader('Content-Type', 'application/samlmetadata+xml');
        $this->_server->sendOutput($xml);
    }

    protected function _getSpEntityDescriptor($spEntityId)
    {
        $entity = $this->_server->getRemoteEntity($spEntityId);
        if (!$entity) {
            return false;
        }

        if (!isset($entity['AssertionConsumerServices'])) {
            return false;
        }

        $entityDescriptor = array(
            '_validUntil' => $this->_server->timeStamp(strtotime('tomorrow') - time()),
            '_entityID' => $spEntityId,
            'md:SPSSODescriptor' => array(
                '_protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
            ),
        );

        if (isset($entity['certificates']['public'])) {
            $entityDescriptor['md:SPSSODescriptor']['md:KeyDescriptor'] = array(
                array(
                    '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    '_use' => 'signing',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                '__v' => $this->_server->getCertDataFromPem($entity['certificates']['public']),
                            ),
                        ),
                    ),
                ),
                array(
                    '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    '_use' => 'encryption',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                '__v' => $this->_server->getCertDataFromPem($entity['certificates']['public']),
                            ),
                        ),
                    ),
                ),
            );
        }

        $entityDescriptor['md:SPSSODescriptor']['md:NameIDFormat'] = array(
            '__v' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'
        );
        $entityDescriptor['md:SPSSODescriptor']['md:AssertionConsumerService'] = array(
            '_Binding' => self::DEFAULT_RESPONSE_BINDING,
            '_Location' => $this->_server->getCurrentEntityUrl('assertionConsumerService', $spEntityId),
            '_index' => '1',
        );

        return $entityDescriptor;
    }

    protected function _sendIntroductionMail($response, $attributes)
    {
        if (!isset($attributes['urn:mace:dir:attribute-def:mail'])) {
            return;
        }
        $config = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
        if (!isset($config->email->sendWelcomeMail) || !$config->email->sendWelcomeMail) {
            return;
        }

        $dbh = $this->_getConsentDatabaseConnection();
        $hashedUserId = sha1($this->_getConsentUid($response, $attributes));
        $query = "SELECT COUNT(*) FROM consent where hashed_user_id = ?";
        $parameters = array($hashedUserId);
        $statement = $dbh->prepare($query);
        $statement->execute($parameters);
        $timesUserGaveConsent = (int)$statement->fetchColumn();

        //we only send a mail if an user provides consent the first time
        if ($timesUserGaveConsent > 1) {
            return;
        }

        $mailer = new EngineBlock_Mail_Mailer();
        $emailAddress = $attributes['urn:mace:dir:attribute-def:mail'][0];
        $mailer->sendMail(
            $emailAddress,
            EngineBlock_Corto_Module_Services::INTRODUCTION_EMAIL,
            array(
                 '{user}' => $this->_getUserName($attributes)
            )
        );
    }

    protected function _getUserName($attributes)
    {
        if (isset($attributes['urn:mace:dir:attribute-def:givenName']) && isset($attributes['urn:mace:dir:attribute-def:sn'])) {
            return $attributes['urn:mace:dir:attribute-def:givenName'][0] . ' ' . $attributes['urn:mace:dir:attribute-def:sn'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:cn'])) {
            return $attributes['urn:mace:dir:attribute-def:cn'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:displayName'])) {
            return $attributes['urn:mace:dir:attribute-def:displayName'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:givenName'])) {
            return $attributes['urn:mace:dir:attribute-def:givenName'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:sn'])) {
            return $attributes['urn:mace:dir:attribute-def:sn'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:mail'])) {
            return $attributes['urn:mace:dir:attribute-def:mail'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:uid'])) {
            return $attributes['urn:mace:dir:attribute-def:uid'][0];
        }

        return "";
    }
}
