<?php

/**
 * SimpleSamlPHP class we have to use due to having to use the correct RequestUri as
 * determined by Symfony.
 */

use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Constants;
use SAML2\EncryptedAssertion;
use SAML2\LogoutRequest;
use SAML2\LogoutResponse;
use SAML2\Message;
use SAML2\Response;
use SAML2\SignedElement;
use SAML2\StatusResponse;
use SAML2\XML\ds\KeyInfo;
use SAML2\XML\ds\X509Certificate;
use SAML2\XML\ds\X509Data;
use SimpleSAML\Logger;
use SimpleSAML\Utils\Crypto;
use SimpleSAML\Utils\HTTP;

/**
 * Common code for building SAML 2 messages based on the
 * available metadata.
 *
 * @package simpleSAMLphp
 */
class EngineBlock_Ssp_sspmod_saml_SymfonyRequestUriMessage extends sspmod_saml_Message
{
    /**
     * Retrieve the decryption keys from metadata.
     *
     * @param SimpleSAML_Configuration $srcMetadata The metadata of the sender (IdP).
     * @param SimpleSAML_Configuration $dstMetadata The metadata of the recipient (SP).
     * @return array  Array of decryption keys.
     */
    public static function getDecryptionKeys(
        SimpleSAML_Configuration $srcMetadata,
        SimpleSAML_Configuration $dstMetadata
    ) {

        $sharedKey = $srcMetadata->getString('sharedkey', null);
        if ($sharedKey !== null) {
            $key = new XMLSecurityKey(XMLSecurityKey::AES128_CBC);
            $key->loadKey($sharedKey);

            return array($key);
        }

        $keys = array();

        /* Load the new private key if it exists. */
        $keyArray = Crypto::loadPrivateKey($dstMetadata, false, 'new_');
        if ($keyArray !== null) {
            assert('isset($keyArray["PEM"])');

            $key = new XMLSecurityKey(XMLSecurityKey::RSA_1_5, array('type' => 'private'));
            if (array_key_exists('password', $keyArray)) {
                $key->passphrase = $keyArray['password'];
            }
            $key->loadKey($keyArray['PEM']);
            $keys[] = $key;
        }

        /* Find the existing private key. */
        $keyArray = Crypto::loadPrivateKey($dstMetadata, true);
        assert('isset($keyArray["PEM"])');

        $key = new XMLSecurityKey(XMLSecurityKey::RSA_1_5, array('type' => 'private'));
        if (array_key_exists('password', $keyArray)) {
            $key->passphrase = $keyArray['password'];
        }
        $key->loadKey($keyArray['PEM']);
        $keys[] = $key;

        return $keys;
    }

    /**
     * Retrieve blacklisted algorithms.
     *
     * Remote configuration overrides local configuration.
     *
     * @param SimpleSAML_Configuration $srcMetadata The metadata of the sender.
     * @param SimpleSAML_Configuration $dstMetadata The metadata of the recipient.
     * @return array  Array of blacklisted algorithms.
     */
    public static function getBlacklistedAlgorithms(
        SimpleSAML_Configuration $srcMetadata,
        SimpleSAML_Configuration $dstMetadata
    ) {

        $blacklist = $srcMetadata->getArray('encryption.blacklisted-algorithms', null);
        if ($blacklist === null) {
            $blacklist = $dstMetadata->getArray('encryption.blacklisted-algorithms', array(XMLSecurityKey::RSA_1_5));
        }

        return $blacklist;
    }

