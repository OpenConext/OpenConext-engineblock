<?php
/**
 *
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
class OpenSsl_Certificate_Chain_Validator
{
    const X509_V_OK = 0;
    const X509_V_ERR_UNABLE_TO_GET_ISSUER_CERT = 1;
    const X509_V_ERR_UNABLE_TO_GET_CRL = 1;

    /**
     * From:
     * @url http://www.openssl.org/docs/apps/verify.html#DIAGNOSTICS
     *
     * @var array
     */
    protected $_ERROR_CODE_LOOKUP = array(
        0 => array(
            'name' => 'X509_V_OK',
            'description'=> 'the operation was successful.',
        ),
        2 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_GET_ISSUER_CERT',
            'description'=> 'the issuer certificate of a looked up certificate could not be found. This
        normally means the list of trusted certificates is not complete.',
        ),
        3 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_GET_CRL',
            'description'=> 'the CRL of a certificate could not be found.',
        ),
        4 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_DECRYPT_CERT_SIGNATURE',
            'description'=> 'the certificate signature could not be decrypted. This means that the
        actual signature value could not be determined rather than it not matching
        the expected value, this is only meaningful for RSA keys.',
        ),
        5 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_DECRYPT_CRL_SIGNATURE',
            'description'=> 'the CRL signature could not be decrypted: this means that the actual
        signature value could not be determined rather than it not matching the
        expected value. Unused.',
        ),
        6 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_DECODE_ISSUER_PUBLIC_KEY',
            'description'=> 'the public key in the certificate SubjectPublicKeyInfo could not be read.',
        ),
        7 => array(
            'name' => 'X509_V_ERR_CERT_SIGNATURE_FAILURE',
            'description'=> 'the signature of the certificate is invalid.',
        ),
        8 => array(
            'name' => 'X509_V_ERR_CRL_SIGNATURE_FAILURE',
            'description'=> 'the signature of the certificate is invalid.',
        ),
        9 => array(
            'name' => 'X509_V_ERR_CERT_NOT_YET_VALID',
            'description'=> 'the certificate is not yet valid: the notBefore date is after the current
        time.',
        ),
        10 => array(
            'name' => 'X509_V_ERR_CERT_HAS_EXPIRED',
            'description'=> 'the certificate has expired: that is the notAfter date is before the
        current time.',
        ),
        11 => array(
            'name' => 'X509_V_ERR_CRL_NOT_YET_VALID',
            'description'=> 'the CRL is not yet valid.',
        ),
        12 => array(
            'name' => 'X509_V_ERR_CRL_HAS_EXPIRED',
            'description'=> 'the CRL has expired.',
        ),
        13 => array(
            'name' => 'X509_V_ERR_ERROR_IN_CERT_NOT_BEFORE_FIELD',
            'description'=> 'the certificate notBefore field contains an invalid time.',
        ),
        14 => array(
            'name' => 'X509_V_ERR_ERROR_IN_CERT_NOT_AFTER_FIELD',
            'description'=> 'the certificate notAfter field contains an invalid time.',
        ),
        15 => array(
            'name' => 'X509_V_ERR_ERROR_IN_CRL_LAST_UPDATE_FIELD',
            'description'=> 'the CRL lastUpdate field contains an invalid time.',
        ),
        16 => array(
            'name' => 'X509_V_ERR_ERROR_IN_CRL_NEXT_UPDATE_FIELD',
            'description'=> 'the CRL nextUpdate field contains an invalid time.',
        ),
        17 => array(
            'name' => 'X509_V_ERR_OUT_OF_MEM',
            'description'=> 'an error occurred trying to allocate memory. This should never happen.',
        ),
        18 => array(
            'name' => 'X509_V_ERR_DEPTH_ZERO_SELF_SIGNED_CERT',
            'description'=> 'the passed certificate is self signed and the same certificate cannot be
        found in the list of trusted certificates.',
        ),
        19 => array(
            'name' => 'X509_V_ERR_SELF_SIGNED_CERT_IN_CHAIN',
            'description'=> 'the certificate chain could be built up using the untrusted certificates
        but the root could not be found locally.',
        ),
        20 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_GET_ISSUER_CERT_LOCALLY',
            'description'=> 'the issuer certificate could not be found: this occurs if the issuer
        certificate of an untrusted certificate cannot be found.',
        ),
        21 => array(
            'name' => 'X509_V_ERR_UNABLE_TO_VERIFY_LEAF_SIGNATURE',
            'description'=> 'no signatures could be verified because the chain contains only one
        certificate and it is not self signed.',
        ),
        22 => array(
            'name' => 'X509_V_ERR_CERT_CHAIN_TOO_LONG',
            'description'=> 'the certificate chain length is greater than the supplied maximum depth.
        Unused.',
        ),
        23 => array(
            'name' => 'X509_V_ERR_CERT_REVOKED',
            'description'=> 'the certificate has been revoked.',
        ),
        24 => array(
            'name' => 'X509_V_ERR_INVALID_CA',
            'description'=> 'a CA certificate is invalid. Either it is not a CA or its extensions are
        not consistent with the supplied purpose.',
        ),
        25 => array(
            'name' => 'X509_V_ERR_PATH_LENGTH_EXCEEDED',
            'description'=> 'the basicConstraints pathlength parameter has been exceeded.',
        ),
        26 => array(
            'name' => 'X509_V_ERR_INVALID_PURPOSE',
            'description'=> 'the supplied certificate cannot be used for the specified purpose.',
        ),
        27 => array(
            'name' => 'X509_V_ERR_CERT_UNTRUSTED',
            'description'=> 'the root CA is not marked as trusted for the specified purpose.',
        ),
        28 => array(
            'name' => 'X509_V_ERR_CERT_REJECTED',
            'description'=> 'the root CA is marked to reject the specified purpose.',
        ),
        29 => array(
            'name' => 'X509_V_ERR_SUBJECT_ISSUER_MISMATCH',
            'description'=> 'the current candidate issuer certificate was rejected because its subject
        name did not match the issuer name of the current certificate. Only
        displayed when the -issuer_checks option is set.',
        ),
        30 => array(
            'name' => 'X509_V_ERR_AKID_SKID_MISMATCH',
            'description'=> 'the current candidate issuer certificate was rejected because its subject
        key identifier was present and did not match the authority key identifier
        current certificate. Only displayed when the -issuer_checks option is set.',
        ),
        31 => array(
            'name' => 'X509_V_ERR_AKID_ISSUER_SERIAL_MISMATCH',
            'description'=> 'the current candidate issuer certificate was rejected because its issuer
        name and serial number was present and did not match the authority key
        identifier of the current certificate. Only displayed when the -issuer_checks option is set.',
        ),
        32 => array(
            'name' => 'X509_V_ERR_KEYUSAGE_NO_CERTSIGN',
            'description'=> 'the current candidate issuer certificate was rejected because its keyUsage
        extension does not permit certificate signing.',
        ),
        50 => array(
            'name' => 'X509_V_ERR_APPLICATION_VERIFICATION',
            'description'=> 'an application specific error. Unused.',
        ),
    );

    protected $_chain;

    protected $_ignoreOnSelfSigned = false;

    protected $_warnOnSelfSigned = false;

    protected $_valid;

    protected $_warnings = array();

    protected $_errors = array();

    public function __construct(OpenSsl_Certificate_Chain $chain)
    {
        $this->_chain = $chain;
    }

    public function setIgnoreSelfSigned($mustIgnore)
    {
        $this->_ignoreOnSelfSigned = $mustIgnore;
        return $this;
    }

    public function setWarnOnSelfSigned($mustWarn)
    {
        $this->_warnOnSelfSigned = $mustWarn;
        return $this;
    }

    public function validate()
    {
        $chainPems = '';
        $chainCertificates = $this->_chain->getCertificates();
        
        foreach ($chainCertificates as $certificate) {
            $chainPems = $certificate->getPem() . $chainPems;
        }

        $command = new OpenSSL_Command_Verify();
        $output = $command->execute($chainPems)->getOutput();

        if (strpos($output, 'stdin: ')===0) {
            $output = trim(substr($output, strlen('stdin: ')));
        }
        $outputLines = explode(PHP_EOL, $output);

        $resultCode = array_pop($outputLines);
        $this->_valid = ($resultCode==="OK");

        while (!empty($outputLines)) {
            $subjectDn = array_shift($outputLines);
            $errorLine = array_shift($outputLines);

            $matches = array();
            preg_match('|error (\d+) at (\d+) depth|', $errorLine, $matches);
            if (!isset($matches[1])) {
                // @todo
            }

            $errorCode = (int)$matches[1];
            if (!isset($this->_ERROR_CODE_LOOKUP[$errorCode])) {
                // @todo
            }

            if ($this->_ignoreOnSelfSigned && $errorCode === OPENSSL_X509_V_ERR_DEPTH_ZERO_SELF_SIGNED_CERT) {
                continue;
            }

            $this->_errors[] = "OpenSSL: " . $this->_ERROR_CODE_LOOKUP[$errorCode]['description'];
        }
    }

    public function isValid()
    {
        return $this->_valid;
    }

    public function getWarnings()
    {
        return $this->_warnings;
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}
