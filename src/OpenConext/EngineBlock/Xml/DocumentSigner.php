<?php declare(strict_types=1);

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

namespace OpenConext\EngineBlock\Xml;

use DOMDocument;
use DOMElement;
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use SAML2\Utils;

class DocumentSigner
{
    public function sign(string $source, X509KeyPair $signingKeyPair) : string
    {
        // Load the XML to be signed
        $doc = new DOMDocument();
        if ($doc->loadXML($source) === false) {
            throw new RuntimeException('Could not parse XML source');
        }

        // Find root element to sign. The firstChild is the TOS comment,
        // so need to skip over that.
        if (!isset($doc->childNodes[1]) || !$doc->childNodes[1] instanceof DOMElement) {
            throw new RuntimeException("Could not locate root element to sign");
        }
        /** @var DOMElement $rootNode */
        $rootNode = $doc->childNodes[1];

        // Sign via SAML2\Utils which wraps xmlseclibs with wrapping-attack protection.
        // Key type (RSA_SHA256) implicitly selects SHA-256 digest inside Utils::insertSignature.
        Utils::insertSignature(
            $signingKeyPair->getPrivateKey()->toXmlSecurityKey(),
            [$signingKeyPair->getCertificate()->toPem()],
            $rootNode,
            $rootNode->firstChild
        );

        return $doc->saveXML();
    }
}
