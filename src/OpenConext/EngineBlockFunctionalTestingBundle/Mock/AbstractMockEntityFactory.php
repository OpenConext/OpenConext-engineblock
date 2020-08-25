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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

use DOMDocument;
use DOMElement;
use SAML2\XML\Chunk;
use SAML2\XML\ds\KeyInfo;
use SAML2\XML\ds\KeyName;
use SAML2\XML\ds\X509Certificate;
use SAML2\XML\ds\X509Data;
use SAML2\XML\md\KeyDescriptor;

/**
 * Class AbstractMockEntityFactory
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Mock
 */
abstract class AbstractMockEntityFactory
{
    /**
     * @return KeyDescriptor
     */
    protected function generateDefaultSigningKeyPair()
    {
        $signingKey = new KeyDescriptor();
        $signingKey->setUse('signing');

        $keyInfo = new KeyInfo();
        $keyInfo->setId('CONEXT-ETS-KEY-SNAKEOIL');

        $keyName = new KeyName();
        $keyName->setName('snakeoil');

        $x509Data = new X509Data();

        $certificate = new X509Certificate();
        $certificate->setCertificate(trim(file_get_contents(__DIR__ . '/../Resources/keys/snakeoil.certData')));

        $domElement = new DOMElement('PrivateKey');
        $domElement->nodeValue = trim(file_get_contents(__DIR__ . '/../Resources/keys/snakeoil.key'));

        $document = new DOMDocument();
        $document->appendChild($domElement);
        $privateKeyChunk = new Chunk($domElement);

        $x509Data->setData([$certificate]);
        $info = [$keyName, $x509Data, $privateKeyChunk];
        $keyInfo->setInfo($info);
        $signingKey->setKeyInfo($keyInfo);

        return $signingKey;
    }
}
