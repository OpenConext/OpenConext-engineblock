<?php

/**
 * Copyright 2022 SURFnet B.V.
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

/**
 */
class EngineBlock_Corto_Filter_Command_ApplyTrustedProxyBehavior extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    const ATTRIBUTE_NAME = 'urn:mace:surf.nl:attribute-def:internal-collabPersonId';
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * When a TP is involved in an authentication, a special case is triggered:
     *   the NameID is set to the collabPersonId ("urn:collab:person:...") and
     *   the epTID to the remote SP's NameID as configured (persistent,
     *   transient...).
     *
     * We want to move the information about the collabPersonId that the TP needs
     * to a dedicated attribute. And then not make special cases for NameID, let
     * this work just like any other SP.
     *
     * This internal attribute is named:
     * urn:mace:surf.nl:attribute-def:internal-collabPersonId
     */
    public function execute()
    {
        $this->logger->info('Executing the ApplyTrustedProxyBehavior output filter');
        if (!$this->_serviceProvider->getCoins()->isTrustedProxy()) {
            return;
        }
        $this->logger->info('Adding internal-collabPersonId to the attributes, set with collabPersonId');
        $this->_responseAttributes[self::ATTRIBUTE_NAME] = [$this->_collabPersonId];
    }

    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }
}
