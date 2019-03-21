<?php

namespace OpenConext\EngineBlock\Metadata\Entity\Disassembler;

use DateTime;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

/**
 * The Corto disassembler disassembles an entity to legacy Corto arrays.
 *
 * Used primarily for legacy support in the Entity Manipulations.
 * Note that these should be migrated and then this disassembler can be removed.
 *
 * @package OpenConext\EngineBlock\Metadata\Legacy
 * @SuppressWarnings(PMD.TooManyMethods)
 * @SuppressWarnings(PMD.ExcessiveClassComplexity)
 */
class CortoDisassembler
{
    /**
     * @SuppressWarnings(PMD.CyclomaticComplexity)
     * @SuppressWarnings(PMD.NPathComplexity)
     * @param ServiceProvider $entity
     * @return array
     */
    public function translateServiceProvider(ServiceProvider $entity)
    {
        $cortoEntity = array();

        $cortoEntity = $this->translateCommon($entity, $cortoEntity);

        if ($entity->isTransparentIssuer) {
            $cortoEntity['TransparentIssuer'] = 'yes';
        }
        if ($entity->displayUnconnectedIdpsWayf) {
            $cortoEntity['DisplayUnconnectedIdpsWayf'] = 'yes';
        }
        foreach ($entity->assertionConsumerServices as $service) {
            if (!isset($cortoEntity['AssertionConsumerServices'])) {
                $cortoEntity['AssertionConsumerServices'] = array();
            }

            $cortoEntity['AssertionConsumerServices'][$service->serviceIndex] = array(
                'Binding'  => $service->binding,
                'Location' => $service->location,
            );
        }
        if (!$entity->isConsentRequired) {
            $cortoEntity['NoConsentRequired'] = true;
        }
        if ($entity->skipDenormalization) {
            $cortoEntity['SkipDenormalization'] = true;
        }
        if ($entity->policyEnforcementDecisionRequired) {
            $cortoEntity['PolicyEnforcementDecisionRequired'] = true;
        }
        if ($entity->isAttributeAggregationRequired()) {
            $cortoEntity['AttributeAggregationRequired'] = true;
        }
        if ($entity->requesteridRequired) {
            $cortoEntity['requesteridRequired'] = true;
        }

        return $cortoEntity;
    }

    /**
     * @param IdentityProvider $entity
     * @return array
     */
    public function translateIdentityProvider(IdentityProvider $entity)
    {
        $cortoEntity = array();

        $cortoEntity = $this->translateCommon($entity, $cortoEntity);

        foreach ($entity->singleSignOnServices as $service) {
            if (!isset($cortoEntity['SingleSignOnService'])) {
                $cortoEntity['SingleSignOnService'] = array();
            }

            $cortoEntity[] = array(
                'Binding'  => $service->binding,
                'Location' => $service->location,
            );
        }

        $cortoEntity['GuestQualifier'] = $entity->guestQualifier;

        if ($entity->schacHomeOrganization) {
            $cortoEntity['SchacHomeOrganization'] = $entity->schacHomeOrganization;
        }

        $cortoEntity['SpsWithoutConsent'] = $entity->getConsentSettings()->getSpEntityIdsWithoutConsent();
        $cortoEntity['isHidden'] = $entity->hidden;

        $cortoEntity['shibmd:scopes'] = array();
        foreach ($entity->shibMdScopes as $scope) {
            $cortoEntity['shibmd:scopes'][] = array(
                'allowed' => $scope->allowed,
                'regexp'  => $scope->regexp,
            );
        }

        return $cortoEntity;
    }

