<?php
/**
 *
 */

/**
 *
 */ 
class OpenSsl_Certificate_Chain
{
    protected $_certificates;

    public function __construct(array $certificates = array())
    {
        $this->_certificates = $certificates;
    }

    public function addCertificate(OpenSsl_Certificate $certificate)
    {
        array_push($this->_certificates, $certificate);
    }

    public function getCertificates()
    {
        return $this->_certificates;
    }
}