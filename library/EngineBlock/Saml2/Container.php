<?php

use Psr\Log\LoggerInterface;
use SAML2\Compat\AbstractContainer;

final class EngineBlock_Saml2_Container extends AbstractContainer
{
    /**
     * The fixed length of random identifiers.
     */
    const ID_LENGTH = 43;

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

    /**
     * See SimpleSAMLphp SimpleSAML\Utils\Random::generateId().
     *
     * @return string
     */
    public function generateId()
    {
        return '_' . bin2hex(openssl_random_pseudo_bytes((int)((self::ID_LENGTH - 1)/2)));
    }

    public function debugMessage($message, $type)
    {
        $this->getLogger()->debug("SAML2 library debug message ($type)", array('message' => $message));
    }

    public function redirect($url, $data = array())
    {
        throw new BadMethodCallException(sprintf(
            "%s:%s may not be called in the Surfnet\\SamlBundle as it doesn't work with Symfony2",
            __CLASS__,
            __METHOD__
        ));
    }

    public function postRedirect($url, $data = array())
    {
        throw new BadMethodCallException(sprintf(
            "%s:%s may not be called in the Surfnet\\SamlBundle as it doesn't work with Symfony2",
            __CLASS__,
            __METHOD__
        ));
    }
}
