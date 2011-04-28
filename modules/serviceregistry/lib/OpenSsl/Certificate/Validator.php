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

    protected $_validationErrors = array();

    protected $_warnOnSelfSigned = false;

    protected $_isValid;

    public function __construct(OpenSsl_Certificate $certificate)
    {
        $this->_certificate = $certificate;
    }

    public function setWarnOnSelfSigned($mustWarn)
    {
        $this->_warnOnSelfSigned = $mustWarn;
        return $this;
    }

    public function validate()
    {
        $this->_validateCertificate();
        $this->_validateRevocationWithCrl();
        $this->_validateRevocationWithOcsp();

        return $this->_isValid;
    }

    protected function _validateCertificate()
    {
        $command = new OpenSSL_Command_Verify();
        $command->execute($this->_certificate->getPem());
    }

    protected function _validateRevocationWithCrl()
    {
    }

    protected function _validateRevocationWithOcsp()
    {
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
        
    }

    public function getErrors()
    {
        return $this->_validationErrors;
    }
}
