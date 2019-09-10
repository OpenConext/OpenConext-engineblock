<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use SAML2\DOMDocumentFactory;

class EngineBlock_Xml_Validator
{
    private $_schemaLocation;

    public function __construct($schemaLocation)
    {
        $this->_schemaLocation = $schemaLocation;
    }

    /**
     * Validates xml against a given schema
     *
     * @param string $xml
     * @return void
     * @throws EngineBlock_Exception in case validating itself fails or if xml does not validate
     */
    public function validate($xml)
    {
        if (!ini_get('allow_url_fopen')) {
            throw new EngineBlock_Exception(
                'Failed validating XML, url_fopen is not allowed'
            );
        }

        // Load schema
        $schemaXml = @file_get_contents($this->_schemaLocation);
        if ($schemaXml === false) {
            throw new EngineBlock_Exception(
                sprintf('Failed validating XML, schema url could not be opened: "%s"', $this->_schemaLocation)
            );
        }

        $schemaXml = $this->_absolutizeSchemaLocations($schemaXml, $this->_schemaLocation);

        $dom = DOMDocumentFactory::fromString($xml);
        if (!@$dom->schemaValidateSource($schemaXml)) {
            $errorInfo = error_get_last();
            $errorMessage = $errorInfo['message'];
            // @todo improve parsing message by creating custom exceptions for which know that structure of messages
            $parsedErrorMessage = preg_replace('/\{[^}]*\}/', '', $errorMessage);
            echo '<pre>' . htmlentities(EngineBlock_Corto_XmlToArray::formatXml($xml)) . '</pre>';
            throw new EngineBlock_Exception(
                sprintf(
                    'Metadata XML doesn\'t validate against schema at "%s", gives error: "%s"',
                    $schemaXml,
                    $parsedErrorMessage
                )
            );
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
}
