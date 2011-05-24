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
 * the operation was successful.
 */
define("OPENSSL_X509_V_OK", 0);

/**
 * the issuer certificate of a looked up certificate could not be found. This
 * normally means the list of trusted certificates is not complete.
 */
define("OPENSSL_X509_V_ERR_UNABLE_TO_GET_ISSUER_CERT", 2);

/**
 * the CRL of a certificate could not be found.
 */
define("OPENSSL_X509_V_ERR_UNABLE_TO_GET_CRL", 3);

/**
 * the certificate signature could not be decrypted. This means that the
 * actual signature value could not be determined rather than it not matching
the expected value, this is only meaningful for RSA keys.
 */
define("OPENSSL_X509_V_ERR_UNABLE_TO_DECRYPT_CERT_SIGNATURE", 4);

/**
 * the CRL signature could not be decrypted: this means that the actual
 * signature value could not be determined rather than it not matching the
expected value. Unused.
 */
define("OPENSSL_X509_V_ERR_UNABLE_TO_DECRYPT_CRL_SIGNATURE", 5);

/**
 * the public key in the certificate SubjectPublicKeyInfo could not be read.
 */
define("OPENSSL_X509_V_ERR_UNABLE_TO_DECODE_ISSUER_PUBLIC_KEY", 6);

/**
 * the signature of the certificate is invalid.
 */
define("OPENSSL_X509_V_ERR_CERT_SIGNATURE_FAILURE", 7);

/**
 * the signature of the certificate is invalid.
 */
define("OPENSSL_X509_V_ERR_CRL_SIGNATURE_FAILURE", 8);

/**
 * the certificate is not yet valid: the notBefore date is after the current
 * time.
 */
define("OPENSSL_X509_V_ERR_CERT_NOT_YET_VALID", 9);

/**
 * the certificate has expired: that is the notAfter date is before the
 * current time.
 */
define("OPENSSL_X509_V_ERR_CERT_HAS_EXPIRED", 10);

/**
 * the CRL is not yet valid.
 */
define("OPENSSL_X509_V_ERR_CRL_NOT_YET_VALID", 11);

/**
 * the CRL has expired.
 */
define("OPENSSL_X509_V_ERR_CRL_HAS_EXPIRED", 12);

/**
 * the certificate notBefore field contains an invalid time.
 */
define("OPENSSL_X509_V_ERR_ERROR_IN_CERT_NOT_BEFORE_FIELD", 13);

/**
 * the certificate notAfter field contains an invalid time.
 */
define("OPENSSL_X509_V_ERR_ERROR_IN_CERT_NOT_AFTER_FIELD", 14);

/**
 * the CRL lastUpdate field contains an invalid time.
 */
define("OPENSSL_X509_V_ERR_ERROR_IN_CRL_LAST_UPDATE_FIELD", 15);

/**
 * the CRL nextUpdate field contains an invalid time.
 */
define("OPENSSL_X509_V_ERR_ERROR_IN_CRL_NEXT_UPDATE_FIELD", 16);

/**
 * an error occurred trying to allocate memory. This should never happen.
 */
define("OPENSSL_X509_V_ERR_OUT_OF_MEM", 17);

/**
 * the passed certificate is self signed and the same certificate cannot be
 * found in the list of trusted certificates.
 */
define("OPENSSL_X509_V_ERR_DEPTH_ZERO_SELF_SIGNED_CERT", 18);

/**
 * the certificate chain could be built up using the untrusted certificates
 * but the root could not be found locally.
 */
define("OPENSSL_X509_V_ERR_SELF_SIGNED_CERT_IN_CHAIN", 19);

/**
 * the issuer certificate could not be found: this occurs if the issuer
 * certificate of an untrusted certificate cannot be found.
 */
define("OPENSSL_X509_V_ERR_UNABLE_TO_GET_ISSUER_CERT_LOCALLY", 20);

/**
 * no signatures could be verified because the chain contains only one
 * certificate and it is not self signed.
 */
define("OPENSSL_X509_V_ERR_UNABLE_TO_VERIFY_LEAF_SIGNATURE", 21);

/**
 * the certificate chain length is greater than the supplied maximum depth.
 * Unused.
 */
define("OPENSSL_X509_V_ERR_CERT_CHAIN_TOO_LONG", 22);

/**
 * the certificate has been revoked.
 */
define("OPENSSL_X509_V_ERR_CERT_REVOKED", 23);

/**
 * a CA certificate is invalid. Either it is not a CA or its extensions are
 * not consistent with the supplied purpose.
 */
define("OPENSSL_X509_V_ERR_INVALID_CA", 24);

/**
 * the basicConstraints pathlength parameter has been exceeded.
 */
define("OPENSSL_X509_V_ERR_PATH_LENGTH_EXCEEDED", 25);

/**
 * the supplied certificate cannot be used for the specified purpose.
 */