    /**
     * Decrypt an assertion.
     *
     * This function takes in a Assertion and decrypts it if it is encrypted.
     * If it is unencrypted, and encryption is enabled in the metadata, an exception
     * will be throws.
     *
     * @param SimpleSAML_Configuration                 $srcMetadata The metadata of the sender (IdP).
     * @param SimpleSAML_Configuration                 $dstMetadata The metadata of the recipient (SP).
     * @param Assertion|EncryptedAssertion $assertion   The assertion we are decrypting.
     * @return Assertion  The assertion.
     */
    private static function decryptAssertion(
        SimpleSAML_Configuration $srcMetadata,
        SimpleSAML_Configuration $dstMetadata,
        $assertion
    ) {
        assert('$assertion instanceof \SAML2\Assertion || $assertion instanceof \SAML2\EncryptedAssertion');

        if ($assertion instanceof Assertion) {
            $encryptAssertion = $srcMetadata->getBoolean('assertion.encryption', null);
            if ($encryptAssertion === null) {
                $encryptAssertion = $dstMetadata->getBoolean('assertion.encryption', false);
            }
            if ($encryptAssertion) {
                /* The assertion was unencrypted, but we have encryption enabled. */
                throw new Exception('Received unencrypted assertion, but encryption was enabled.');
            }

            return $assertion;
        }

        try {
            $keys = self::getDecryptionKeys($srcMetadata, $dstMetadata);
        } catch (Exception $e) {
            throw new SimpleSAML_Error_Exception('Error decrypting assertion: ' . $e->getMessage());
        }

        $blacklist = self::getBlacklistedAlgorithms($srcMetadata, $dstMetadata);

        $lastException = null;
        foreach ($keys as $i => $key) {
            try {
                $ret = $assertion->getAssertion($key, $blacklist);
                Logger::debug('Decryption with key #' . $i . ' succeeded.');

                return $ret;
            } catch (Exception $e) {
                Logger::debug('Decryption with key #' . $i . ' failed with exception: ' . $e->getMessage());
                $lastException = $e;
            }
        }
        throw $lastException;
    }

    /**
     * Retrieve the status code of a response as a sspmod_saml_Error.
     *
     * @param StatusResponse $response The response.
     * @return sspmod_saml_Error  The error.
     */
    public static function getResponseError(StatusResponse $response)
    {

        $status = $response->getStatus();

        return new sspmod_saml_Error($status['Code'], $status['SubCode'], $status['Message']);
    }

    /**
     * Process a response message.
     *
     * If the response is an error response, we will throw a sspmod_saml_Error
     * exception with the error.
     *
     * @param SimpleSAML_Configuration $spMetadata  The metadata of the service provider.
     * @param SimpleSAML_Configuration $idpMetadata The metadata of the identity provider.
     * @param Response           $response    The response.
     * @return array  Array with Assertion objects, containing valid assertions from the response.
     */
    public static function processResponse(
        SimpleSAML_Configuration $spMetadata,
        SimpleSAML_Configuration $idpMetadata,
        Response $response
    ) {

        if (!$response->isSuccess()) {
            throw self::getResponseError($response);
        }

        /* Validate Response-element destination. */
        $currentRequest = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getSymfonyRequest();
        $requestUri     = $currentRequest->getSchemeAndHttpHost() . $currentRequest->getRequestUri();
        $msgDestination = $response->getDestination();

        if ($msgDestination !== null && $msgDestination !== $requestUri) {
            throw new Exception(
                'Destination in response doesn\'t match the current URL. Destination is "' .
                $msgDestination . '", current URL is "' . $requestUri . '".'
            );
        }

        $responseSigned = self::checkSign($idpMetadata, $response);

        /*
         * When we get this far, the response itself is valid.
         * We only need to check signatures and conditions of the response.
         */

        $assertion = $response->getAssertions();
        if (empty($assertion)) {
            throw new SimpleSAML_Error_Exception('No assertions found in response from IdP.');
        }

        $ret = array();
        foreach ($assertion as $a) {
            $ret[] = self::processAssertion($spMetadata, $idpMetadata, $response, $a, $responseSigned);
        }

        return $ret;
    }

