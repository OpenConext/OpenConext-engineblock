<?php
/**
 *
 */

/**
 *
 */ 
class Janus_CertificateFactory
{
    public static function create($certData)
    {
        $pem = trim($certData);
        if ($pem==="") {
            throw new Janus_Exception_NoCertData();
        }

        // Strip out possible newlines
        $pem = str_replace("\n", "", $pem);
        $pem = str_replace("\r", "", $pem);

        // Split it into chunks of 64 characters
        $pem = chunk_split($pem, 64, "\r\n");

        // remove the last \n character
        $pem = substr($pem, 0, -1);

        // Add header and footer
        if(strpos($pem, '-----BEGIN CERTIFICATE-----') === FALSE) {
            $pem = '-----BEGIN CERTIFICATE-----' . PHP_EOL . $pem . PHP_EOL . '-----END CERTIFICATE-----' . PHP_EOL;
        }
        return new OpenSsl_Certificate($pem);
    }
}
