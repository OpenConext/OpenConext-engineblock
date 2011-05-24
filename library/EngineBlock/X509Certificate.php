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
    public static function getPemFromCertData($certData)
    {
        $publicKey = implode('', explode(" ", implode('', explode("\n", $certData))));
        $publicKey = "-----BEGIN CERTIFICATE-----" . PHP_EOL .
                chunk_split($publicKey, 64, PHP_EOL) .
                "-----END CERTIFICATE-----" . PHP_EOL;

        $openSslPubKey = openssl_pkey_get_public($publicKey);
        if ($openSslPubKey === false){
            throw new EngineBlock_Exception("Pub key $publicKey is not a valid public key!");
        }

        return $publicKey;
    }
}
