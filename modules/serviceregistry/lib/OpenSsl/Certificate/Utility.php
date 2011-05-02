<?php
/**
 *
 */

/**
 *
 */ 
class OpenSsl_Certificate_Utility
{
    public static function getCertificatesFromText($text)
    {
        $inputLines = explode(PHP_EOL, $text);
        $certificatesFound = array();
        $recording = false;
        $certificate = "";
        foreach ($inputLines as $inputLine) {
            if (trim($inputLine) === "-----BEGIN CERTIFICATE-----") {
                $certificate = "";

                $recording = true;
            }

            if ($recording) {
                $certificate .= $inputLine . PHP_EOL;
            }

            if (trim($inputLine) === "-----END CERTIFICATE-----") {
                $certificate = new OpenSsl_Certificate($certificate);
                $certificatesFound[$certificate->getSubjectDN()] = $certificate;
                $recording = false;
            }
        }
        return $certificatesFound;
    }
}
