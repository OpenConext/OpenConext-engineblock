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

class Test_Controller_IdentityProvider extends PHPUnit_Framework_TestCase
{
    public function testSendAuthenticationRequestWithIdP()
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();

        // Mock an authentication request
        $request = new EngineBlock_Http_Request();
        $samlRequest = 'nZJBbxoxEIX%2Fysr33TUEsqwFSDSoKlLaoEBz6KXyrodgyR5vPbNp%2B%2B9rllShPXDIyfLMvOfnTzMn7'.
                'V2nVj0f8RF%2B9ECc%2FfIOSQ2NhegjqqDJkkLtgRS3arf6fK%2FGhVRdDBza4MSF5LpCE0FkG1Bkm%2FVCfAeoG2kOlZzW41lz'.
                'OwXZTCqoZ9royWwqD%2Bb2phrJCowU2RNESsqFSEZJTtTDBok1cirJkcxllY%2Fr%2FWiqbmo1mnwT2Tr9xqLmQXVk7kiVJTQFNAZ'.
                'eCgQuvbZYksVnBzv7jA%2B4g%2FhiWxDZ6m%2FSu4DUe4ivna%2BP929eRAV1F3ZkfefghKH0wfQOiu7YlcOdzuc41y0NVQMH3TvO'.
                'qRPZ9hXkB4smhbnOsDkPkfq032%2Fz7cNuL5bzk7camMTl%2B9J54ESd9X%2Fh5uWl9fy8Ll9SqM16G5xtf2cfQ%2FSar2c%2BVaz'.
                'JD8Oo4qiRLCAnzM6Fn3cRNMNCcOxBlMvzk%2F8u5fIP';
        $relayState = 'https%3A%2F%2Fss.sp.ebdev.net%2Fsimplesaml%2Fmodule.php%2Fcore%2Fauthenticate.php%3Fas%3Ddefault-sp';
        $request->setQueryString('SAMLRequest=' . $samlRequest . '&RelayState=' . $relayState);
        $application->setHttpRequest($request);

        // Initiate response object
        $response = new EngineBlock_Http_Response();
        $application->setHttpResponse($response);

        // Mock MetaData
        $singleSignOnUrl = 'http://myidp.example.com/main/SingleSignOn';
        $metaData = new EngineBlock_MetaData();
        $metaData->setSingleSignOnUrlByEntityId('myidp', $singleSignOnUrl);
        $application->setMetaData($metaData);

        // CALL CONTROLLER
        $controller = new Authentication_Controller_IdentityProvider('test', 'IdentityProvider');
        $controller->singleSignOnAction('myidp');

        // Try getting a Redirect URL from the response object
        $url = $response->getRedirectUrl();

        // Get the http://host/path part of the url to redirect to
        $urlParsed = parse_url($url);
        unset($urlParsed['query']);
        unset($urlParsed['fragment']);

        // Get the http://host/path part of the url we set as single sign on url
        $singleSignOnUrlParsed = parse_url($singleSignOnUrl);
        unset($singleSignOnUrlParsed['query']);
        unset($singleSignOnUrlParsed['fragment']);

        // Check whether we're being redirected to the destination IdP URL
        $this->assertEquals($singleSignOnUrlParsed, $urlParsed, "IdP preselection returns the proper IdP");

        // Check whether the RelayState parameter is being passed along properly
        $urlParsed = parse_url($url);
        $urlQuery = isset($urlParsed['query']) && $urlParsed['query'] ? $urlParsed['query'] : ''; 
        $this->assertContains('RelayState=' . $relayState, $urlQuery, 'RelayState param is sent along');
    }

    public function testSendAuthenticationRequestWithoutIdP()
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();

        // Mock an authentication request
        $request = new EngineBlock_Http_Request();
        $samlRequest = 'nZJBbxoxEIX%2Fysr33TUEsqwFSDSoKlLaoEBz6KXyrodgyR5vPbNp%2B%2B9rllShPXDIyfLMvOfnTzMn7'.
                'V2nVj0f8RF%2B9ECc%2FfIOSQ2NhegjqqDJkkLtgRS3arf6fK%2FGhVRdDBza4MSF5LpCE0FkG1Bkm%2FVCfAeoG2kOlZzW41lz'.
                'OwXZTCqoZ9royWwqD%2Bb2phrJCowU2RNESsqFSEZJTtTDBok1cirJkcxllY%2Fr%2FWiqbmo1mnwT2Tr9xqLmQXVk7kiVJTQFNAZ'.
                'eCgQuvbZYksVnBzv7jA%2B4g%2FhiWxDZ6m%2FSu4DUe4ivna%2BP929eRAV1F3ZkfefghKH0wfQOiu7YlcOdzuc41y0NVQMH3TvO'.
                'qRPZ9hXkB4smhbnOsDkPkfq032%2Fz7cNuL5bzk7camMTl%2B9J54ESd9X%2Fh5uWl9fy8Ll9SqM16G5xtf2cfQ%2FSar2c%2BVaz'.
                'JD8Oo4qiRLCAnzM6Fn3cRNMNCcOxBlMvzk%2F8u5fIP';
        $relayState = 'https%3A%2F%2Fss.sp.ebdev.net%2Fsimplesaml%2Fmodule.php%2Fcore%2Fauthenticate.php%3Fas%3Ddefault-sp';
        $request->setQueryString('SAMLRequest=' . $samlRequest . '&RelayState=' . $relayState);
        $request->setHostName('test.engineblock.example.com');
        $request->setProtocol(false);
        $application->setHttpRequest($request);

        // Initiate response object
        $response = new EngineBlock_Http_Response();
        $application->setHttpResponse($response);

        // CALL CONTROLLER
        $controller = new Authentication_Controller_IdentityProvider('test', 'IdentityProvider');
        $controller->singleSignOnAction();

        // Try getting a Redirect URL from the response object
        $url = $response->getRedirectUrl();
        $urlParsed = parse_url($url);

        $this->assertEquals("/authentication/proxy/wayf", $urlParsed['path'], "Calling SSO without IdP leads to redirect to WAYF");
    }
}