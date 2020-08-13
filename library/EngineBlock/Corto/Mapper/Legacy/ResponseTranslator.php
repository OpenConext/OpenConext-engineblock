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
use SAML2\Response;
use SAML2\XML\saml\NameID;

/**
 * Class EngineBlock_Corto_Mapper_Legacy_ResponseTranslator
 */
class EngineBlock_Corto_Mapper_Legacy_ResponseTranslator
{
    /**
     * @var array
     */
    protected $privateVars = array(
        'Return',
        'OriginalIssuer',
        'OriginalNameId',
        'OriginalBinding',
        'OriginalResponse',
        'CollabPersonId',
        'CustomNameId',
        'IntendedNameId',
        'DeliverByBinding',
        'RelayState',
    );

    /**
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $response
     * @return array
     */
    public function fromNewFormat(EngineBlock_Saml2_ResponseAnnotationDecorator $response)
    {
        $legacyResponse = EngineBlock_Corto_XmlToArray::xml2array(
            $response->getSspMessage()->toUnsignedXML()->ownerDocument->saveXML()
        );

        return $this->addPrivateVarsToLegacy($legacyResponse, $response);
    }

    /**
     * @param array $legacyResponse
     * @return EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    public function fromOldFormat(array $legacyResponse)
    {
        $legacyResponse = EngineBlock_Corto_XmlToArray::registerNamespaces($legacyResponse);
        $xml = EngineBlock_Corto_XmlToArray::array2xml($legacyResponse);

        $document = DOMDocumentFactory::fromString($xml);

        $response = new Response($document->firstChild);
        $annotatedResponse = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);

        return $this->addPrivateVars($annotatedResponse, $legacyResponse);
    }

    /**
     * @param array $to
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $from
     * @return array
     */
    protected function addPrivateVarsToLegacy(array $to, EngineBlock_Saml2_ResponseAnnotationDecorator $from)
    {
        if (!isset($to[EngineBlock_Corto_XmlToArray::PRIVATE_PFX])) {
            $to[EngineBlock_Corto_XmlToArray::PRIVATE_PFX] = array();
        }

        foreach ($this->privateVars as $privateVar) {
            $method = 'get' . $privateVar;
            $value = $from->$method();
            if ($value) {
                if ($value instanceof EngineBlock_Saml2_ResponseAnnotationDecorator) {
                    $value = $this->fromNewFormat($value);
                }
                $to[EngineBlock_Corto_XmlToArray::PRIVATE_PFX][$privateVar] = $value;
            }
        }
        return $to;
    }

    /**
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $to
     * @param array $from
     * @return EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    protected function addPrivateVars(EngineBlock_Saml2_ResponseAnnotationDecorator $to, array $from)
    {
        foreach ($this->privateVars as $privateVar) {
            if (!isset($from[EngineBlock_Corto_XmlToArray::PRIVATE_PFX][$privateVar])) {
                continue;
            }

            $value = $from[EngineBlock_Corto_XmlToArray::PRIVATE_PFX][$privateVar];
            // NameIds need to be converted to their object representation (intended nameID is still a string)
            if ($privateVar === 'CustomNameId' && is_array($value)) {
                $nameId = new NameID();
                $nameId->setValue($value['Value']);
                $nameId->setFormat($value['Format']);
                $value = $nameId;
            }

            // Does this value have a __t tag? If so, make sure it's namespaces are registered.
            if (is_array($value) && isset($value[EngineBlock_Corto_XmlToArray::TAG_NAME_PFX])) {
                $value = $this->fromOldFormat($value);
            }
            $method = 'set' . $privateVar;
            $to->$method($value);
        }
        return $to;
    }
}
