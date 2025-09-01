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

namespace OpenConext\EngineBlockBundle\Pdp;

use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\AttributeAssignment;

final class PolicyDecision
{
    const DECISION_DENY = 'Deny';
    const DECISION_INDETERMINATE = 'Indeterminate';
    const DECISION_NOT_APPLICABLE = 'NotApplicable';
    const DECISION_PERMIT = 'Permit';

    /**
     * @var string
     */
    private $decision;

    /**
     * @var string[]
     */
    private $localizedDenyMessages = [];

    /**
     * @var string|null
     */
    private $statusMessage;

    /**
     * @var Obligation[]
     */
    private $stepupObligations;

    /**
     * @var bool
     */
    private $isIdpSpecific = false;

    /**
     * @var Logo
     */
    private $idpLogo;

    public static function fromResponse(Response $response) : PolicyDecision
    {
        $policyDecision = new self;
        $policyDecision->decision = $response->decision;

        if (isset($response->status->statusMessage)) {
            $policyDecision->statusMessage = $response->status->statusMessage;
        }

        $policyDecision->stepupObligations = self::findStepupObligations($response->obligations);

        if ($policyDecision->permitsAccess()) {
            return $policyDecision;
        }

        if (isset($response->associatedAdvices)) {
            $localizedDenyMessages = [];
            foreach ($response->associatedAdvices as $associatedAdvice) {
                foreach ($associatedAdvice->attributeAssignments as $attributeAssignment) {
                    $parts = explode(':', $attributeAssignment->attributeId);
                    if (count($parts) >= 2) {
                        list($identifier, $locale) = $parts;

                        if ($identifier === 'DenyMessage') {
                            $localizedDenyMessages[$locale] = $attributeAssignment->value;
                        }
                    }

                    self::setAttributeAssignmentSource($attributeAssignment, $policyDecision);
                }
            }
            $policyDecision->localizedDenyMessages = $localizedDenyMessages;
        }

        return $policyDecision;
    }

    /**
     * Checks obgligations for any stepup LoA requirements, returns all found.
     * @return Obligation[]
     */
    private static function findStepupObligations(?array $obligations) : array
    {
        $stepupObligations = [];
        if ($obligations !== null) {
            foreach ($obligations as $obligation) {
                if ($obligation->id === 'urn:openconext:stepup:loa') {
                    $stepupObligations[] = $obligation->attributeAssignments[0]->value;
                }
            }
        }
        return $stepupObligations;
    }

    /**
     * Checks attributeAssignment for clues if this assignment is IdP specific. And sets the idpOnly field
     * accordingly.
     */
    private static function setAttributeAssignmentSource(
        AttributeAssignment $attributeAssignment,
        PolicyDecision $policyDecision
    ) : void {

        if ($attributeAssignment->attributeId !== 'IdPOnly') {
            return;
        }

        if (isset($attributeAssignment->value) && $attributeAssignment->value === true) {
            $policyDecision->isIdpSpecific = true;
        }

        return;
    }

    public function permitsAccess() : bool
    {
        return $this->decision === self::DECISION_PERMIT || $this->decision === self::DECISION_NOT_APPLICABLE;
    }

    public function getLocalizedDenyMessage(string $locale, string $defaultLocale = 'en') : string
    {
        if (!$this->hasLocalizedDenyMessage()) {
            throw new RuntimeException(sprintf(
                'No localized deny messages present for decision "%s"',
                $this->decision
            ));
        }

        if (isset($this->localizedDenyMessages[$locale])) {
            return $this->localizedDenyMessages[$locale];
        }

        if (!isset($this->localizedDenyMessages[$defaultLocale])) {
            throw new RuntimeException(sprintf(
                'No localized deny message for locale "%s" or default locale "%s" found',
                $locale,
                $defaultLocale
            ));
        }

        return $this->localizedDenyMessages[$defaultLocale];
    }

    public function getStatusMessage() : ?string
    {
        if (!$this->hasStatusMessage()) {
            throw new RuntimeException('No status message found');
        }

        return $this->statusMessage;
    }

    public function hasLocalizedDenyMessage() : bool
    {
        return !empty($this->localizedDenyMessages);
    }

    public function hasStatusMessage() : bool
    {
        return isset($this->statusMessage);
    }

    public function setIdpLogo(?Logo $logoUri)
    {
        $this->idpLogo = $logoUri;
    }

    /**
     * If the logo is not set, this method returns null
     * @return Logo|null
     */
    public function getIdpLogo() : ?Logo
    {
        if ($this->isIdpSpecificMessage() && $this->hasIdpLogo()) {
            /** @var Logo $logo */
            return $this->idpLogo;
        }
        return null;
    }

    /**
     * @return Obligation[]
     */
    public function getStepupObligations() : array
    {
        return $this->stepupObligations;
    }

    private function hasIdpLogo() : bool
    {
        return !is_null($this->idpLogo);
    }

    private function isIdpSpecificMessage() : bool
    {
        return $this->isIdpSpecific;
    }
}
