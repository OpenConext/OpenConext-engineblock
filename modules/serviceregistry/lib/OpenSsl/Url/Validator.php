<?php
/**
 * SURFconext Service Registry
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext Service Registry
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 *
 */ 
class OpenSsl_Url_Validator
{
    protected $_url;

    public function __construct($url)
    {
        $this->_url = $url;
    }

    public function validate()
    {
        try {
            $sslUrl = new OpenSsl_Url($this->_url);
        }
        catch (Exception $e) {
            $endpointResponse->Errors[] = "Endpoint is not a valid URL";
            return $this->_sendResponse();
        }

        if (!$sslUrl->isHttps()) {
            $endpointResponse->Errors[] = "Endpoint is not HTTPS";
            return $this->_sendResponse();
        }


        $connectSuccess = $sslUrl->connect();
        if (!$connectSuccess) {
            $endpointResponse->Errors[] = "Endpoint is unreachable";
            return $this->_sendResponse();
        }


        if (!$sslUrl->isCertificateValidForUrlHostname()) {
            $urlHostName = $sslUrl->getHostName();
            $validHostNames = $sslUrl->getServerCertificate()->getValidHostNames();
            $endpointResponse->Errors[] = "Certificate does not match the hostname '$urlHostName' (instead it matches " . implode(', ', $validHostNames) . ")";
        }

        $urlChain = $sslUrl->getServerCertificateChain();

        $certificates = $urlChain->getCertificates();
        foreach ($certificates as $certificate) {
            $certificateSubject = $certificate->getSubject();

            $endpointResponse->CertificateChain[] = array(
                'Subject' => array(
                    'DN' => $certificate->getSubjectDn(),
                    'CN' => (isset($certificateSubject['CN'])?$certificateSubject['CN']:$certificateSubject['O']),
                ),
                'SubjectAlternative' => array(
                    'DNS' => $certificate->getSubjectAltNames(),
                ),
                'Issuer' => array(
                    'Dn' => $certificate->getIssuerDn(),
                ),
                'NotBefore' => array(
                    'UnixTime' => $certificate->getValidFromUnixTime(),
                ),
                'NotAfter' => array(
                    'UnixTime' => $certificate->getValidUntilUnixTime(),
                ),
                'RootCa'     => $certificate->getTrustedRootCertificateAuthority(),
                'SelfSigned' => $certificate->isSelfSigned(),
            );
        }

        $urlChainValidator = new OpenSsl_Certificate_Chain_Validator($urlChain);
        $urlChainValidator->validate();
    }
}
