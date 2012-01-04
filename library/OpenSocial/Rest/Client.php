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
 * Client for OpenSocial REST JSON API.
 *
 * @example OpenSocial_Rest_Client::create($httpClient)->get('/people/{uid}/@all', array('uid' => 'john doe'))
 *
 * @throws OpenSocial_Rest_Exception
 *
 */
class OpenSocial_Rest_Client
{
    /**
     * @var \Zend_Http_Client
     */
    protected $_httpClient;

    /**
     * Factory method to create a new OpenSocial REST client.
     * Inject a fully configured Zend_Http_Client (from Zend_OAuth?)
     *
     * @static
     * @param Zend_Http_Client $httpClient Configured HTTP client
     * @return OpenSocial_Rest_Client
     */
    public static function create(Zend_Http_Client $httpClient)
    {
        return new static($httpClient);
    }

    /**
     * Create a new OpenSocial REST client.
     * Inject a fully configured Zend_Http_Client (from Zend_OAuth?).
     *
     * @param Zend_Http_Client $httpClient Configured HTTP client
     */
    public function __construct(Zend_Http_Client $httpClient)
    {
        $this->_httpClient = $httpClient;
    }

    /**
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        return $this->_httpClient;
    }

    /**
     * Get OpenSocial data for an OpenSocial URI, expect zero or one model.
     *
     * @param string $uri
     * @param array  $params
     * @return OpenSocial_Model_Interface|null
     */
    public function getOne($uri, $params)
    {
        $models = $this->get($uri, $params);
        if (count($models) === 0) {
            return null;
        }
        if (count($models) > 1) {
            throw new OpenSocial_Rest_Exception(
                "Multiple models found for '$uri', only one expected"
            );
        }
        return array_shift($models);
    }

    /**
     * Get OpenSocial data for an OpenSocial URI
     *
     * Using the following syntax:
     * /people/{uid}/@all with params array('uid' => 'john.doe')
     * will cause the HTTP client to use the following uri:
     * /people/123%20abc/@all
     *
     * @example $client->get('/people/{uid}/@all', array('uid' => 'john.doe'));
     *
     * @param string $uri    (Prepared) OpenSocial URI
     * @param array  $params Data for prepared URI
     * @return array Models for OpenSocial data (/person returns OpenSocial_Model_Person objects, etc.)
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

    /**
     * Convert a 'prepared' URI to a full URI.
     *
     * @example _getPreparedUri('/people/{uid}/@all', array('uid' => 'john doe'))
     *          returns '/people/john%20doe/@all'
     *
     * @param string $uri
     * @param array  $params
     * @return string
     */
    protected function _getPreparedUri($uri, array $params)
    {
        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }
        return $uri;
    }

    /**
     * Make sure the first character is a /.
     *
     * @param string $uri
     * @return string URI guaranteed with leading slash
     */
    protected function _prependSlash($uri)
    {
        if ($uri[0] !== '/') {
            return '/' . $uri;
        }
        else {
            return $uri;
        }
    }

    /**
     * Get the type of service called in the URI.
     *
     * @param string $uri
     * @return string Service (example: 'Person' or 'Group')
     */
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
        if (strpos($response->getHeader('Content-Type'), 'application/json') !== 0) {
            throw new OpenSocial_Rest_Exception(
                "Unknown Content-Type for response:<br /> " . 
                var_export($response, true) .
                ' with body: ' .
                var_export($response->getBody(), true) .
                ' for request: ' .
                $this->_httpClient->getLastRequest()
            );
        }
        
        $modelClass = 'OpenSocial_Model_' . $this->_getModelTypeForService($serviceType);
        if (!class_exists($modelClass, true)) {
            throw new OpenSocial_Rest_Exception("Model class $modelClass not found for service $serviceType!");
        }

        /**
         * @var OpenSocial_Rest_Mapper_Interface $mapper
         */
        $mapper = new OpenSocial_Rest_Mapper_Json($modelClass);
        return $mapper->map($response->getBody());
    }

    protected function _getModelTypeForService($serviceType)
    {
        switch ($serviceType) {
            case 'Groups':
                return 'Group';
            case 'Person':
                return 'Person';
            case 'People':
                return 'Person';
            default:
                throw new OpenSocial_Rest_Exception("Unknown serviceType $serviceType, can not find a model for it!");
        }
    }
}
