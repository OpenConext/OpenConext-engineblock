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

/**
 * Lazy Proxy for X509 Certificate.
 * Used when parsing / validation of the certificate is meant to be deferred until use
 * (bad idea in theory, sometimes useful in practice).
 */
class EngineBlock_X509_CertificateLazyProxy
{
    /**
     * @var EngineBlock_X509_CertificateFactory
     */
    private $_factory;

    /**
     * @var string
     */
    private $_certData;

    /**
     * @var EngineBlock_X509_Certificate
     */
    private $_certificate = null;

    /**
     * @param EngineBlock_X509_CertificateFactory $factory
     * @param $certData
     */
    function __construct(EngineBlock_X509_CertificateFactory $factory, $certData)
    {
        $this->_factory = $factory;
        $this->_certData = $certData;
    }

    public function __call($methodName, $methodArguments)
    {
        if (!$this->_certificate) {
            $this->_certificate = $this->_factory->fromCertData($this->_certData);
        }

        return call_user_func_array(array($this->_certificate, $methodName), $methodArguments);
    }
}
