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

class Dummy_Controller_ServiceProvider extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
        $this->setTestCaseFromRequest($this->_getRequest());

        if (!empty($_POST)) {
            $responseFactory = new EngineBlock_Saml2_ResponseFactory();
            $samlResponse = $responseFactory->createFromHttpRequest($this->_getRequest());
            // @todo check status, for now it's assumed that a receiving a valid response means the user is logged in
            $_SESSION['loggedin'] = true;
        }

        if (empty($_SESSION['loggedin'])) {

            $authNRequest = $this->factoryAuthnRequest();

            $bindingType = Dummy_Model_Binding_BindingFactory::TYPE_REDIRECT;
            $testCase = $this->factoryTestCaseFromSession($_SESSION);
            if ($testCase instanceof Dummy_Model_Sp_TestCase_TestCaseInterface) {
                $authNRequest = $testCase->decorateRequest($authNRequest);
                $bindingType = $testCase->setBindingType($bindingType);
            }

            $bindingFactory = new Dummy_Model_Binding_BindingFactory();
            $binding = $bindingFactory->create($authNRequest, $bindingType);
            $binding->output();
        }

        header('Content-Type: text/html');
        die('<html><body><h1>DUMMY SP</h1></body></html>');
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     */
    private function setTestCaseFromRequest(EngineBlock_Http_Request $httpRequest)
    {
        $testCase = $httpRequest->getQueryParameter('testCase');
        if ($testCase) {
            $_SESSION['dummy']['sp']['testCase'] = $testCase;
            exit;
        }
    }

    /**
     * @param array $session
     * @throws InvalidArgumentException
     */
    private function factoryTestCaseFromSession(array $session) {
        if (!isset($session['dummy']['sp']['testCase'])) {
            return;
        }
        $testCaseClass = 'Dummy_Model_Sp_TestCase_' . $session['dummy']['sp']['testCase'];
        if (!class_exists($testCaseClass)) {
            throw new \InvalidArgumentException("Sp testcase '" . $testCaseClass . ' does not exist');
        }

        return new $testCaseClass();
    }

    /**
     * @return SAML2_AuthnRequest
     */
    private function factoryAuthnRequest()
    {
        $engineUrl = 'https://' . $_SERVER['HTTP_HOST'];

        $destinationUrl = $engineUrl . '/authentication/idp/single-sign-on';

        $spUrl = $_SERVER['SCRIPT_URI'];
        if (!empty($_GET['nr'])) {
            $spUrl .= '?nr=' . urlencode($_GET['nr']);
        }
        $assertionConsumerServiceURL = $spUrl;
        $issuerUrl = $spUrl;
        $authnRequestFactory = new EngineBlock_Saml2_AuthnRequestFactory();
        $authnRequest = $authnRequestFactory->create(
            $destinationUrl,
            $assertionConsumerServiceURL,
            $issuerUrl
        );

        return $authnRequest;
    }
}
