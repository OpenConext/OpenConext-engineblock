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

/**
 * Class X509KeyPair
 * @package OpenConext\EngineBlock\Metadata
 */
class X509KeyPair
{
    /**
     * @var X509Certificate
     */
    private $certificate;

    /**
     * @var X509PrivateKey
     */
    private $private;

    /**
     * @param X509Certificate $certificate
     * @param X509PrivateKey $private
     */
    public function __construct(
        X509Certificate $certificate = null,
        X509PrivateKey $private = null
    ) {
        $this->certificate = $certificate;
        $this->private = $private;
    }

    /**
     * @return X509Certificate
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @return X509PrivateKey
     */
    public function getPrivateKey()
    {
        return $this->private;
    }
}
