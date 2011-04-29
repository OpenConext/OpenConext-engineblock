<?php
/**
 *
 */

class OpenSsl_Certificate
{
    protected $_pemData;

    protected $_textData;

    protected $_trustedRootCertificateAuthority = false;

    public function __construct($pemData)
    {
        $this->_pemData = $pemData;
        $this->_parsed = openssl_x509_parse($pemData);

        if ($this->_parsed === false) {
            throw new OpenSsl_Certificate_Exception_NotAValidPem("Data '$pemData' is not a valid X.509 PEM certificate");
        }
    }

    public function setTrustedRootCertificateAuthority($isTrusted)
    {
        $this->_trustedRootCertificateAuthority = $isTrusted;
        return $this;
    }

    public function getTrustedRootCertificateAuthority()
    {
        return $this->_trustedRootCertificateAuthority;
    }

    public function getSubject()
    {
        return $this->_parsed['subject'];
    }

    public function getSubjectAltNames()
    {
        if (!isset($this->_parsed['extensions']['subjectAltName'])) {
            return array();
        }

        $names = explode(',', $this->_parsed['extensions']['subjectAltName']);
        foreach ($names as $key => &$name) {
            $name = trim($name);
            if (substr($name, 0, strlen('DNS:'))==='DNS:') {
                $name = substr($name, strlen('DNS:'));
            }
            else {
                unset($names[$key]);
            }
        }
        return $names;
    }

    public function getSubjectDn()
    {
        $dnParts = array();
        foreach ($this->_parsed['subject'] as $key => $value) {
            $dnParts []= "$key=$value";
        }
        return implode(',', $dnParts);
    }

    public function getIssuer()
    {
        return $this->_parsed['issuer'];
    }

    public function getIssuerDn()
    {
        $dnParts = array();
        foreach ($this->_parsed['issuer'] as $key => $value) {
            $dnParts []= "$key=$value";
        }
        return implode(',', $dnParts);
    }

    public function getPem()
    {
        return $this->_pemData;
    }

    public function isSelfSigned()
    {
        return ($this->getIssuerDn()===$this->getSubjectDn());
    }

    public function isCA()
    {
        return $this->isCertificateAuthority();
    }

    public function isCertificateAuthority()
    {
        return (
                isset($this->_parsed['extensions']['basicConstraints']) &&
                strstr($this->_parsed['extensions']['basicConstraints'], "CA:TRUE")
        );
    }

    public function getCertificateAuthorityIssuerUrls()
    {
        if (!isset($this->_parsed['extensions']['authorityInfoAccess'])) {
            return array();
        }

        $matches = array();
        preg_match_all('/CA.+((https?|ftp):\/\/.+)/', $this->_parsed['extensions']['authorityInfoAccess'], $matches);

        return $matches[1];
    }

    public function getOcspUrls()
    {
        if (!isset($this->_parsed['extensions']['authorityInfoAccess'])) {
            return array();
        }

        $matches = array();
        preg_match_all('/OCSP.+((https?|ftp):\/\/.+)/', $this->_parsed['extensions']['authorityInfoAccess'], $matches);

        return $matches[1];
    }

    public function getCrlDistributionPointUrls()
    {
        if (!isset($this->_parsed['extensions']['crlDistributionPoints'])) {
            return array();
        }

        $matches = array();
        preg_match_all('/URI:((https?|ftp):\/\/.+)/', $this->_parsed['extensions']['crlDistributionPoints'], $matches);

        return $matches[1];
    }

    public function getValidFromUnixTime()
    {
        return $this->_parsed['validFrom_time_t'];
    }

    public function getValidUntilUnixTime()
    {
        return $this->_parsed['validTo_time_t'];
    }
}