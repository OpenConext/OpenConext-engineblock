<?php
/**
 *
 */

/**
 *
 */ 
class OpenSsl_Certificate_Chain_Factory 
{
    protected static $s_rootCertificates;

    public static function loadRootCertificatesFromFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("Unable to load Root certificates, file '$filePath' does not exist");
        }

        $inputLines = file($filePath);

        $certificatesFound = array();
        $recording = false;
        foreach ($inputLines as $inputLine) {
            if (trim($inputLine) === "-----BEGIN CERTIFICATE-----") {
                $certificate = "";

                $recording = true;
            }

            if ($recording) {
                $certificate .= $inputLine;
            }

            if (trim($inputLine) === "-----END CERTIFICATE-----") {
                $certificate = new OpenSsl_Certificate($certificate);
                $certificate->setTrustedRootCertificateAuthority(true);
                $certificatesFound[$certificate->getSubjectDN()] = $certificate;

                $recording = false;
            }
        }
        self::setRootCertificates($certificatesFound);
    }

    public static function setRootCertificates(array $list)
    {
        self::$s_rootCertificates = $list;
    }

    public static function create(OpenSsl_Certificate $certificate, OpenSsl_Certificate_Chain $chain = null)
    {
        if (!$chain) {
            $chain = new OpenSsl_Certificate_Chain();
        }
        
        $chain->addCertificate($certificate);

        // Self signed?
        if ($certificate->isSelfSigned()) {
            return $chain;
        }

        // Root CA, add it and stop building
        if (isset(self::$s_rootCertificates[$certificate->getIssuerDn()])) {
            $chain->addCertificate(self::$s_rootCertificates[$certificate->getIssuerDn()]);
            return $chain;
        }

        /**
         * Get the certificate for the issuer of this certificate
         */
        $issuerUrls = $certificate->getCertificateAuthorityIssuerUrls();
        if (empty($issuerUrls)) {
            var_dump($chain);
            throw new OpenSsl_Certificate_Chain_Exception_BuildingFailedIssuerUrlNotFound("Unable to get issuer certificate?");
        }

        foreach ($issuerUrls as $issuerUrl) {
            $issuerCertificate = file_get_contents($issuerUrl);
            if (!$issuerCertificate || trim($issuerCertificate) === "") {
                // @todo Unable to get the issuer certificate... log this somewhere?
                //       For now we silently just use the next issuer url
                continue;
            }

            // Not a PEM certificate? Probably a DER certificate, transform
            if (strpos($issuerCertificate, '-----BEGIN CERTIFICATE-----') === false) {
                $x509Command = new OpenSsl_Command_X509();
                $x509Command->setInForm(OpenSsl_Command_X509::FORM_DER);
                $x509Command->execute($issuerCertificate)->getOutput();
                $issuerCertificate = $x509Command->getOutput();
            }

            $issuerCertificate = new OpenSsl_Certificate($issuerCertificate);
            return self::create($issuerCertificate, $chain);
        }

        throw new OpenSsl_Certificate_Chain_Exception_BuildingFailedIssuerUrlNotFound(
            "Unable to get issuer certificate?"
        );
    }
}
