<?php
/**
 *
 */

/**
 *
 */ 
class OpenSsl_Certificate_Chain_Validator
{
    protected $_chain;

    protected $_ignoreOnSelfSigned = false;

    protected $_warnOnSelfSigned = false;

    protected $_valid;

    protected $_warnings = array();

    protected $_errors = array();

    public function __construct(OpenSsl_Certificate_Chain $chain)
    {
        $this->_chain = $chain;
    }

    public function setIgnoreSelfSigned($mustIgnore)
    {
        $this->_ignoreOnSelfSigned = $mustIgnore;
        return $this;
    }

    public function setWarnOnSelfSigned($mustWarn)
    {
        $this->_warnOnSelfSigned = $mustWarn;
        return $this;
    }

    public function validate()
    {
        $this->_validateWithOpenSsl();
    }

    protected function _validateWithOpenSsl()
    {
        $chainPems = '';
        $chainCertificates = $this->_chain->getCertificates();

        foreach ($chainCertificates as $certificate) {
            $chainPems = $certificate->getPem() . PHP_EOL . $chainPems;
        }

        $command = new OpenSSL_Command_Verify();
        $command->execute($chainPems)->getOutput();

        $results = $command->getParsedResults();
        
        $this->_isValid = $results['valid'];
        foreach ($results['errors'] as $openSslErrorCode => $openSslError) {
            if ($openSslErrorCode === OPENSSL_X509_V_ERR_DEPTH_ZERO_SELF_SIGNED_CERT) {
                if ($this->_ignoreOnSelfSigned) {
                    continue;
                }
                else if ($this->_warnOnSelfSigned) {
                    $this->_warnings[] = 'OpenSSL: ' . $openSslError['description'];
                    continue;
                }
            }

            $this->_errors[] = 'OpenSSL: ' . $openSslError['description'];
        }
    }

    public function isValid()
    {
        return $this->_valid;
    }

    public function getWarnings()
    {
        return $this->_warnings;
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}
