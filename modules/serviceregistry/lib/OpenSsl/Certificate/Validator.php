<?php
/**
 *
 */

define('DAY_IN_SECONDS', 86400);

/**
 *
 */
class OpenSsl_Certificate_Validator
{
    const ERROR_PREFIX      = 'OpenSSL: ';
    const WARNING_PREFIX    = 'OpenSSL: ';

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

    protected $_certificateExpiryWarningDays = 30;

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

    protected $_trustedRootCertificateAuthorityFile;

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

    public function setTrustedRootCertificateAuthorityFile($file)
    {
        $this->_trustedRootCertificateAuthorityFile = $file;
        return $this;
    }

    public function setCertificateExpiryWarningDays($days)
    {
        $this->_certificateExpiryWarningDays = $days;
        return $this;
    }

    public function validate()
    {
        $this->_validateExpiry();
        $this->_validateWithOpenSsl();

        return $this->_isValid;
    }
    
    protected function _validateExpiry()
    {
        if ($this->_certificate->getValidFromUnixTime() > time()) {
            $this->_errors[] = "Entity certificate is not yet valid";
        }
        if ($this->_certificate->getValidUntilUnixTime() < time()) {
            $this->_errors[] = "Entity certificate has expired";
        }

        // Check if the certificate is still valid in x days, add a warning if it is not
        $entityMetadataMinimumValidityUnixTime = time() + ($this->_certificateExpiryWarningDays * DAY_IN_SECONDS);
        if (!$this->_certificate->getValidUntilUnixTime() > $entityMetadataMinimumValidityUnixTime) {
            $this->_warnings[] = "Entity certificate will expire in less than {$this->_certificateExpiryWarningDays} days";
        }
    }

    protected function _validateWithOpenSsl()
    {
        $command = new OpenSSL_Command_Verify();
        if (isset($this->_trustedRootCertificateAuthorityFile)) {
            $command->setCertificateAuthorityFile($this->_trustedRootCertificateAuthorityFile);
        }
        $command->execute($this->_certificate->getPem());
        $results = $command->getParsedResults();
        
        $this->_isValid = $results['valid'];

        foreach ($results['errors'] as $openSslErrorCode => $openSslError) {
            if ($openSslErrorCode === OPENSSL_X509_V_ERR_DEPTH_ZERO_SELF_SIGNED_CERT) {
                if ($this->_ignoreOnSelfSigned) {
                    continue;
                }
                else if ($this->_warnOnSelfSigned) {
                    $this->_warnings[] = self::WARNING_PREFIX . $openSslError['description'];
                    continue;
                }
            }

            $this->_errors[] = self::ERROR_PREFIX . $openSslError['description'];
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