define("OPENSSL_X509_V_ERR_INVALID_PURPOSE", 26);

/**
 * the root CA is not marked as trusted for the specified purpose.
 */
define("OPENSSL_X509_V_ERR_CERT_UNTRUSTED", 27);

/**
 * the root CA is marked to reject the specified purpose.
 */
define("OPENSSL_X509_V_ERR_CERT_REJECTED", 28);

/**
 * the current candidate issuer certificate was rejected because its subject
 * name did not match the issuer name of the current certificate. Only
displayed when the <strong>-issuer_checks</strong> option is set.
 */
define("OPENSSL_X509_V_ERR_SUBJECT_ISSUER_MISMATCH", 29);

/**
 * the current candidate issuer certificate was rejected because its subject
 * key identifier was present and did not match the authority key identifier
current certificate. Only displayed when the <strong>-issuer_checks</strong> option is set.
 */
define("OPENSSL_X509_V_ERR_AKID_SKID_MISMATCH", 30);

/**
 * the current candidate issuer certificate was rejected because its issuer
 * name and serial number was present and did not match the authority key
identifier of the current certificate. Only displayed when the <strong>-issuer_checks</strong> option is set.
 */
define("OPENSSL_X509_V_ERR_AKID_ISSUER_SERIAL_MISMATCH", 31);

/**
 * the current candidate issuer certificate was rejected because its keyUsage
 * extension does not permit certificate signing.
 */
define("OPENSSL_X509_V_ERR_KEYUSAGE_NO_CERTSIGN", 32);

/**
 * an application specific error. Unused.
 */
define("OPENSSL_X509_V_ERR_APPLICATION_VERIFICATION", 50);

/**
 * 
 */
class OpenSsl_Command_Verify extends Shell_Command_Abstract
{
    const COMMAND = 'openssl verify';

