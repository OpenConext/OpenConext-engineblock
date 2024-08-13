<?php

/**
 * Copyright 2024 SURFnet B.V.
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

class EngineBlock_Corto_Filter_Command_AttributeReleaseAs extends EngineBlock_Corto_Filter_Command_Abstract implements
    EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        $logger = EngineBlock_ApplicationSingleton::getLog();

        $serviceProvider = EngineBlock_SamlHelper::findRequesterServiceProvider(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository(),
            $logger
        );
        if ($serviceProvider === null) {
            $serviceProvider = $this->_serviceProvider;
        }

        $arp = $serviceProvider->getAttributeReleasePolicy();
        if ($arp === null) {
            $logger->info(sprintf("No ARP available for %s. No 'release-as' instructions to apply.", $serviceProvider->entityId));
            return;
        }

        $releaseAs = $arp->getRulesWithReleaseAsSpecification();
        if (empty($releaseAs)) {
            $logger->info(sprintf("No 'release-as' instructions to apply for %s.", $serviceProvider->entityId));
        }
        $enforcer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getReleaseAsEnforcer();

        $attributes = $this->_responseAttributes;
        $this->_responseAttributes = $enforcer->enforce($attributes, $releaseAs);
    }
}
