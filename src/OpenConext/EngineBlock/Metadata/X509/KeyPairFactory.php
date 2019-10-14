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

namespace OpenConext\EngineBlock\Metadata\X509;

use OpenConext\EngineBlock\Exception\RuntimeException;

class KeyPairFactory
{
    private $keyPairConfiguration = [];

    /**
     * @param array $keyPairConfiguration
     */
    public function __construct(array $keyPairConfiguration)
    {
        $this->keyPairConfiguration = $keyPairConfiguration;
    }

    /**
     * @param string $identifier
     * @return X509KeyPair
     *
     * @throws RuntimeException
     */
    public function buildFromIdentifier(string $identifier = 'default') : X509KeyPair
    {
        if (array_key_exists($identifier, $this->keyPairConfiguration)) {
            $keys = $this->keyPairConfiguration[$identifier];
            $privateKey = new X509PrivateKey($keys['privateFile']);
            $publicKey = new X509Certificate(openssl_x509_read(file_get_contents($keys['publicFile'])));
            return new X509KeyPair($publicKey, $privateKey);
        }
        throw new RuntimeException(
            sprintf('Unable to find the encryption key pair identified by "%s"', $identifier)
        );
    }
}
