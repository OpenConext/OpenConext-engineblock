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
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use RobRichards\XMLSecLibs\XMLSecurityDSig;

class DocumentSigner
{
    const SIGN_ALGORITHM = XMLSecurityDSig::SHA256;

    public function sign(string $source, X509KeyPair $signingKeyPair) : string
    {
        // Load the XML to be signed
        $doc = new DOMDocument();
        $doc->loadXML($source);

        // Find root element to sign. The firstChild is the TOS comment,
        // so need to skip over that.
        $rootNode = $doc->childNodes[1];

        // Create sign object
        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $objDSig->addReference(
            $rootNode,
            self::SIGN_ALGORITHM,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
            ['id_name' => 'ID', 'overwrite' => false]
        );

        // Load private key
        $objKey = $signingKeyPair->getPrivateKey()->toXmlSecurityKey();
        $objKey->loadKey($signingKeyPair->getPrivateKey()->getFilePath(), true);

        // Sign with private key
        $objDSig->sign($objKey);

        // Add the associated public key to the signature
        $objDSig->add509Cert($signingKeyPair->getCertificate()->toPem());

        // Append the signature to the XML
        $objDSig->insertSignature($doc->documentElement, $doc->documentElement->firstChild);

        // Save the signed XML
        return $doc->saveXML();
    }
}
