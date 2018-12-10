<?php

use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\Value\Saml\Metadata\ShibbolethMetadataScope;
use OpenConext\Value\Saml\Metadata\ShibbolethMetadataScopeList;
use Psr\Log\LoggerInterface;

class EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalName extends
    EngineBlock_Corto_Filter_Command_Abstract
{
    const EDU_PERSON_PRINCIPAL_NAME_URN_MACE = 'urn:mace:dir:attribute-def:eduPersonPrincipalName';
    const EDU_PERSON_PRINCIPAL_NAME_URN_OID  = 'urn:oid:1.3.6.1.4.1.5923.1.1.1.6';

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
        $this->logger->info('Verifying if eduPersonPrincipalName is allowed by configured IdP shibmd:scopes');

        $scopes = $this->_identityProvider->shibMdScopes;
        if (empty($scopes)) {
            $this->logger->notice('No shibmd:scope found in the IdP metadata, not verifying eduPersonPrincipalName');

            return;
        }

        $eduPersonPrincipalName = $this->resolveEduPersonPrincipalName();
        if ($eduPersonPrincipalName === false) {
            $this->logger->notice('No eduPersonPrincipalName found in response, not verifying');

            return;
        }

        if (strpos($eduPersonPrincipalName, '@') === false) {
            $this->logger->warning('Value of attribute eduPersonPrincipalName does not contain "@", not verifying');

            return;
        }

        $scopeList = $this->buildScopeList($scopes);
        list(,$suffix) = explode('@', $eduPersonPrincipalName, 2);

        if (!$scopeList->inScope($suffix)) {
            $message = sprintf(
                'eduPersonPrincipalName attribute value "%s" is not allowed by configured ShibMdScopes for IdP "%s"',
                $suffix, $this->_identityProvider->entityId
            );

            $this->logger->warning($message);

            if ($this->blockUserOnViolation) {
                throw new EngineBlock_Corto_Exception_InvalidAttributeValue($message, 'eduPersonPrincipalName', $suffix);
            }
        }
    }

    /**
     * @return string|false
     */
    private function resolveEduPersonPrincipalName()
    {
        $attributes = $this->_response->getAssertion()->getAttributes();

        if (isset($attributes[self::EDU_PERSON_PRINCIPAL_NAME_URN_MACE])) {
            return reset($attributes[self::EDU_PERSON_PRINCIPAL_NAME_URN_MACE]);
        } else {
            $this->logger->debug('No eduPersonPrincipleName attribute found using urn:mace');
        }

        if (isset($attributes[self::EDU_PERSON_PRINCIPAL_NAME_URN_OID])) {
            return reset($attributes[self::EDU_PERSON_PRINCIPAL_NAME_URN_OID]);
        } else {
            $this->logger->debug('No eduPersonPrincipleName attribute found using urn:oid');
        }

        return false;
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