    /**
     * @param AbstractRole $entity
     * @param array $cortoEntity
     * @return array
     */
    private function translateCommon(AbstractRole $entity, array $cortoEntity)
    {
        $cortoEntity['EntityID'] = $entity->entityId;
        if ($entity->publishInEdugain) {
            $cortoEntity['PublishInEdugain'] = true;
        }
        if ($entity->publishInEduGainDate) {
            $cortoEntity['PublishInEdugainDate'] = $entity->publishInEduGainDate->format(DateTime::W3C);
        }
        if ($entity->disableScoping) {
            $cortoEntity['DisableScoping'] = true;
        }
        if ($entity->additionalLogging) {
            $cortoEntity['AdditionalLogging'] = $entity->additionalLogging;
        }
        $cortoEntity = $this->translateCommonCertificates($entity, $cortoEntity);
        if ($entity->logo) {
            $cortoEntity['Logo'] = array(
                'Height' => $entity->logo->height,
                'Width'  => $entity->logo->width,
                'URL'    => $entity->logo->url,
            );
        }
        if ($entity->requestsMustBeSigned) {
            $cortoEntity['AuthnRequestsSigned'] = $entity->requestsMustBeSigned;
        }
        if ($entity->nameIdFormat) {
            $cortoEntity['NameIDFormat'] = $entity->nameIdFormat;
        }
        $cortoEntity['NameIDFormats'] = $entity->supportedNameIdFormats;
        $cortoEntity['WorkflowState'] = $entity->workflowState;

        $cortoEntity = $this->translateContactPersons($entity, $cortoEntity);
        $cortoEntity = $this->translateSingleLogoutServices($entity, $cortoEntity);
        $cortoEntity = $this->translateOrganization($entity, $cortoEntity);
        $cortoEntity = $this->translateKeywords($entity, $cortoEntity);
        $cortoEntity = $this->translateName($entity, $cortoEntity);
        $cortoEntity = $this->translateDescription($entity, $cortoEntity);
        $cortoEntity = $this->translateDisplayName($entity, $cortoEntity);
        return $cortoEntity;
    }

    /**
     * @param AbstractRole $entity
     * @param array $cortoEntity
     * @return array
     */
    private function translateCommonCertificates(AbstractRole $entity, array $cortoEntity)
    {
        $cortoEntity['certificates'] = array();
        if (isset($entity->certificates[0])) {
            $cortoEntity['certificates']['public'] = $entity->certificates[0]->toPem();
        }
        if (isset($entity->certificates[1])) {
            $cortoEntity['certificates']['public-fallback'] = $entity->certificates[1]->toPem();
        }
        if (isset($entity->certificates[2])) {
            $cortoEntity['certificates']['public-fallback2'] = $entity->certificates[2]->toPem();
            return $cortoEntity;
        }
        return $cortoEntity;
    }

    /**
     * @param AbstractRole $entity
     * @param array $cortoEntity
     * @return mixed
     */
    private function translateOrganization(AbstractRole $entity, array $cortoEntity)
    {
        // @codingStandardsIgnoreStart
        if ($entity->organizationEn) {
            $this->mapMultilang($entity->organizationEn->name       , $cortoEntity, 'Organization', 'Name'       , 'en');
            $this->mapMultilang($entity->organizationEn->displayName, $cortoEntity, 'Organization', 'DisplayName', 'en');
            $this->mapMultilang($entity->organizationEn->url        , $cortoEntity, 'Organization', 'URL'        , 'en');
        }

        if ($entity->organizationNl) {
            $this->mapMultilang($entity->organizationNl->name       , $cortoEntity, 'Organization', 'Name'       , 'nl');
            $this->mapMultilang($entity->organizationNl->displayName, $cortoEntity, 'Organization', 'DisplayName', 'nl');
            $this->mapMultilang($entity->organizationNl->url        , $cortoEntity, 'Organization', 'URL'        , 'nl');
        }

        if ($entity->organizationPt) {
            $this->mapMultilang($entity->organizationPt->name       , $cortoEntity, 'Organization', 'Name'       , 'pt');
            $this->mapMultilang($entity->organizationPt->displayName, $cortoEntity, 'Organization', 'DisplayName', 'pt');
            $this->mapMultilang($entity->organizationPt->url        , $cortoEntity, 'Organization', 'URL'        , 'pt');
        }
        // @codingStandardsIgnoreEnd
        return $cortoEntity;
    }

    /**
     * @param AbstractRole $entity
     * @param array $cortoEntity
     * @return mixed
     */
    private function translateKeywords(AbstractRole $entity, array $cortoEntity)
    {
        if ($entity->keywordsNl) {
            $this->mapMultilang($entity->keywordsNl, $cortoEntity, 'Keywords', 'nl');
        }

        if ($entity->keywordsEn) {
            $this->mapMultilang($entity->keywordsNl, $cortoEntity, 'Keywords', 'en');
        }

        if ($entity->keywordsPt) {
            $this->mapMultilang($entity->keywordsPt, $cortoEntity, 'Keywords', 'pt');
        }
        return $cortoEntity;
    }

