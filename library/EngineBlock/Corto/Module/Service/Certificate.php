<?php

class EngineBlock_Corto_Module_Service_Certificate extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        $filename = $_SERVER['SERVER_NAME'] . '.pem';
        $certificates = $this->_server->getCertificates();
        $publicCertContents = $certificates['public'];

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