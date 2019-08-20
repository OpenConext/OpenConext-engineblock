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

        if (!$this->isAuthnContextClassRefAllowed($authnContextClassRef, $this->authnContextClassRefBlacklistPattern)) {
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
     * @throws EngineBlock_Exception
     */
    private function isAuthnContextClassRefAllowed($value, $regex)
    {
        $match = @preg_match($regex, $value);
        if ($match) {
            return false;
        }

        if ($match === false) {
            throw new EngineBlock_Exception(
                sprintf(
                    'Invalid authn_context_class_ref_blacklist_regex found in the configuration: "%s"',
                    $regex
                )
            );
        }

        return true;
    }
}
