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

class EngineBlock_Corto_Filter_Command_ValidateRequiredAttributes extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    const URN_MACE_TERENA_SCHACHOMEORG = 'urn:mace:terena.org:attribute-def:schacHomeOrganization';

    /**
     * {@inheritdoc}
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    /**
     * @throws EngineBlock_Corto_Exception_MissingRequiredFields
     */
    public function execute()
    {
        // ServiceRegistry override of SchacHomeOrganization, set it and skip validation
        $excluded = array();
        if ($this->_identityProvider->getCoins()->schacHomeOrganization()) {
            $this->_responseAttributes[self::URN_MACE_TERENA_SCHACHOMEORG] = array(
                $this->_identityProvider->getCoins()->schacHomeOrganization()
            );
            $excluded[] = static::URN_MACE_TERENA_SCHACHOMEORG;
        }

        $validationResult = EngineBlock_ApplicationSingleton::getInstance()
            ->getDiContainer()
            ->getAttributeValidator()
            ->validate($this->_responseAttributes, $excluded);

        if ($validationResult->hasErrors()) {
            throw new EngineBlock_Corto_Exception_MissingRequiredFields(
                sprintf(
                    'Errors validating attributes errors: "%s" attributes: "%s"',
                    print_r($validationResult->getErrors(), true),
                    print_r($this->_responseAttributes, true)
                )
            );
        }
    }
}
