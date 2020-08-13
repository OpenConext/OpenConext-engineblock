<?php

/**
 * Copyright 2010 SURFnet B.V.
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

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * See SimpleSAMLphp SimpleSAML\Utils\Random::generateId().
     *
     * @return string
     */
    public function generateId(): string
    {
        return '_' . bin2hex(openssl_random_pseudo_bytes((int)((self::ID_LENGTH - 1)/2)));
    }

    public function debugMessage($message, $type): void
    {
        $this->getLogger()->debug("SAML2 library debug message ($type)", array('message' => $message));
    }

    public function redirect($url, $data = array()): void
    {
        throw new BadMethodCallException(
            sprintf(
                '"%s":"%s" may not be called in the Surfnet\\SamlBundle as it doesn\'t work with Symfony',
                __CLASS__,
                __METHOD__
            )
        );
    }

    public function postRedirect($url, $data = array()): void
    {
        throw new BadMethodCallException(
            sprintf(
                '"%s":"%s" may not be called in the Surfnet\\SamlBundle as it doesn\'t work with Symfony"',
                __CLASS__,
                __METHOD__
            )
        );
    }

    public function getTempDir(): string
    {
        throw new BadMethodCallException(
            sprintf(
                '"%s":"%s" may not be called in the Surfnet\\SamlBundle as it doesn\'t work with Symfony"',
                __CLASS__,
                __METHOD__
            )
        );
    }

    public function writeFile(string $filename, string $data, int $mode = null): void
    {
        throw new BadMethodCallException(
            sprintf(
                '"%s":"%s" may not be called in the Surfnet\\SamlBundle as it doesn\'t work with Symfony"',
                __CLASS__,
                __METHOD__
            )
        );
    }
}
