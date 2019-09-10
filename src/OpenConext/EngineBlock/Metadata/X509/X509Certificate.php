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

use RuntimeException;

/**
 * Class X509Certificate
 * @package OpenConext\EngineBlock\Metadata
 */
class X509Certificate
{
    const PEM_HEADER = '-----BEGIN CERTIFICATE-----';
    const PEM_FOOTER = '-----END CERTIFICATE-----';

    /**
     * @var resource
     */
    private $opensslResource;

    /**
     * @param $opensslResource
     * @throws RuntimeException
     */
    public function __construct($opensslResource)
    {
        if (empty($opensslResource)) {
            throw new RuntimeException('Invalid OpenSSL key!');
        }

        $this->opensslResource = $opensslResource;
    }

    /**
     * @return resource
     */
    public function toResource()
    {
        return $this->opensslResource;
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function toPem()
    {
        $pem = '';
        $exported = openssl_x509_export($this->opensslResource, $pem);

        if (!$exported) {
            throw new RuntimeException("Unable to convert certificate to PEM?");
        }

        return $pem;
    }

    /**
     * @return string
     */
    public function toCertData()
    {
        $pemKey = $this->toPem();

        $lines = explode("\n", $pemKey);
        $data = '';
        foreach ($lines as $line) {
            $line = rtrim($line);

            // Skip the header
            if ($line === self::PEM_HEADER) {
                continue;
            }

            // End transformation on footer
            if ($line === self::PEM_FOOTER) {
                break;
            }

            $data .= $line;
        }

        return $data;
    }
}
