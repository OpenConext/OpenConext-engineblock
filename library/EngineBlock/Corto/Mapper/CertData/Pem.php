<?php

class EngineBlock_Corto_Mapper_CertData_Pem
{
    private $_pemKey;

    public function __construct($pemKey)
    {
        $this->_pemKey = $pemKey;
    }

    public function map()
    {
        $lines = explode("\n", $this->_pemKey);
        $data = '';
        foreach ($lines as $line) {
            $line = rtrim($line);
            if ($line === '-----BEGIN CERTIFICATE-----') {
                $data = '';
            } elseif ($line === '-----END CERTIFICATE-----') {
                break;
            } else {
                $data .= $line . PHP_EOL;
            }
        }
        return $data;
    }
}