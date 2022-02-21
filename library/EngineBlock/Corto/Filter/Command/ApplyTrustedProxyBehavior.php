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
     * When a TP is involved in an authentication, a special case is triggered.
     * The urn:mace:surf.nl:attribute-def:internal-collabPersonId is added to
     * the response attributes set with the collabPersonId.
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
