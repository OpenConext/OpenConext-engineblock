<?php

use Psr\Log\LoggerInterface;

final class EngineBlock_Saml2_Container extends SAML2_Compat_AbstractContainer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function generateId()
    {
        return SimpleSAML_Utilities::generateID();
    }

    public function debugMessage($message, $type)
    {
        SimpleSAML_Utilities::debugMessage($message, $type);
    }

    public function redirect($url, $data = array())
    {
        SimpleSAML_Utilities::redirectTrustedURL($url, $data);
    }

    public function postRedirect($url, $data = array())
    {
        SimpleSAML_Utilities::postRedirect($url, $data);
    }
}
