
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

class Dummy_Controller_IdentityProvider extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
        $this->setTestCaseFromRequest($this->_getRequest());

        $authnRequestFactory = new EngineBlock_Saml_AuthnRequestFactory();
        $authnRequest = $authnRequestFactory->createFromHttpRequest($this->_getRequest());

        $responseFactory = new EngineBlock_Saml_ResponseFactory();
        $idpConfig = $this->createIdpConfig();
        // Required attributes
        $nameId = 'johndoe';
        $issuer = $_SERVER['SCRIPT_URI'];
        $attributes = array(
            'urn:mace:dir:attribute-def:uid' => array('johndoe'),
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => array('example.com'),
        );

        $samlResponse = $responseFactory->create(
            $authnRequest,
            $idpConfig,
            $nameId,
            $issuer,
            $attributes
        );

        $testCase = $this->factoryTestCaseFromSession($_SESSION);
        if ($testCase instanceof Dummy_Model_Idp_TestCase_TestCaseInterface) {
            $testCase->decorateResponse($samlResponse);
        }

        $bindingFactory = new Dummy_Model_Binding_BindingFactory();
        $binding = $bindingFactory->create($samlResponse, $bindingFactory::TYPE_POST);
        $binding->output();
    }

    /**
     * Creates a config containing the keys needed for signing
     *
     * @return SimpleSAML_Configuration
     */
    private function createIdpConfig()
    {
        $sspIdpConfig = array();
        $sspIdpConfig['privatekey'] = ENGINEBLOCK_FOLDER_APPLICATION . 'modules/Dummy/keys/private_key.pem';
        $sspIdpConfig['certData'] = file_get_contents(ENGINEBLOCK_FOLDER_APPLICATION . 'modules/Dummy/keys/certificate.crt');
        return new SimpleSAML_Configuration($sspIdpConfig, null);
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     */
    private function setTestCaseFromRequest(EngineBlock_Http_Request $httpRequest)
    {
        $testCase = $httpRequest->getQueryParameter('testCase');
        if ($testCase) {
            $_SESSION['dummy']['idp']['testCase'] = $testCase;
            exit;
        }
    }

    /**
     * @param array $session
     * @throws InvalidArgumentException
     */
    private function factoryTestCaseFromSession(array $session) {
        if (!isset($session['dummy']['idp']['testCase'])) {
            return;
        }
        $testCaseClass = 'Dummy_Model_Idp_TestCase_' . $session['dummy']['idp']['testCase'];
        if (!class_exists($testCaseClass)) {
            throw new \InvalidArgumentException("Idp testcase '" . $testCaseClass . ' does not exist');
        }

        return new $testCaseClass();
    }

}
