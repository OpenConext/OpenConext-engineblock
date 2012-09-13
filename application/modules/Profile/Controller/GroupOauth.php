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

class Profile_Controller_GroupOauth extends Default_Controller_LoggedIn
{
    /**
     * @example /profile/group-oauth/authenticate/provider2
     *
     * @param string $providerId
     * @return void
     */
    public function authenticateAction($providerId)
    {
        $this->setNoRender();

        $_SESSION['return_url'] = $this->_getRequest()->getQueryParameter('return_url');

        $providerConfig = $this->_getProviderConfiguration($providerId);
        $consumer = new Zend_Oauth_Consumer($providerConfig->auth);

        // Do an HTTP request to the provider to fetch a request token
        $requestToken = $consumer->getRequestToken();

        // persist the token to session as we redirect the user to the provider
        if (!isset($_SESSION['request_token'])) {
            $_SESSION['request_token'] = array();
        }
        $_SESSION['request_token'][$providerId] = serialize($requestToken);

        // redirect the user to the provider
        $consumer->redirect();
    }

    /**
     *
     * @example /profile/group-oauth/consume/provider2?oauth_token=request-token
     *
     * @param string $providerId
     * @return void
     */
    public function consumeAction($providerId)
    {
        $this->setNoRender();

        $providerConfig = $this->_getProviderConfiguration($providerId);
        $consumer = new Zend_Oauth_Consumer($providerConfig->auth);

        $queryParameters = $this->_getRequest()->getQueryParameters();

        if (empty($queryParameters)) {
            throw new EngineBlock_Exception(
                'Unable to consume access token, no query parameters given',
                EngineBlock_Exception::CODE_NOTICE
            );
        }

        if (!isset($_SESSION['request_token'][$providerId])) {
            throw new EngineBlock_Exception(
                "Unable to consume access token, no request token (session lost?)",
                EngineBlock_Exception::CODE_NOTICE
            );
        }

        $requestToken = unserialize($_SESSION['request_token'][$providerId]);

        $token = $consumer->getAccessToken(
            $queryParameters,
            $requestToken
        );
        $userId = $this->attributes['nameid'][0];
        $provider = EngineBlock_Group_Provider_OpenSocial_Oauth_ThreeLegged::createFromConfigs(
            $providerConfig,
            $userId
        );
        $provider->setAccessToken($token);

        if (!$provider->validatePreconditions()) {

            EngineBlock_ApplicationSingleton::getLog()->notice(
                "Unable to test OpenSocial 3-legged Oauth provider because not all preconditions have been matched?",
                EngineBlock_Log_Message_AdditionalInfo::create()->setUserId($userId)
            );
            $this->providerId = $providerId;
            $this->renderAction("Error"); 
        } else {
            // Now that we have an Access Token, we can discard the Request Token
            $_SESSION['request_token'][$providerId] = null;

            $this->_redirectToUrl($_SESSION['return_url']);
        }

    }

//    /**
//     * Simply renders the Error.phtml
//     *
//     * @return void
//     */
//    public function errorAction()
//    {
//    }

    /**
     *
     * @example /profile/group-oauth/revoke?provider=providerId
     *
     * @return void
     */
    public function revokeAction()
    {
        $this->setNoRender();

        $providerId = $this->_getRequest()->getQueryParameter('provider');

        $this->user->deleteOauthGroupConsent($providerId);

        $this->_redirectToUrl('/#MyGroups');
    }

    protected function _getProviderConfiguration($providerId)
    {
        $configReader = new EngineBlock_Group_Provider_ProviderConfig();
        return $configReader->createFromDatabaseFor($providerId)->current();
    }
}