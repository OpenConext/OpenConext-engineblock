<?php

use OpenConext\Component\EngineBlockMetadata\Entity\Assembler\JanusPushMetadataAssembler;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\DoctrineMetadataRepository;

class Api_Controller_Connections extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
        $this->setNoRender();

        if (!$this->requireApiAuth()) {
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
            $this->_getResponse()->setStatus(400, 'Bad Request');
            $this->_getResponse()->setBody(
              json_encode('Unable to decode body as JSON')
            );
            return;
        }

        if (!is_object($connections) || !isset($connections->connections) && !is_object($connections->connections)) {
            $this->_getResponse()->setStatus(400, 'Bad Request');
            $this->_getResponse()->setBody(
              json_encode('Unrecognized structure for JSON')
            );
            return;
        }

        $assembler = new JanusPushMetadataAssembler();
        $roles = $assembler->assemble($connections->connections);

        $doctrineRepository = DoctrineMetadataRepository::createFromConfig(
            array(),
            EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()
        );
        $result = $doctrineRepository->synchronize($roles);

        $this->_getResponse()->setBody(json_encode($result));
    }

    public function testAction()
    {
        if (!$this->requireApiAuth()) {
            return;
        }
    }

    private function requireApiAuth()
    {
        $configuration = EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue('engineApi');

        if (!$configuration) {
            throw new EngineBlock_Exception('API access disabled');
        }

        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            $this->setNoRender();
            $this->_getResponse()->setHeader(
              'WWW-Authenticate',
              'Basic realm="EngineBlock API'
            );
            $this->_getResponse()->setStatus(401, 'Unauthorized');
            $this->_getResponse()->setBody(json_encode('Unauthenticated'));
            return false;
        }

        if ($_SERVER['PHP_AUTH_USER'] !== $configuration->user) {
            $this->setNoRender();
            $this->_getResponse()->setHeader(
              'WWW-Authenticate',
              'Basic realm="EngineBlock API'
            );
            $this->_getResponse()->setStatus(401, 'Unauthorized');
            $this->_getResponse()->setBody(json_encode('Invalid credentials'));
            return false;
        }

        if ($_SERVER['PHP_AUTH_PW'] !== $configuration->password) {
            $this->setNoRender();
            $this->_getResponse()->setHeader(
              'WWW-Authenticate',
              'Basic realm="EngineBlock API'
            );
            $this->_getResponse()->setStatus(401, 'Unauthorized');
            $this->_getResponse()->setBody(json_encode('Invalid credentials'));
            return false;
        }

        return true;
    }
}
