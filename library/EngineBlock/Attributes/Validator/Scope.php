<?php

/**
 * Copyright 2024 SURF B.V.
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

use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\Value\Saml\Metadata\ShibbolethMetadataScope;
use OpenConext\Value\Saml\Metadata\ShibbolethMetadataScopeList;

class EngineBlock_Attributes_Validator_Scope extends EngineBlock_Attributes_Validator_Abstract
{
    const ERROR_ATTRIBUTE_VALIDATOR_SCOPE = 'error_attribute_validator_scope';

    public function validate(array $attributes)
    {
        if (empty($attributes[$this->_attributeName])) {
            return true;
        }

        $logger = EngineBlock_ApplicationSingleton::getLog();

        $scopes = $this->_identityProvider->shibMdScopes;
        if (empty($scopes)) {
            $logger->notice('No shibmd:scope found in the IdP metadata, not verifying ' . $this->_attributeName);
            return true;
        }
        $scopeList = $this->buildScopeList($scopes);

        foreach ($attributes[$this->_attributeName] as $attributeValue) {
            if($scopeList->inScope($attributeValue)) {
                continue;
            }
            // consider moving this to inScope()
            list(,$suffix) = explode('@', $attributeValue, 2);
            if(empty($suffix) || $scopeList->inScope($suffix)) {
                continue;
            }
            $logger->warning(sprintf(
                '%s attribute value "%s" is not allowed by configured ShibMdScopes for IdP "%s"',
                $this->_attributeName,
                $attributeValue,
                $this->_identityProvider->entityId
            ));
            $this->_messages[] = [
                self::ERROR_ATTRIBUTE_VALIDATOR_SCOPE,
                $this->_attributeName,
                $this->_options,
                $attributeValue
            ];
            return false;
        }
        return true;
    }

    /**
     * @param ShibMdScope[] $scopes
     * @return ShibbolethMetadataScopeList
     */
    private function buildScopeList(array $scopes)
    {
        $scopes = array_map(
            function (ShibMdScope $scope) {
                if (!$scope->regexp) {
                    return ShibbolethMetadataScope::literal($scope->allowed);
                }

                return ShibbolethMetadataScope::regexp($scope->allowed);
            },
            $scopes
        );

        return new ShibbolethMetadataScopeList($scopes);
    }
}
