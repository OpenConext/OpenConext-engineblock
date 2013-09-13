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

/**
 * This extends Zend_Rest_Client with an improved way of retrieving
 * results.
 *
 * Zend_Rest_Client uses Zend_Rest_Response which only handles
 * XML requests. EngineBlock_Rest_Client first checks the Content-Type
 * header of the result. If it's application/json we simply
 * json_decode the result, if it's anything else, the original
 * Zend_Rest_Client behaviour is used (which is to invoke an xml
 * parser).
 *
 * Note: the issue that Zend_Rest_Client is json unfriendly has been
 * logged as:
 * http://framework.zend.com/issues/browse/ZF-10272
 *
 * Keep an eye on this ticket; if it gets fixed, this override
 * may no longer be necessary.
 *
 * @author ivo
 *
 */
class EngineBlock_Rest_Client extends Zend_Rest_Client
{
    /**
     * @return array|Zend_Rest_Client_Result
     */
    public function get($args = array())
    {
        if (!isset($args[0])) {
            $args[0] = $this->_uri->getPath();
        }
        $this->_data['rest'] = 1;
        $data = array_slice($args, 1) + $this->_data;

        $response = $this->restGet($args[0], $data);
        $this->_logRequest();

        $this->_data = array();//Initializes for next Rest method.

        if ($response->getStatus() !== 200) {
            EngineBlock_ApplicationSingleton::getLog()->attach($response, 'Response');

            throw new EngineBlock_Exception(
                'Response status !== 200', EngineBlock_Exception::CODE_WARNING
            );
        }

        if (strpos($response->getHeader("Content-Type"), "application/json")!==false) {
            return json_decode($response->getBody(), true);
        } else {
            try {
                return new Zend_Rest_Client_Result($response->getBody());
            }
            catch (Zend_Rest_Client_Result_Exception $e) {
                EngineBlock_ApplicationSingleton::getLog()->attach($response, 'Response');

                throw new EngineBlock_Exception(
                    'Error parsing response', null, $e
                );
            }
        }
    }

    protected function _logRequest()
    {
        /**
         * @var Zend_Http_Client $httpClient
         */
        $httpClient = $this->getHttpClient();
        $log = EngineBlock_ApplicationSingleton::getLog();

        $log->attach($httpClient->getLastRequest(), 'REST Request');

        $originalBody = $httpClient->getLastResponse()->getBody();
        $body = substr($originalBody, 0, 1024);
        if ($body !== $originalBody) {
            $body .= '...';
        }

        // If able to decode as JSON, show parsed result
        $decoded = json_decode($body);
        if ($decoded) {
            $body = $decoded;
            $logType = 'original response below';
        } else {
            $logType = 'decoded JSON body below';
        }

        $log->attach($body, "REST Response ($logType)");
    }
}