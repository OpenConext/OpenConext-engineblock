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

use OpenConext\EngineBlockBundle\Pdp\Dto\Attribute;
use OpenConext\EngineBlockBundle\Pdp\Dto\Request;
use OpenConext\EngineBlockBundle\Pdp\PolicyDecision;

class EngineBlock_PolicyDecisionPoint_PepValidator
{
    const DEFAULT_LANG = 'en';

    /**
     * @var array<string,string>
     */
    private $message;

    /**
     * @param string $subjectId
     * @param string $idp
     * @param string $sp
     * @param array $responseAttributes
     * @return bool
     */
    public function hasAccess($subjectId, $idp, $sp, array $responseAttributes)
    {
        $accessSubjectIdAttribute = new Attribute;
        $accessSubjectIdAttribute->attributeId = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';
        $accessSubjectIdAttribute->value = $subjectId;
        $accessSubjectAttributes = [$accessSubjectIdAttribute];

        foreach ($responseAttributes as $id => $values) {
            foreach ($values as $value) {
                $accessSubjectResponseAttribute = new Attribute;
                $accessSubjectResponseAttribute->attributeId = $id;
                $accessSubjectResponseAttribute->value = $value;
                $accessSubjectAttributes[] = $accessSubjectResponseAttribute;
            }
        }

        $clientIdAttribute = new Attribute;
        $clientIdAttribute->attributeId = 'ClientID';
        $clientIdAttribute->value = $this->getPdpClientId();
        $idpAttribute = new Attribute;
        $idpAttribute->attributeId = 'IDPentityID';
        $idpAttribute->value = $idp;
        $spAttribute = new Attribute;
        $spAttribute->attributeId = 'SPentityID';
        $spAttribute->value = $sp;

        $pdpRequest = new Request();
        $pdpRequest->accessSubject->attributes = $accessSubjectAttributes;
        $pdpRequest->resource->attributes = [$clientIdAttribute, $idpAttribute, $spAttribute];

        $pdpClient = $this->getPdpClient();

        /** @var PolicyDecision $policyDecision */
        $policyDecision = $pdpClient->requestDecisionFor($pdpRequest);
        if ($policyDecision->permitsAccess()) {
            return true;
        }

        if ($policyDecision->isDeny()) {
            $this->message = $policyDecision->getLocalizedDenyMessage($this->getLocale(), self::DEFAULT_LANG);
        }

        if ($policyDecision->isIndeterminate() && $policyDecision->hasStatusMessage()) {
            $this->message = $policyDecision->getStatusMessage();
        }

        return false;
    }

    /**
     * Get the response message when subject has no access.
     * @return null|string
     */
    public function getMessage()
    {
        return $this->message;
    }

    private function getPdpClient()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getPdpClient();
    }

    private function getPdpClientId()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getPdpClientId();
    }

    private function getLocale()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getTranslator()->getLocale();
    }
}
