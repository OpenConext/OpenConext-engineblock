<?php
/**
 *
 */
ini_set('display_errors', true);
require '_includes.php';

$server = new EntityEndpointsServer();
$server->serve($_GET['eid']);

class EntityEndpointsServer
{
    protected $_response;

    protected $_endpointMetadataFields = array('SingleSignOnService', 'AssertionConsumerService', 'SingleLogoutService');

    protected $_entityMetadata;

    public function __construct()
    {
        $this->_initializeResponse();
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

                $sslUrl = new OpenSsl_Url($binding['Location']);
                $connectSuccess = $sslUrl->connect();
                if (!$connectSuccess) {
                    $endpointResponse->Errors[] = "Endpoint is unreachable";
                    return $this->_sendResponse();
                }

                $urlCertificate = $sslUrl->getCertificate();

                $urlCertificateSubject = $urlCertificate->getSubject();
                $urlCertificateCn = $urlCertificateSubject['CN'];
                $urlCertificateAltNames = $urlCertificate->getSubjectAltNames();
                $urlHost = $sslUrl->getHost();

                $matches = false;;
                if (doesHostnameMatchPattern($urlHost, $urlCertificateCn)) {
                    $matches = true;
                }
                foreach ($urlCertificateAltNames as $altName) {
                    if (doesHostnameMatchPattern($urlHost, $altName)) {
                        $matches = true;
                    }
                }

                if (!$matches) {
                    $matchesHostNames = $urlCertificateCn . (!empty($urlCertificateAltNames)?',' . implode(', ', $urlCertificateAltNames):'');
                    $endpointResponse->Errors[] = "Certificate does not match the hostname '$urlHost' (instead it matches $matchesHostNames)";
                }

                $urlChain = OpenSsl_Certificate_Chain_Factory::create($urlCertificate);

                $certificates = $urlChain->getCertificates();
                foreach ($certificates as $certificate) {
                    $certificateSubject = $certificate->getSubject();

                    $endpointResponse->CertificateChain[] = array(
                        'Subject' => array(
                            'DN' => $certificate->getSubjectDn(),
                            'CN' => $certificateSubject['CN'],
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
                        'RootCa' => $certificate->isRootCa(),
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

/**
 * Match patterns from certificates like:
 * test.example.com
 * or
 * *.test.example.com
 *
 * @param string $hostname
 * @param string $pattern
 * @return bool
 */
function doesHostnameMatchPattern($hostname, $pattern)
{
    if ($hostname === $pattern) {
        return true; // Exact match
    }

    if (!substr($pattern, 0, 2)==='*.') {
        return false; // Not an exact match, not a wildcard pattern, so no match...
    }

    $pattern = substr($pattern, 2);

    if ($hostname === $pattern) {
        return true; // Exact match for pattern root, eg *.example.com also matches example.com
    }

    // Remove sub-domain
    $hostname = substr($hostname, strpos($hostname, '.') + 1);
    if ($hostname === $pattern) {
        return true;
    }

    return false;
}