    /**
     * Process an assertion in a response.
     *
     * Will throw an exception if it is invalid.
     *
     * @param SimpleSAML_Configuration                 $spMetadata     The metadata of the service provider.
     * @param SimpleSAML_Configuration                 $idpMetadata    The metadata of the identity provider.
     * @param Response                           $response       The response containing the assertion.
     * @param Assertion|EncryptedAssertion $assertion      The assertion.
     * @param bool                                     $responseSigned Whether the response is signed.
     * @return Assertion  The assertion, if it is valid.
     */
    private static function processAssertion(
        SimpleSAML_Configuration $spMetadata,
        SimpleSAML_Configuration $idpMetadata,
        Response $response,
        $assertion,
        $responseSigned
    ) {
        assert('$assertion instanceof \SAML2\Assertion || $assertion instanceof \SAML2\EncryptedAssertion');
        assert('is_bool($responseSigned)');

        $assertion = self::decryptAssertion($idpMetadata, $spMetadata, $assertion);

        if (!self::checkSign($idpMetadata, $assertion)) {
            if (!$responseSigned) {
                throw new SimpleSAML_Error_Exception('Neither the assertion nor the response was signed.');
            }
        }
        /* At least one valid signature found. */
        $currentRequest = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getSymfonyRequest();
        $requestUri     = $currentRequest->getSchemeAndHttpHost() . $currentRequest->getRequestUri();

        /* Check various properties of the assertion. */

        $notBefore = $assertion->getNotBefore();
        if ($notBefore !== null && $notBefore > time() + 60) {
            throw new SimpleSAML_Error_Exception(
                'Received an assertion that is valid in the future. Check clock synchronization on IdP and SP.'
            );
        }

        $notOnOrAfter = $assertion->getNotOnOrAfter();
        if ($notOnOrAfter !== null && $notOnOrAfter <= time() - 60) {
            throw new SimpleSAML_Error_Exception(
                'Received an assertion that has expired. Check clock synchronization on IdP and SP.'
            );
        }

        $sessionNotOnOrAfter = $assertion->getSessionNotOnOrAfter();
        if ($sessionNotOnOrAfter !== null && $sessionNotOnOrAfter <= time() - 60) {
            throw new SimpleSAML_Error_Exception(
                'Received an assertion with a session that has expired. Check clock synchronization on IdP and SP.'
            );
        }

        $validAudiences = $assertion->getValidAudiences();
        if ($validAudiences !== null) {
            $spEntityId = $spMetadata->getString('entityid');
            if (!in_array($spEntityId, $validAudiences, true)) {
                $candidates = '[' . implode('], [', $validAudiences) . ']';
                throw new SimpleSAML_Error_Exception(
                    'This SP [' . $spEntityId . ']  is not a valid audience for the assertion. Candidates were: ' . $candidates
                );
            }
        }

        $found     = false;
        $lastError = 'No SubjectConfirmation element in Subject.';
        foreach ($assertion->getSubjectConfirmation() as $sc) {
            if ($sc->Method !== Constants::CM_BEARER && $sc->Method !== Constants::CM_HOK) {
                $lastError = 'Invalid Method on SubjectConfirmation: ' . var_export($sc->Method, true);
                continue;
            }

            /* Is SSO with HoK enabled? IdP remote metadata overwrites SP metadata configuration. */
            $hok = $idpMetadata->getBoolean('saml20.hok.assertion', null);
            if ($hok === null) {
                $hok = $spMetadata->getBoolean('saml20.hok.assertion', false);
            }
            if ($sc->Method === Constants::CM_BEARER && $hok) {
                $lastError = 'Bearer SubjectConfirmation received, but Holder-of-Key SubjectConfirmation needed';
                continue;
            }
            if ($sc->Method === Constants::CM_HOK && !$hok) {
                $lastError = 'Holder-of-Key SubjectConfirmation received, but the Holder-of-Key profile is not enabled.';
                continue;
            }

            $scd = $sc->SubjectConfirmationData;
            if ($sc->Method === Constants::CM_HOK) {
                /* Check HoK Assertion */
                if (HTTP::isHTTPS() === false) {
                    $lastError = 'No HTTPS connection, but required for Holder-of-Key SSO';
                    continue;
                }
                if (isset($_SERVER['SSL_CLIENT_CERT']) && empty($_SERVER['SSL_CLIENT_CERT'])) {
                    $lastError = 'No client certificate provided during TLS Handshake with SP';
                    continue;
                }
                /* Extract certificate data (if this is a certificate). */
                $clientCert = $_SERVER['SSL_CLIENT_CERT'];
                $pattern    = '/^-----BEGIN CERTIFICATE-----([^-]*)^-----END CERTIFICATE-----/m';
                if (!preg_match($pattern, $clientCert, $matches)) {
                    $lastError = 'Error while looking for client certificate during TLS handshake with SP, the client certificate does not '
                        . 'have the expected structure';
                    continue;
                }
                /* We have a valid client certificate from the browser. */
                $clientCert = str_replace(array("\r", "\n", " "), '', $matches[1]);

                $keyInfo = array();
                foreach ($scd->info as $thing) {
                    if ($thing instanceof KeyInfo) {
                        $keyInfo[] = $thing;
                    }
                }
                if (count($keyInfo) != 1) {
                    $lastError = 'Error validating Holder-of-Key assertion: Only one <ds:KeyInfo> element in <SubjectConfirmationData> allowed';
                    continue;
                }

                $x509data = array();
                foreach ($keyInfo[0]->info as $thing) {
                    if ($thing instanceof X509Data) {
                        $x509data[] = $thing;
                    }
                }

                if (count($x509data) != 1) {
                    $lastError = 'Error validating Holder-of-Key assertion: Only one <ds:X509Data> element in <ds:KeyInfo> within <SubjectConfirmationData> allowed';
                    continue;
                }

                $x509cert = array();
                foreach ($x509data[0]->data as $thing) {
                    if ($thing instanceof X509Certificate) {
                        $x509cert[] = $thing;
                    }
                }

                if (count($x509cert) != 1) {
                    $lastError = 'Error validating Holder-of-Key assertion: Only one <ds:X509Certificate> element in <ds:X509Data> within <SubjectConfirmationData> allowed';
                    continue;
                }

                $HoKCertificate = $x509cert[0]->certificate;
                if ($HoKCertificate !== $clientCert) {
                    $lastError = 'Provided client certificate does not match the certificate bound to the Holder-of-Key assertion';
                    continue;
                }
            }

            if ($scd->NotBefore && $scd->NotBefore > time() + 60) {
                $lastError = 'NotBefore in SubjectConfirmationData is in the future: ' . $scd->NotBefore;
                continue;
            }
            if ($scd->NotOnOrAfter && $scd->NotOnOrAfter <= time() - 60) {
                $lastError = 'NotOnOrAfter in SubjectConfirmationData is in the past: ' . $scd->NotOnOrAfter;
                continue;
            }
            if ($scd->Recipient !== null && $scd->Recipient !== $requestUri) {
                $lastError = 'Recipient in SubjectConfirmationData does not match the current URL. Recipient is ' .
                    var_export($scd->Recipient, true) . ', current URL is ' . var_export($requestUri, true) . '.';
                continue;
            }
            if ($scd->InResponseTo !== null && $response->getInResponseTo(
                ) !== null && $scd->InResponseTo !== $response->getInResponseTo()
            ) {
                $lastError = 'InResponseTo in SubjectConfirmationData does not match the Response. Response has ' .
                    var_export($response->getInResponseTo(), true) . ', SubjectConfirmationData has ' . var_export(
                        $scd->InResponseTo,
                        true
                    ) . '.';
                continue;
            }
            $found = true;
            break;
        }
        if (!$found) {
            throw new SimpleSAML_Error_Exception('Error validating SubjectConfirmation in Assertion: ' . $lastError);
        }

        /* As far as we can tell, the assertion is valid. */

        /* Maybe we need to base64 decode the attributes in the assertion? */
        if ($idpMetadata->getBoolean('base64attributes', false)) {
            $attributes    = $assertion->getAttributes();
            $newAttributes = array();
            foreach ($attributes as $name => $values) {
                $newAttributes[$name] = array();
                foreach ($values as $value) {
                    foreach (explode('_', $value) AS $v) {
                        $newAttributes[$name][] = base64_decode($v);
                    }
                }
            }
            $assertion->setAttributes($newAttributes);
        }

        /* Decrypt the NameID element if it is encrypted. */
        if ($assertion->isNameIdEncrypted()) {
            try {
                $keys = self::getDecryptionKeys($idpMetadata, $spMetadata);
            } catch (Exception $e) {
                throw new SimpleSAML_Error_Exception('Error decrypting NameID: ' . $e->getMessage());
            }

            $blacklist = self::getBlacklistedAlgorithms($idpMetadata, $spMetadata);

            $lastException = null;
            foreach ($keys as $i => $key) {
                try {
                    $assertion->decryptNameId($key, $blacklist);
                    Logger::debug('Decryption with key #' . $i . ' succeeded.');
                    $lastException = null;
                    break;
                } catch (Exception $e) {
                    Logger::debug(
                        'Decryption with key #' . $i . ' failed with exception: ' . $e->getMessage()
                    );
                    $lastException = $e;
                }
            }
            if ($lastException !== null) {
                throw $lastException;
            }
        }

        return $assertion;
    }
}
