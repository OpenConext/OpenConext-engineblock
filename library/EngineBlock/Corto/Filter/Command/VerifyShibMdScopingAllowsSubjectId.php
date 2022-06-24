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

use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\Value\Saml\Metadata\ShibbolethMetadataScope;
use OpenConext\Value\Saml\Metadata\ShibbolethMetadataScopeList;
use Psr\Log\LoggerInterface;
use SAML2\Constants;

class EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSubjectId extends
    EngineBlock_Corto_Filter_Command_Abstract
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $blockUserOnViolation;

    public function __construct(LoggerInterface $logger, $blockUserOnViolation)
    {
        $this->logger = $logger;
        $this->blockUserOnViolation = (bool)$blockUserOnViolation;
    }

    /**
     * @throws EngineBlock_Corto_Exception_InvalidAttributeValue
     */
    public function execute()
    {
        $this->logger->info('Verifying if subject-id is allowed by configured IdP shibmd:scopes');

        $scopes = $this->_identityProvider->shibMdScopes;
        if (empty($scopes)) {
            $this->logger->notice('No shibmd:scope found in the IdP metadata, not verifying subject-id');

            return;
        }

        $attributes = $this->_response->getAssertion()->getAttributes();
        if (!isset($attributes[Constants::ATTR_SUBJECT_ID])) {
            $this->logger->notice('No subject-id found in response, not verifying');

            return;
        }

        if (count($attributes[Constants::ATTR_SUBJECT_ID]) !== 1) {
            throw new EngineBlock_Corto_Exception_InvalidAttributeValue('Only exactly one subject-id allowed', 'subject-id', 'not exactly one value');
        }
        $subjectId = reset($attributes[Constants::ATTR_SUBJECT_ID]);

        if (strpos($subjectId, '@') === false) {
            throw new EngineBlock_Corto_Exception_InvalidAttributeValue('Invalid subject-id, missing @', 'subject-id', 'missing @ in value');
        }

        $scopeList = $this->buildScopeList($scopes);
        list(,$suffix) = explode('@', $subjectId, 2);

        if (!$scopeList->inScope($suffix)) {
            $message = sprintf(
                'subjectId attribute value scope "%s" is not allowed by configured ShibMdScopes for IdP "%s"',
                $suffix, $this->_identityProvider->entityId
            );

            $this->logger->warning($message);

            if ($this->blockUserOnViolation) {
                throw new EngineBlock_Corto_Exception_InvalidAttributeValue($message, 'subject-id', $suffix);
            }
        }
    }

    /**
     * @param ShibMdScope[] $scopes
     * @return ShibbolethMetadataScopeList
     */
    private function buildScopeList(array $scopes): ShibbolethMetadataScopeList
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
