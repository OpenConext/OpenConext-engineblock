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

class EngineBlock_Test_Corto_Filter_Command_ValidateVoMembershipTest extends PHPUnit_Framework_TestCase
{
    const COLLAB_PERSON_ID = 'urn:collab:person:example.com:john.doe';
    const VO_NAME = 'vo_test';
    const IDP_NAME = 'https://mock-idp';

    protected $_validator;

    public function setup()
    {
        $this->_validator = new ValidateVoMembership();
        $this->_validator->setHttpAdapter(new Zend_Http_Client_Adapter_Test());
        $this->_validator->setCollabPersonId(self::COLLAB_PERSON_ID);
        $this->_validator->setRequest(array('__' => array(EngineBlock_Corto_ProxyServer::VO_CONTEXT_PFX => self::VO_NAME)));
        $this->_validator->setIdpMetadata(array('EntityId' => self::IDP_NAME));

        $adapter = Phake::mock('EngineBlock_Corto_Adapter');
        $this->_validator->setAdapter($adapter);

        EngineBlock_ApplicationSingleton::getInstance()->setConfiguration(new Zend_Config(
            array("api" => array("vovalidate" => array(
                "url" => "http://locahost/vovalidate",
                "key" => "some_key",
                "secret" => "some_secret",
            )
            ))));

    }

    public function testVoMembership()
    {
        $this->_recordValidRepsonse();
        $this->_validator->execute();

        $this->assertEquals(array(EngineBlock_Corto_Filter_Command_ValidateVoMembership::VO_NAME_ATTRIBUTE => self::VO_NAME), $this->_validator->getResponseAttributes());
    }

    public function testVoValidationurl()
    {
        $url = $this->_validator->getVoValidationUrl("https://localhost", self::VO_NAME, self::COLLAB_PERSON_ID, self::IDP_NAME);
        $this->assertEquals('https://localhost/vo_test/urn%3Acollab%3Aperson%3Aexample.com%3Ajohn.doe/https%3A%2F%2Fmock-idp', $url);
    }

    protected function _recordValidRepsonse()
    {
        $res = new Zend_Http_Response(200, array('Content-Type' => 'application/json'), '{ "value" : true }');
        $this->_validator->getHttpAdapter()->setResponse($res);
    }

}

class ValidateVoMembership extends EngineBlock_Corto_Filter_Command_ValidateVoMembership
{

    protected $_httpAdapter;

    protected function getHttpClient($url)
    {
        return new Zend_Http_Client($url, array(
            'adapter' => $this->_httpAdapter
        ));
    }

    public function getHttpAdapter()
    {
        return $this->_httpAdapter;
    }

    public function setHttpAdapter($httpAdapter)
    {
        $this->_httpAdapter = $httpAdapter;
    }

}