    /**
     * @param AbstractRole $entity
     * @param array $cortoEntity
     * @return mixed
     */
    private function translateName(AbstractRole $entity, array $cortoEntity)
    {
        if ($entity->nameNl) {
            $this->mapMultilang($entity->keywordsNl, $cortoEntity, 'Name', 'nl');
        }

        if ($entity->nameEn) {
            $this->mapMultilang($entity->keywordsNl, $cortoEntity, 'Name', 'en');
        }

        if ($entity->namePt) {
            $this->mapMultilang($entity->keywordsPt, $cortoEntity, 'Name', 'pt');
        }
        return $cortoEntity;
    }

    /**
     * @param AbstractRole $entity
     * @param array $cortoEntity
     * @return mixed
     */
    private function translateDescription(AbstractRole $entity, array $cortoEntity)
    {
        if ($entity->descriptionNl) {
            $this->mapMultilang($entity->keywordsNl, $cortoEntity, 'Description', 'nl');
        }

        if ($entity->descriptionEn) {
            $this->mapMultilang($entity->keywordsNl, $cortoEntity, 'Description', 'nl');
        }

        if ($entity->descriptionPt) {
            $this->mapMultilang($entity->keywordsPt, $cortoEntity, 'Description', 'pt');
        }
        return $cortoEntity;
    }

    /**
     * @param AbstractRole $entity
     * @param array $cortoEntity
     * @return array
     */
    private function translateDisplayName(AbstractRole $entity, array $cortoEntity)
    {
        if ($entity->displayNameNl) {
            $this->mapMultilang($entity->displayNameNl, $cortoEntity, 'DisplayName', 'nl');
        }

        if ($entity->displayNameEn) {
            $this->mapMultilang($entity->displayNameEn, $cortoEntity, 'DisplayName', 'en');
        }
        
        if ($entity->displayNamePt) {
            $this->mapMultilang($entity->displayNamePt, $cortoEntity, 'DisplayName', 'pt');
        }
        return $cortoEntity;
    }

    /**
     * @param AbstractRole $entity
     * @param array $cortoEntity
     * @return array
     */
    private function translateSingleLogoutServices(AbstractRole $entity, array $cortoEntity)
    {
        if (!$entity->singleLogoutService) {
            return $cortoEntity;
        }

        $service = $entity->singleLogoutService;

        if (!isset($cortoEntity['SingleLogoutService'])) {
            $cortoEntity['SingleLogoutService'] = array();
        }

        $cortoEntity['SingleLogoutService'][] = array(
            'Binding' => $service->binding,
            'Location' => $service->location,
        );

        return $cortoEntity;
    }


    /**
     * @param AbstractRole $entity
     * @param array $cortoEntity
     * @return array
     */
    private function translateContactPersons(AbstractRole $entity, array $cortoEntity)
    {
        $cortoEntity['ContactPersons'] = array();
        foreach ($entity->contactPersons as $contactPerson) {
            $cortoEntity['ContactPersons'][] = array(
                'ContactType' => $contactPerson->contactType,
                'EmailAddress' => $contactPerson->emailAddress,
                'TelephoneNumber' => $contactPerson->telephoneNumber,
                'GivenName' => $contactPerson->givenName,
                'SurName' => $contactPerson->surName,
            );
        }
        if (empty($cortoEntity['ContactPersons'])) {
            unset($cortoEntity['ContactPersons']);
            return $cortoEntity;
        }
        return $cortoEntity;
    }

    /**
     * Given:
     * $example = array();
     * mapMultilang(1, $example, 'a', 'b', 'c')
     * print_r($example);
     *
     * Gives:
     * Array (
     *  [a] => Array(
     *    [b] => Array(
     *      [c] => 1
     *     )
     *   )
     * )
     *
     * @param mixed $value
     * @param array $to
     */
    private function mapMultilang($value, array &$to)
    {
        $path = array_slice(func_get_args(), 2);
        while (count($path) >= 1) {
            $key = array_shift($path);
            if (!isset($to[$key])) {
                $to[$key] = array();
            }
            $to = &$to[$key];
        }
        $to = $value;
    }
}
