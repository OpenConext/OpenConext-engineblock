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
