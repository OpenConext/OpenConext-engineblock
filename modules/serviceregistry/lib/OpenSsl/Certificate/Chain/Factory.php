<?php
/**
 *
 */

/**
 *
 */ 
class OpenSsl_Certificate_Chain_Factory 
{
    public static function create(OpenSsl_Certificate $certificate, OpenSsl_Certificate_Chain $chain = null)
    {
        if (!$chain) {
            $chain = new OpenSsl_Certificate_Chain();
        }
        $chain->addCertificate($certificate);

        // Root CA, add it and stop building
        if ($certificate->isRootCertificateAuthority()) {
            return $chain;
        }

        // Self signed?
        if ($certificate->isSelfSigned()) {
            return $chain;
        }

        /**
         * Get the certificate for the issuer of this certificate
         */
        $issuerUrls = $certificate->getCertificateAuthorityIssuerUrls();
        if (empty($issuerUrls)) {
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
