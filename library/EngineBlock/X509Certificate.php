<?php
/**
 * SURFconext EngineBlock
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
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class EngineBlock_X509Certificate 
{
    const PEM_PUBLIC_HEADER = '-----BEGIN CERTIFICATE-----';
    const PEM_PUBLIC_FOOTER = '-----END CERTIFICATE-----';
    const PEM_PRIVATE_HEADER = '-----BEGIN RSA PRIVATE KEY-----';
    const PEM_PRIVATE_FOOTER = '-----END RSA PRIVATE KEY-----';

    public static function getPublicPemCertFromCertData($certData)
    {

        // Remove newlines, spaces and tabs (http://micro-optimization.com/str_replace-vs-implode-explode)
        $certData = str_replace(array("\n", " ", "\t", "\x09"),"", $certData);

        // Chunk it in 64 character bytes
        $publicKey = self::PEM_PUBLIC_HEADER .
            PHP_EOL .
            chunk_split($certData, 64, PHP_EOL) .
            self::PEM_PUBLIC_FOOTER .
            PHP_EOL;

        $openSslPubKey = openssl_pkey_get_public($publicKey);
        if ($openSslPubKey === false){
            throw new EngineBlock_Exception("Pub key $publicKey is not a valid public key!");
        }

        return $publicKey;
    }

    public static function getPrivatePemCertFromCertData($certData)
    {
        // Remove newlines, spaces and tabs (http://micro-optimization.com/str_replace-vs-implode-explode)
        $certData = str_replace(array("\n", " ", "\t", "\x09"),"", $certData);

        // Chunk it in 64 character bytes
        $pemKey = self::PEM_PRIVATE_HEADER .
            PHP_EOL .
            chunk_split($certData, 64, PHP_EOL) .
            self::PEM_PRIVATE_FOOTER .
            PHP_EOL;

        $openSslPrivateKey = openssl_pkey_get_private($pemKey);
        if ($openSslPrivateKey === false){
            throw new EngineBlock_Exception("Private key $pemKey is not a valid public key!");
        }

        return $pemKey;
    }

    /**
     * Loads SSL certificate for a given url
     *
     * @param   string  $url
     * @return  resource $certificateResource
     * @throws  Exception if loading fails
     */
    public function loadFromUrl($url)
    {
        $context = stream_context_create (array(
            "ssl" => array(
                "capture_peer_cert" => true
            )
        ));

        $sslUrl = str_replace('https://', '', $url);
        $sslUrl = 'ssl://' . $sslUrl;

        $timeoutSeconds = 30;
        $streamResource = @stream_socket_client(
            $sslUrl,
            $errorNr,
            $errorMessage,
            $timeoutSeconds,
            STREAM_CLIENT_CONNECT,
            $context
        );
        if(!is_resource($streamResource)) {
            throw new Exception('Failed loading SSL certificate: "' . $errorMessage . '"');
        }

        $contextParams = stream_context_get_params($streamResource);
        $certificateResource = $contextParams["options"]["ssl"]["peer_certificate"];

        return $certificateResource;
    }

    /**
     * Exports key of a given SSL certificate
     *
     * @param string    $url without protocol
     * @return string   $certificateContent
     */
    public function exportPemFromUrl($url) {
        $certificateResource = $this->loadFromUrl($url);
        openssl_x509_export($certificateResource, $certificateContent);

        $certificateContent = str_replace('-----BEGIN CERTIFICATE-----' ,'', $certificateContent);
        $certificateContent = str_replace('-----END CERTIFICATE-----' ,'', $certificateContent);

        return $certificateContent;
    }
}
