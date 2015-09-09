<?php

use OpenConext\Component\EngineBlockMetadata\Entity\Assembler\JanusPushMetadataAssembler;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\DoctrineMetadataRepository;
use OpenConext\Component\EngineBlockMetadata\Service\JanusPushMetadataSynchronizer;

class Api_Controller_Connections extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
        $this->setNoRender();

        $configuration = EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue('engineApi');

        if (!$configuration) {
            throw new EngineBlock_Exception('API access disabled');
        }

        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            header('WWW-Authenticate: Basic realm="EngineBlock API"');
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode('Unauthenticated');
            exit;
        }

        if ($_SERVER['PHP_AUTH_USER'] !== $configuration->user) {
            header('WWW-Authenticate: Basic realm="EngineBlock API"');
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode('Invalid credentials');
            exit;
        }

        if ($_SERVER['PHP_AUTH_PW'] !== $configuration->password) {
            header('WWW-Authenticate: Basic realm="EngineBlock API"');
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode('Invalid credentials');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode('Not a POST request');
            return;
        }

        ini_set('memory_limit', '265M');

        $body = $this->_getRequest()->getRawBody();

        if (!$body) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode('No body');
            exit;
        }

        $connections = json_decode($body);

        if (!$connections) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode('Unable to decode body as JSON');
            exit;
        }

        if (!is_object($connections) || !isset($connections->connections) && !is_object($connections->connections)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode('Unrecognized structure for JSON');
            exit;
        }

        $assembler = new JanusPushMetadataAssembler();
        $roles = $assembler->assemble($connections->connections);

        $doctrineRepository = DoctrineMetadataRepository::createFromConfig(
            array(),
            EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()
        );
        $result = $doctrineRepository->synchronize($roles);

        echo json_encode($result);
    }
}
