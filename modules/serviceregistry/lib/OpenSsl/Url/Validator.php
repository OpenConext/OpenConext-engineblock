<?php
/**
 *
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
