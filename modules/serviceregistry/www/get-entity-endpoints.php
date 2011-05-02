<?php
/**
 *
 */
ini_set('display_errors', true);
require '_includes.php';

$srConfig = SimpleSAML_Configuration::getConfig('module_serviceregistry.php');
$rootCertificatesFile = $srConfig->getString('ca_bundle_file');

$server = new EntityEndpointsServer();
$server->setTrustedRootCertificateAuthoritiesFile($rootCertificatesFile);
$server->serve($_GET['eid']);

class EntityEndpointsServer
{
    protected $_response;

    protected $_endpointMetadataFields = array(
        'SingleSignOnService',
        'AssertionConsumerService',
        'SingleLogoutService'
    );

    protected $_entityMetadata;

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
        $this->_response = $response;
    }

    protected function _loadEntityMetadata($entityId)
    {
        $janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
        $entityController = new sspmod_serviceregistry_EntityController($janusConfig);
        $entityController->setEntity($entityId);
        $entityController->loadEntity();

        $this->_entityMetadata = $entityController->getMetaArray();
    }

    public function serve($entityId)
    {
        if (isset($this->_trustedRootCertificateAuthoritiesFile)) {
            OpenSsl_Certificate_Chain_Factory::loadRootCertificatesFromFile(
                $this->_trustedRootCertificateAuthoritiesFile
            );
        }
        
        $this->_loadEntityMetadata($entityId);

        foreach ($this->_endpointMetadataFields as $endPointMetaKey) {
            if (!isset($this->_entityMetadata[$endPointMetaKey])) {
                // This entity does not have this binding
                continue;
            }

            foreach ($this->_entityMetadata[$endPointMetaKey] as $index => $binding) {
                $endpointResponse = new stdClass();
                $endpointResponse->CertificateChain = array();
                $endpointResponse->Errors = array();
                $endpointResponse->Warnings = array();

                $key = $endPointMetaKey . $index;
                $this->_response->$key = $endpointResponse;
                $endpointResponse = &$this->_response->$key;

                if (!isset($binding['Location']) || trim($binding['Location'])==="") {
                    $endpointResponse->Errors[] = "Binding has no Location?";
                    return $this->_sendResponse();
                }
                else {
                    $endpointResponse->Url = $binding['Location'];
                }

                try {
                    $sslUrl = new OpenSsl_Url($binding['Location']);
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

                $endpointResponse->Warnings = array_merge($endpointResponse->Warnings, $urlChainValidator->getWarnings());
                $endpointResponse->Errors   = array_merge($endpointResponse->Errors,   $urlChainValidator->getErrors());
            }
        }
        return $this->_sendResponse();
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