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

require_once ENGINEBLOCK_FOLDER_LIBRARY . 'simplesamlphp/lib/_autoload.php';

class DummySp_Controller_Index extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
        if (!empty($_POST)) {
            $responseFactory = new EngineBlock_Saml_ResponseFactory();
            $samlResponse = $responseFactory->createFromHttpRequest($this->_getRequest());
            // @todo check status, for now it's assumed that a receiving a valid response means the user is logged in
            $_SESSION['loggedin'] = true;
        }

        if (empty($_SESSION['loggedin'])) {

            $authNRequest = $this->factoryAuthnRequest();

            $bindingFactory = new DummyIdp_Model_Binding_BindingFactory();
            $binding = $bindingFactory->create($authNRequest, $bindingFactory::TYPE_REDIRECT);
            $binding->output();
        }

        header('Content-Type: text/html');
        die('<html><body><h1>DUMMY SP</h1></body></html>');
    }

    /**
     * @return SAML2_AuthnRequest
     */
    private function factoryAuthnRequest()
    {
        $engineUrl = 'https://engine-test.demo.openconext.org';

        $destinationUrl = $engineUrl . '/authentication/idp/single-sign-on';
        $assertionConsumerServiceURL = 'https://engine-test.demo.openconext.org/dummy-sp';
        $issuerUrl = 'https://engine-test.demo.openconext.org/dummy-sp';
        $authnRequestFactory = new EngineBlock_Saml_AuthnRequestFactory();
        $authnRequest = $authnRequestFactory->create(
            $destinationUrl,
            $assertionConsumerServiceURL,
            $issuerUrl
        );

        return $authnRequest;
    }
}