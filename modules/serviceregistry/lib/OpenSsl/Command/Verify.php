<?php
/**
 *
 */

/**
 * 
 */
class OpenSsl_Command_Verify extends Shell_Command_Abstract
{
    const COMMAND = 'openssl verify';

    const PURPOSE_SSL_CLIENT    = 'sslclient';
    const PURPOSE_SSL_SERVER    = 'sslserver';
    const PURPOSE_NSSL_SERVER   = 'nsslserver';
    const PURPOSE_SMIME_SIGN    = 'smimesign';
    const PURPOSE_SMIME_ENCRYPT = 'smimeencrypt';

    public function setCertificateAuthoritiesPath($directory)
    {
    }

    public function setCertificateAuthoritiesFile($file)
    {

    }

    public function setUntrustedCertificatesFile()
    {

    }

    public function doCrlCheck()
    {
    }

    public function doCrlCheckAll()
    {
    }

    public function setPurpose()
    {

    }

    public function setVerbose()
    {

    }

    public function doIssuerChecks()
    {

    }

    public function addPolicy()
    {

    }

    public function doPolicyCheck()
    {

    }

    public function setRequireExplicitPolicy()
    {

    }

    public function setInhibitAnyPolicy()
    {

    }

    public function setInhibitPolicyMapping()
    {

    }

    public function showPolicyDiagnostics()
    {

    }

    protected function _buildCommand()
    {
        return self::COMMAND;
    }
}