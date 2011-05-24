<?php
/**
 * SURFconext Service Registry
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext Service Registry
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
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

        $fileContents = file_get_contents($filePath);
        $certificatesFound = OpenSsl_Certificate_Utility::getCertificatesFromText($fileContents);

        self::setRootCertificates($certificatesFound);
    }

    public static function setRootCertificates(array $list)
    {
        self::$s_rootCertificates = $list;
    }

    public static function createFromCertificates(array $certificates)
    {
        $chain = new OpenSsl_Certificate_Chain();
        foreach ($certificates as $certificate) {
            // Root CA?
            if (isset(self::$s_rootCertificates[$certificate->getIssuerDn()])) {
                $certificate->setTrustedRootCertificateAuthority(true);
            }

            $chain->addCertificate($certificate);
        }
        return $chain;
    }

    public static function createFromPems(array $pems)
    {
        $chain = new OpenSsl_Certificate_Chain();
        foreach ($pems as $pem) {
            $certificate = new OpenSsl_Certificate($pem);

            // Root CA?
            if (isset(self::$s_rootCertificates[$certificate->getIssuerDn()])) {
                $certificate->setTrustedRootCertificateAuthority(true);
            }

            $chain->addCertificate($certificate);
        }
        return $chain;
    }

    public static function createFromCertificateIssuerUrl(OpenSsl_Certificate $certificate, OpenSsl_Certificate_Chain $chain = null)
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
            return self::createFromCertificateIssuerUrl($issuerCertificate, $chain);
        }

        throw new OpenSsl_Certificate_Chain_Exception_BuildingFailedIssuerUrlNotFound(
            "Unable to get issuer certificate?"
        );
    }
}
