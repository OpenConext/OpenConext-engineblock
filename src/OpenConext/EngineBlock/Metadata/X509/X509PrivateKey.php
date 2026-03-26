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

namespace OpenConext\EngineBlock\Metadata\X509;

use RobRichards\XMLSecLibs\XMLSecurityKey;
use RuntimeException;

/**
 * X.509 private key representation.
 */
class X509PrivateKey
{
    private string $filePath;

    /**
     * @throws RuntimeException
     */
    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException(sprintf('Private key file "%s" does not exist.', $filePath));
        }

        if (!is_readable($filePath)) {
            throw new RuntimeException(sprintf('Private key file "%s" exists but is not readable.', $filePath));
        }

        $this->filePath = $filePath;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return XMLSecurityKey
     */
    public function toXmlSecurityKey(): XMLSecurityKey
    {
        $privateKeyObj = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $privateKeyObj->loadKey($this->filePath, true);
        return $privateKeyObj;
    }

    /**
     * Sign some data with this private key.
     *
     * @param string $data
     * @return string
     */
    public function sign(string $data): string
    {
        $privateKeyResource = openssl_pkey_get_private('file://' . $this->filePath);

        $signature = null;
        openssl_sign($data, $signature, $privateKeyResource);

        return $signature;
    }
}
