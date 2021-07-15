<?php

namespace OpenConext\EngineBlock\Service;

use EngineBlock_Corto_ProxyServer;
use OpenConext\EngineBlock\Exception\InvalidJsonException;
use OpenConext\EngineBlock\Http\JsonResponseParser;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class SsoNotificationService
{
    private const SSO_NOT_COOKIE_NAME = "ssonot";
    private const FIELD_ENTITY_ID = "entityId";
    private const IV_SIZE = 16;
    private const KEY_SIZE = 256;
    private const ITERATION_COUNT = 1000;

    /**
     * @var string
     */
    private $encryptionKey;

    /**
     * @var string
     */
    private $encryptionSalt;

    /**
     * @var string
     */
    private $encryptionAlgorithm;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $encryptionKey, string $encryptionSalt, string $encryptionAlgorithm,
                                LoggerInterface $logger)
    {
        $this->encryptionKey = $encryptionKey;
        $this->encryptionSalt = $encryptionSalt;
        $this->encryptionAlgorithm = $encryptionAlgorithm;
        $this->logger = $logger;
    }

    /**
     * Parses the SSO notification cookie and if successful starts an authentication with the parsed IdP.
     *
     * @param ParameterBag $cookies the cookies in the current HTTP request
     * @param EngineBlock_Corto_ProxyServer $server the proxy server to start authentication from
     * @return string the entity ID of a known Identity Provider, otherwise an empty string
     */
    public function handleSsoNotification(ParameterBag $cookies, EngineBlock_Corto_ProxyServer $server): string
    {
        if (!is_null($this->getSsoCookie($cookies))) {
            $parsedSsoNotification = $this->parseSsoNotification($this->getSsoCookie($cookies));

            if (array_key_exists(self::FIELD_ENTITY_ID, $parsedSsoNotification)) {
                $idpEntityId = $parsedSsoNotification[self::FIELD_ENTITY_ID];
                if (!is_null($idpEntityId) && !is_null($server->getRepository()->findIdentityProviderByEntityId($idpEntityId))) {

                    return $idpEntityId;
                } else {
                    $this->logger->warning("SSO notification found for unknown IdP: '$idpEntityId'");
                }
            } else {
                $this->logger->warning("Field '" . self::FIELD_ENTITY_ID . "' not found in parsed SSO " .
                    "notification: " . json_encode($parsedSsoNotification));
            }
        }

        return '';
    }

    /**
     * Retrieves the SSO notification cookie from the provided set of cookies.
     *
     * @param ParameterBag $cookies the set of cookies to retrieve the SSO notification cookie from
     * @return mixed|null the SSO notification cookie or null if not present
     */
    public function getSsoCookie(ParameterBag $cookies) {
        return $cookies->get(self::SSO_NOT_COOKIE_NAME);
    }

    /**
     * Parses the provided SSO notification and returns an associative array if successful and an empty array otherwise.
     * The provided SSO notification should be a base64 string of a cipher to decrypt and an initialization vector to
     * decrypt with.
     *
     * @param string $ssoNotification the SSO notification as a base64 string
     * @return array array containing the data of the SSO notification or an empty array in case of an error
     */
    private function parseSsoNotification(string $ssoNotification): array
    {
        $data = [];

        // Extract cipher and initialization vector
        $base64Decoded = base64_decode($ssoNotification);
        $iv = substr($base64Decoded, 0, self::IV_SIZE);
        $cipherText = substr($base64Decoded, self::IV_SIZE);
        // Construct encryption key
        $key = hash_pbkdf2('sha256', $this->encryptionKey, $this->encryptionSalt, self::ITERATION_COUNT,
            self::KEY_SIZE, true);

        $jsonString = $this->decryptSsoNotification($cipherText, $key, $this->encryptionAlgorithm, $iv);
        try {
            $data = JsonResponseParser::parse($jsonString);
        } catch (InvalidJsonException $exception) {
            $this->logger->error("Failed to parse JSON string '$jsonString' from SSO notification",
                array('exception' => $exception));
        }
        return $data;
    }

    /**
     * Decrypts the SSO notification using the provided key, encryption algorithm and initialization vector.
     * Returns a JSON string or an empty string if the SSO notification cannot be decrypted.
     *
     * @param string $ssoNotification the SSO notification to decrypt
     * @param string $encryptionKey the encryption key to decrypt with
     * @param string $encryptionAlgorithm the encryption algorithm
     * @param string $iv the initialization vector to decrypt with
     * @return string a JSON string or an empty string in case the data could not be decrypted
     */
    private function decryptSsoNotification(string $ssoNotification, string $encryptionKey, string $encryptionAlgorithm,
                                            string $iv): string
    {
        $data = openssl_decrypt($ssoNotification, $encryptionAlgorithm, $encryptionKey, OPENSSL_RAW_DATA, $iv);
        if (!$data) {
            $this->logger->error("Failed to decrypt SSO notification '$ssoNotification' using algorithm " .
                "'$encryptionAlgorithm', returning empty string");

            return '';
        }
        return $data;
    }

}
