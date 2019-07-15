<?php

use OpenConext\EngineBlock\Assert\Assertion;
use Psr\Log\LoggerInterface;

/**
 * The validator will block any incoming IdP assertion with a AuthnContextClassRef value in the configured namespace
 * with an error. As it MUST be impossible for an IdP to 'impersonate' the blacklisted values used for AuthnContextClassRef.
 **/
class EngineBlock_Corto_Filter_Command_ValidateAuthnContextClassRef extends EngineBlock_Corto_Filter_Command_Abstract
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $authnContextClassRefBlacklistPattern;

    public function __construct(LoggerInterface $logger, $authnContextClassRefBlacklistPattern)
    {
        Assertion::nullOrString($authnContextClassRefBlacklistPattern, 'The authnContextClassRefBlacklistPattern must be a string or null');

        $this->logger = $logger;
        $this->authnContextClassRefBlacklistPattern = $authnContextClassRefBlacklistPattern;
    }

    /**
     * @throws EngineBlock_Corto_Exception_AuthnContextClassRefBlacklisted
     */
    public function execute()
    {
        if (empty($this->authnContextClassRefBlacklistPattern)) {
            $this->logger->notice('No authn_context_class_ref_blacklist_regex found in the configuration, not validating AuthnContextClassRef');
            return;
        }

        $authnContextClassRef = $this->_response->getAssertion()->getAuthnContextClassRef();

        if (!$this->validateAuthnContextClassRef($authnContextClassRef, $this->authnContextClassRefBlacklistPattern)) {
            throw new EngineBlock_Corto_Exception_AuthnContextClassRefBlacklisted(
                sprintf(
                    'Assertion from IdP contains a blacklisted AuthnContextClassRef "%s"',
                    $authnContextClassRef
                )
            );
        }
    }

    /**
     * @param string $value
     * @param string $regex
     * @return bool
     */
    private function validateAuthnContextClassRef($value, $regex)
    {
        if (preg_match($regex, $value)) {
            return false;
        }

        return true;
    }
}
