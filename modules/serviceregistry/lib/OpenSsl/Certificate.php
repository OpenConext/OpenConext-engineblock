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

    public function getSubject($partName = '')
    {
        if ($partName!=='') {
            return $this->_parsed['subject'][$partName];
        }
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
            $dnParts []= "/$key=$value";
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
            $dnParts []= "/$key=$value";
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

    /**
     * @todo this is quick and dirty, I have no idea whether this will work with more complicated certs,
     *       then again, I have yet to see a cert that doesn't work with this...
     *
     * @return array
     */
    public function getCertificateAuthorityIssuerUrls()
    {
        if (!isset($this->_parsed['extensions']['authorityInfoAccess'])) {
            return array();
        }

        $matches = array();
        preg_match_all('/CA.+((https?|ftp):\/\/.+)/', $this->_parsed['extensions']['authorityInfoAccess'], $matches);

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

    public function getValidHostNames()
    {
        $names = $this->getSubjectAltNames();
        array_unshift($names, $this->getSubject('CN'));
        $names = array_keys(array_flip($names)); // Remove duplicates
        return $names;
    }
}