<?php
/**
 *
 */

/**
 *
 */
class OpenSsl_Certificate_Validator
{
    /**
     * @var sspmod_serviceregistry_Certificate
     */
    protected $_certificate;

    /**
     * @var array
     */
    protected $_warnings = array();

    /**
     * @var array
     */
    protected $_errors = array();

    /**
     * @var bool
     */
    protected $_ignoreOnSelfSigned = false;

    /**
     * @var bool
     */
    protected $_warnOnSelfSigned = false;

    /**
     * @var bool
     */
    protected $_isValid;

    public function __construct(OpenSsl_Certificate $certificate)
    {
        $this->_certificate = $certificate;
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

        return $this->_isValid;
    }

    protected function _validateWithOpenSsl()
    {
        $command = new OpenSSL_Command_Verify();
        $command->execute($this->_certificate->getPem());
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
        if (!isset($this->_isValid)) {
            $this->validate();
        }

        return $this->_isValid;
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