    /**
     * From:
     * @url http://www.openssl.org/docs/apps/verify.html#DIAGNOSTICS
     *
     * @var array
     */
    protected $_ERROR_CODE_LOOKUP = array(
        0 => array(
            'name' => 'X509_V_OK',
            'description' => 'The operation was successful.',
        ),
        2 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_GET_ISSUER_CERT',
            'description' => 'The issuer certificate of a looked up certificate could not be found. This normally means the list of trusted certificates is not complete.',
        ),
        3 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_GET_CRL',
            'description' => 'The CRL of a certificate could not be found.',
        ),
        4 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_DECRYPT_CERT_SIGNATURE',
            'description' => 'The certificate signature could not be decrypted. This means that the actual signature value could not be determined rather than it not matching the expected value, this is only meaningful for RSA keys.',
        ),
        5 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_DECRYPT_CRL_SIGNATURE',
            'description' => 'The CRL signature could not be decrypted: this means that the actual signature value could not be determined rather than it not matching the expected value. Unused.',
        ),
        6 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_DECODE_ISSUER_PUBLIC_KEY',
            'description' => 'The public key in the certificate SubjectPublicKeyInfo could not be read.',
        ),
        7 => array(
            'name' => 'X509_V_ERR_CERT_SIGNATURE_FAILURE',
            'description' => 'The signature of the certificate is invalid.',
        ),
        8 => array(
            'name' => 'X509_V_ERR_CRL_SIGNATURE_FAILURE',
            'description' => 'The signature of the certificate is invalid.',
        ),
        9 => array(
            'name' => 'X509_V_ERR_CERT_NOT_YET_VALID',
            'description' => 'The certificate is not yet valid: the notBefore date is after the current time.',
        ),
        10 => array(
            'name' => 'X509_V_ERR_CERT_HAS_EXPIRED',
            'description' => 'The certificate has expired: that is the notAfter date is before the current time.',
        ),
        11 => array(
            'name' => 'X509_V_ERR_CRL_NOT_YET_VALID',
            'description' => 'The CRL is not yet valid.',
        ),
        12 => array(
            'name' => 'X509_V_ERR_CRL_HAS_EXPIRED',
            'description' => 'The CRL has expired.',
        ),
        13 => array(
            'name' => 'X509_V_ERR_ERROR_IN_CERT_NOT_BEFORE_FIELD',
            'description' => 'The certificate notBefore field contains an invalid time.',
        ),
        14 => array(
            'name' => 'X509_V_ERR_ERROR_IN_CERT_NOT_AFTER_FIELD',
            'description' => 'The certificate notAfter field contains an invalid time.',
        ),
        15 => array(
            'name' => 'X509_V_ERR_ERROR_IN_CRL_LAST_UPDATE_FIELD',
            'description' => 'The CRL lastUpdate field contains an invalid time.',
        ),
        16 => array(
            'name' => 'X509_V_ERR_ERROR_IN_CRL_NEXT_UPDATE_FIELD',
            'description' => 'The CRL nextUpdate field contains an invalid time.',
        ),
        17 => array(
            'name' => 'X509_V_ERR_OUT_OF_MEM',
            'description'=> 'an error occurred trying to allocate memory. This should never happen.',
        ),
        18 => array(
            'name' => 'X509_V_ERR_DEPTH_ZERO_SELF_SIGNED_CERT',
            'description' => 'The passed certificate is self signed and the same certificate cannot be found in the list of trusted certificates.',
        ),
        19 => array(
            'name' => 'X509_V_ERR_SELF_SIGNED_CERT_IN_CHAIN',
            'description' => 'The certificate chain could be built up using the untrusted certificates but the root could not be found locally.',
        ),
        20 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_GET_ISSUER_CERT_LOCALLY',
            'description' => 'The issuer certificate could not be found: this occurs if the issuer certificate of an untrusted certificate cannot be found.',
        ),
        21 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_VERIFY_LEAF_SIGNATURE',
            'description'=> 'no signatures could be verified because the chain contains only one certificate and it is not self signed.',
        ),
        22 => array(
            'name' => 'X509_V_ERR_CERT_CHAIN_TOO_LONG',
            'description' => 'The certificate chain length is greater than the supplied maximum depth. Unused.',
        ),
        23 => array(
            'name' => 'X509_V_ERR_CERT_REVOKED',
            'description' => 'The certificate has been revoked.',
        ),
        24 => array(
            'name' => 'X509_V_ERR_INVALID_CA',
            'description'=> 'a CA certificate is invalid. Either it is not a CA or its extensions are not consistent with the supplied purpose.',
        ),
        25 => array(
            'name' => 'X509_V_ERR_PATH_LENGTH_EXCEEDED',
            'description' => 'The basicConstraints pathlength parameter has been exceeded.',
        ),
        26 => array(
            'name' => 'X509_V_ERR_INVALID_PURPOSE',
            'description' => 'The supplied certificate cannot be used for the specified purpose.',
        ),
        27 => array(
            'name' => 'X509_V_ERR_CERT_UNTRUSTED',
            'description' => 'The root CA is not marked as trusted for the specified purpose.',
        ),
        28 => array(
            'name' => 'X509_V_ERR_CERT_REJECTED',
            'description' => 'The root CA is marked to reject the specified purpose.',
        ),
        29 => array(
            'name' => 'X509_V_ERR_SUBJECT_ISSUER_MISMATCH',
            'description' => 'The current candidate issuer certificate was rejected because its subject name did not match the issuer name of the current certificate. Only
        displayed when the -issuer_checks option is set.',
        ),
        30 => array(
            'name' => 'X509_V_ERR_AKID_SKID_MISMATCH',
            'description' => 'The current candidate issuer certificate was rejected because its subject key identifier was present and did not match the authority key identifier current certificate. Only displayed when the -issuer_checks option is set.',
        ),
        31 => array(
            'name' => 'X509_V_ERR_AKID_ISSUER_SERIAL_MISMATCH',
            'description' => 'The current candidate issuer certificate was rejected because its issuer name and serial number was present and did not match the authority key identifier of the current certificate. Only displayed when the -issuer_checks option is set.',
        ),
        32 => array(
            'name' => 'X509_V_ERR_KEYUSAGE_NO_CERTSIGN',
            'description' => 'The current candidate issuer certificate was rejected because its keyUsage extension does not permit certificate signing.',
        ),
        50 => array(
            'name' => 'X509_V_ERR_APPLICATION_VERIFICATION',
            'description'=> 'an application specific error. Unused.',
        ),
    );

    protected $_certificateAuthorityFile;

    public function setCertificateAuthorityFile($file)
    {
        $this->_certificateAuthorityFile = $file;
        return $this;
    }

    protected function _buildCommand()
    {
        return self::COMMAND;
    }

    public function getParsedResults()
    {
        $output = $this->_output;
        if (strpos($this->_output, 'stdin: ')===0) {
            $output = trim(substr($this->_output, strlen('stdin: ')));
        }
        $outputLines = explode(PHP_EOL, $output);

        $valid = false;
        if ($outputLines[count($outputLines)-1]==="OK") {
            $valid = true;
            array_pop($outputLines);
        }

        $errors = array();
        while (!empty($outputLines)) {
            $subjectDn = array_shift($outputLines);
            $errorLine = array_shift($outputLines);

            $matches = array();
            preg_match('|error (\d+) at (\d+) depth|', $errorLine, $matches);
            if (!isset($matches[1])) {
                throw new Exception("Expecting 'error NUM at NUM depth', got: '$errorLine'");
            }

            $errorCode = (int)$matches[1];
            if (!isset($this->_ERROR_CODE_LOOKUP[$errorCode])) {
                throw new Exception("Unknown error code '$errorCode' from '$errorLine'?!");
            }

            $errors[$errorCode] = $this->_ERROR_CODE_LOOKUP[$errorCode];
        }
        return array(
            'valid' => $valid,
            'errors' => $errors,
        );
    }
}