<?php

class Corto_Package_PhpFile extends Corto_Package_Abstract
{
    public function build($filePath)
    {
        $fileContents = "<?php" . PHP_EOL; // In the beginning there was PHP :)

        $configsExport          = var_export($this->_configs, true);
        $hostedEntitiesExport   = var_export($this->_metaData['hosted'], true);
        $remoteEntitiesExport   = var_export($this->_metaData['remote'], true);
        $templateContentsExport = var_export($this->_templateContents, true);

        $fileContents = <<<MAIN
\$server = new Corto_ProxyServer();

\$hostedEntities = $hostedEntitiesExport;
\$server->setHostedEntities(\$hostedEntities);

\$remoteEntities = $remoteEntitiesExport;
\$server->setRemoteEntities(\$remoteEntities);

\$configs = $configsExport;
\$server->setConfigs(\$configs);

\$server->setTemplateSource(
    Corto_ProxyServer::TEMPLATE_SOURCE_MEMORY,
    $templateContentsExport
);

\$server->serveRequest(\$_SERVER['PATH_INFO']);
MAIN;

        $fileContents .= $this->_getLibraryCode();

        return file_put_contents($filePath, $fileContents);
    }

    protected function _getLibraryCode()
    {
        // @todo recurse through parent directory, concatenating php file contents
        $code = "";
        return $code;
    }
}