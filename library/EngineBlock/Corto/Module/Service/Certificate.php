<?php

/**
 * Copyright 2014 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class EngineBlock_Corto_Module_Service_Certificate extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        $filename = $_SERVER['SERVER_NAME'] . '.pem';
        $certificates = $this->_server->getSigningCertificates();
        $publicCertContents = $certificates->getCertificate()->toPem();

        header('Content-Type: application/x-pem-file');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($publicCertContents));
        header('Expires: 0');

        // check for IE only headers
        if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        } else {
            header('Pragma: no-cache');
        }

        echo $publicCertContents;
    }
}
