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

class OpenSocial_Rest_Client
{
    protected $_httpClient;

    public static function create(Zend_Http_Client $httpClient)
    {
        return new static($httpClient);
    }

    public function __construct(Zend_Http_Client $httpClient)
    {
        $this->_httpClient = $httpClient;
    }

    public function getHttpClient()
    {
        return $this->_httpClient;
    }

    /**
     * Get OpenSocial data.
     *
     * Using the following syntax:
     * /people/{uid}/@all with params array('uid' => '123 abc')
     * will cause the HTTP client to use the following uri:
     * /people/123%20abc/@all
     *
     * @param  $query
     * @return array
     */
    public function get($uri, $params = array())
    {
        $uri = $this->_getPreparedUri($uri, $params);
        $uri = $this->_prependSlash($uri);

        $serviceType = $this->_getServiceTypeFromUri($uri);

        $uri = $this->_httpClient->getUri(true) . $uri;

        $response = $this->_httpClient->setUri($uri)->request(Zend_Http_Client::GET);

        return $this->_mapResponseToModels($serviceType, $response);
    }

    protected function _getPreparedUri($uri, $params)
    {
        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }
        return $uri;
    }

    protected function _prependSlash($uri)
    {
        if ($uri[0] !== '/') {
            return '/' . $uri;
        }
        else {
            return $uri;
        }
    }

    protected function _getServiceTypeFromUri($uri)
    {
        $secondSlashPos = strpos($uri, '/', 1);
        $firstUriPart = substr($uri, 1, $secondSlashPos - 1);
        return ucfirst($firstUriPart);
    }

    /**
     * @throws OpenSocial_Rest_Exception
     * @param  $serviceType
     * @param Zend_Http_Response $response
     * @return array
     */
    protected function _mapResponseToModels($serviceType, Zend_Http_Response $response)
    {
        if (substr($response->getHeader('Content-Type'), 0, 16)  === 'application/json') {
            $mapperClass = 'OpenSocial_Rest_Mapper_Json_' . $serviceType;
            if (!class_exists($mapperClass, true)) {
                throw new OpenSocial_Rest_Exception("Mapper class $mapperClass not found!");
            }

            /**
             * @var OpenSocial_Rest_Mapper_Interface $mapper
             */
            $mapper = new $mapperClass();
            return $mapper->map($response->getBody());
        }
        else {
            throw new OpenSocial_Rest_Exception("Unknown Content-Type for response: " . var_export($response, true));
        }
    }
}