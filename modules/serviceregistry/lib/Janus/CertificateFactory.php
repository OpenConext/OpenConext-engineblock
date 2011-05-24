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
