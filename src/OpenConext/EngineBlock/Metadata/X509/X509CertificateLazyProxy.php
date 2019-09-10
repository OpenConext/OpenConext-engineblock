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
 * Lazy Proxy for X509 Certificate.
 * Used when parsing / validation of the certificate is meant to be deferred until use
 * (useful if your upstream certificate supplier does not do checking and hands you all certificates at once).
 */
class X509CertificateLazyProxy
{
    /**
     * @var X509CertificateFactory
     */
    private $factory;

    /**
     * @var string
     */
    private $certData;

    /**
     * @var X509Certificate
     */
    private $certificate = null;

    /**
     * @param X509CertificateFactory $factory
     * @param $certData
     */
    public function __construct(X509CertificateFactory $factory, $certData)
    {
        $this->factory = $factory;
        $this->certData = $certData;
    }

    /**
     * @param $methodName
     * @param $methodArguments
     * @return mixed
     */
    public function __call($methodName, $methodArguments)
    {
        if (!$this->certificate) {
            $this->certificate = $this->factory->fromCertData($this->certData);
        }

        return call_user_func_array(array($this->certificate, $methodName), $methodArguments);
    }

    /**
     * Take care not to serialize the openSSL resource ($this->certificate).
     */
    public function __sleep()
    {
        return array('certData', 'factory');
    }
}
