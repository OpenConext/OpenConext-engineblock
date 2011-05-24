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

ini_set('display_errors', true);
require '_includes.php';

$srConfig = SimpleSAML_Configuration::getConfig('module_serviceregistry.php');
$rootCertificatesFile = $srConfig->getString('ca_bundle_file');

$server = new EntityCertificateServer();
$server->setTrustedRootCertificateAuthoritiesFile($rootCertificatesFile);
$server->serve($_GET['eid']);

class EntityCertificateServer
{
    protected $_response;

    /**
     * @var OpenSsl_Certificate
     */
    protected $_certificate;

    /**
     * @var OpenSsl_Certificate_Chain
     */
    protected $_certificateChain;

    protected $_trustedRootCertificateAuthoritiesFile;
    
    public function __construct()
    {
        $this->_initializeResponse();
    }

    public function setTrustedRootCertificateAuthoritiesFile($file)
    {
        $this->_trustedRootCertificateAuthoritiesFile = $file;
    }

    protected function _initializeResponse()
    {
        $response = new stdClass();
        $response->CertificateChain = array();
        $response->Warnings = array();
        $response->Errors = array();
        $this->_response = $response;
    }
    
    public function serve($entityId)
    {
        if (!$this->_loadEntityCertificate($entityId)) {
            return $this->_sendResponse();
        }

        $this->_checkCertificateValidity();
        $this->_loadCertificateChain();
        $this->_checkChainValidity();
        return $this->_sendResponse();
    }

    protected function _loadEntityCertificate($entityId)
    {
        $janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
        $entityController = new sspmod_serviceregistry_EntityController($janusConfig);
        $entityController->setEntity($entityId);
        $entityController->loadEntity();

        try {
            $this->_certificate = $entityController->getCertificate();
            return true;
        } catch(Janus_Exception_NoCertData $e) {
            $this->_response->Warnings[] = "No certificate data for this entity";
            return false;
        }
    }

    protected function _checkCertificateValidity()
    {
        $validator = new OpenSsl_Certificate_Validator($this->_certificate);
        $validator->setIgnoreSelfSigned(true);
        $validator->validate();

        $this->_response->Warnings = array_merge($this->_response->Warnings, $validator->getWarnings());
        $this->_response->Errors   = array_merge($this->_response->Errors,   $validator->getErrors());
    }

    protected function _loadCertificateChain()
    {
        if (isset($this->_trustedRootCertificateAuthoritiesFile)) {
            OpenSsl_Certificate_Chain_Factory::loadRootCertificatesFromFile(
                $this->_trustedRootCertificateAuthoritiesFile
            );
        }
        $this->_certificateChain = OpenSsl_Certificate_Chain_Factory::createFromCertificateIssuerUrl($this->_certificate);
        $certificates = $this->_certificateChain->getCertificates();
        foreach ($certificates as $certificate) {
            $certificateSubject = $certificate->getSubject();
        
            $this->_response->CertificateChain[] = array(
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
                'RootCa' => $certificate->getTrustedRootCertificateAuthority(),
                'SelfSigned' => $certificate->isSelfSigned(),
            );
        }
    }

    protected function _checkChainValidity()
    {
        $validator = new OpenSsl_Certificate_Chain_Validator($this->_certificateChain);
        $validator->setIgnoreSelfSigned(true);
        if (isset($this->_trustedRootCertificateAuthoritiesFile)) {
            $validator->setTrustedRootCertificateAuthorityFile($this->_trustedRootCertificateAuthoritiesFile);
        }
        $validator->validate();

        $this->_response->Warnings = array_merge($this->_response->Warnings, $validator->getWarnings());
        $this->_response->Errors   = array_merge($this->_response->Errors,   $validator->getErrors());
    }
    
    protected function _sendResponse()
    {
        $this->_outputContentType('application/json');
        $this->_outputResponse();
    }

    protected function _outputContentType($contentType)
    {
        header("Content-Type: $contentType");
    }

    protected function _outputResponse()
    {
        echo json_encode($this->_response);
    }
}