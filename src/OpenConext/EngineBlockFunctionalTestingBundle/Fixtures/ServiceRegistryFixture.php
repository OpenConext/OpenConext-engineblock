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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use Doctrine\ORM\EntityManager;
use Exception;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ConsentSettings;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\MduiElement;
use OpenConext\EngineBlock\Metadata\MetadataRepository\DoctrineMetadataRepository;
use OpenConext\EngineBlock\Metadata\MfaEntityCollection;
use OpenConext\EngineBlock\Metadata\MultilingualElement;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\StepupConnections;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateFactory;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateLazyProxy;
use ReflectionClass;
use SAML2\Constants;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ServiceRegistryFixture
{
    const TYPE_SP = 'sp';
    const TYPE_IDP = 'idp';

    /**
     * @var DoctrineMetadataRepository
     */
    private $repository;

    /**
     * Used strictly to write fixture data to the test database and reset the state of the database between tests.
     * @var EntityManager
     */
    private $entityManager;


    public function __construct(DoctrineMetadataRepository $repository, EntityManager $em)
    {
        $this->repository = $repository;
        $this->entityManager = $em;
    }

    public function __destruct()
    {
        $this->save();
    }

    /**
     * @param string $entityId
     * @return ServiceProvider
     * @throws Exception
     */
    private function getServiceProvider(string $entityId)
    {
        $entity = $this->repository->findServiceProviderByEntityId($entityId);

        if ($entity === null) {
            throw new Exception(
                sprintf('Entity "%s" was not registered with registerSp()', $entityId)
            );
        }

        $this->entityManager->persist($entity);

        return $entity;
    }

    /**
     * @param $entityId
     * @return IdentityProvider
     * @throws Exception
     */
    private function getIdentityProvider($entityId)
    {
        $entity = $this->repository->findIdentityProviderByEntityId($entityId);

        if ($entity === null) {
            throw new Exception(
                sprintf('Entity "%s" was not registered with registerIdp()', $entityId)
            );
        }

        $this->entityManager->persist($entity);

        return $entity;
    }

    public function save()
    {
        $this->entityManager->flush();
    }

    public function reset()
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->delete('sso_provider_roles_eb5')
            ->execute();

        return $this;
    }

    public function remove($entityId, $role)
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->delete('sso_provider_roles_eb5', 'roles')
            ->where('roles.entity_id = :entityId')
            ->andWhere('roles.type = :type')
            ->setParameter('entityId', $entityId)
            ->setParameter('type', $role)
            ->execute();

        return $this;
    }

    public function registerSp($name, $entityId, $acsLocation, $certData = '')
    {
        $sp = new ServiceProvider($entityId);
        $sp->workflowState = 'prodaccepted';

        $this->assembleEntityName($sp, $name);
        $this->assembleCertificateData($sp, $certData);

        $sp->assertionConsumerServices[] = new IndexedService(
            $acsLocation,
            Constants::BINDING_HTTP_POST,
            0
        );

        $this->setCoin($sp, 'termsOfServiceUrl', 'http://welcome.vm.openconext.org');
        $sp->getMdui()->setLogo(new Logo('/images/placeholder.png'));


        // The repository does not allow us to retrieve all SP's for good reason. In functional testing mode the total
        // number of SP's should always be limited.
        $idpEntityIDQuery = <<<QUERY
        SELECT `entity_id`
        FROM `sso_provider_roles_eb5`
        WHERE `type` = 'idp'
QUERY;
        $query = $this->entityManager->getConnection()->prepare($idpEntityIDQuery);
        $query->execute();
        $idps = $query->fetchAll();

        foreach ($idps as $idpEntityId) {
            $idp = $this->repository->findIdentityProviderByEntityId($idpEntityId['entity_id']);
            $sp->allowedIdpEntityIds[] = $idp->entityId;
        }

        $this->entityManager->persist($sp);

        return $this;
    }

    public function registerIdp($name, $entityId, $ssoLocation, $certData = '')
    {
        $idp = new IdentityProvider($entityId);
        $this->assembleEntityName($idp, $name);
        $this->assembleCertificateData($idp, $certData);

        $idp->singleSignOnServices[] = new Service($ssoLocation, Constants::BINDING_HTTP_POST);
        $idp->singleSignOnServices[] = new Service($ssoLocation, Constants::BINDING_HTTP_REDIRECT);

        $contact = new ContactPerson('support');
        $contact->emailAddress = 'support@openconext.org';
        $contact->telephoneNumber = '+31612345678';

        $idp->contactPersons[] = $contact;

        // The repository does not allow us to retrieve all SP's for good reason. In functional testing mode the total
        // number of SP's should always be limited.
        $spEntityIDQuery = <<<QUERY
        SELECT `entity_id`
        FROM `sso_provider_roles_eb5`
        WHERE `type` = 'sp'
QUERY;
        $query = $this->entityManager->getConnection()->prepare($spEntityIDQuery);
        $query->execute();
        $sps = $query->fetchAll();

        foreach ($sps as $spEntityId) {
            $sp = $this->repository->findServiceProviderByEntityId($spEntityId['entity_id']);
            $sp->allowedIdpEntityIds[] = $idp->entityId;
            $this->entityManager->persist($sp);
        }

        $this->entityManager->persist($idp);

        return $this;
    }

    /**
     * @param AbstractRole $entity
     * @param string $name
     */
    private function assembleEntityName(AbstractRole $entity, $name)
    {
        $entity->nameNl = $name;
        $entity->nameEn = $name;
        $entity->displayNameNl = $name;
        $entity->displayNameEn = $name;
    }

    /**
     * @param AbstractRole $entity
     * @param string $certData
     */
    private function assembleCertificateData(AbstractRole $entity, $certData)
    {
        if (!empty($certData)) {
            $entity->certificates[] = new X509CertificateLazyProxy(
                new X509CertificateFactory(),
                $certData
            );
        }
    }

    public function setSpEntityNoConsent($entityId)
    {
        $this->setCoin($this->getServiceProvider($entityId), 'isConsentRequired', false);

        return $this;
    }

    public function setSpEntityWantsSignature($entityId)
    {
        $this->getServiceProvider($entityId)->requestsMustBeSigned = true;

        return $this;
    }

    public function setSpSignRepsones($entityId, $state = true)
    {
        $this->setCoin($this->getServiceProvider($entityId), 'signResponse', (bool)$state);

        return $this;
    }

    public function setSpEntityTrustedProxy($entityId)
    {
        $this->setCoin($this->getServiceProvider($entityId), 'isTrustedProxy', true);

        return $this;
    }

    public function setSpEntityRequesterIdRequired($entityId)
    {
        $this->setCoin($this->getServiceProvider($entityId), 'requesteridRequired', true);

        return $this;
    }

    public function setSpEntityManipulation($entityId, $manipulation)
    {
        $this->getServiceProvider($entityId)->manipulation = $manipulation;

        return $this;
    }

    public function setSpEntityNameIdFormatUnspecified($entityId)
    {
        $this->getServiceProvider($entityId)->nameIdFormat = Constants::NAMEID_UNSPECIFIED;

        return $this;
    }

    public function setSpEntityNameIdFormatPersistent($entityId)
    {
        $this->getServiceProvider($entityId)->nameIdFormat = Constants::NAMEID_PERSISTENT;

        return $this;
    }

    public function setSpEntityNameIdFormatTransient($entityId)
    {
        $this->getServiceProvider($entityId)->nameIdFormat = Constants::NAMEID_TRANSIENT;

        return $this;
    }

    public function spRequiresPolicyEnforcementDecisionForSp($entityId)
    {
        $this->setCoin($this->getServiceProvider($entityId), 'policyEnforcementDecisionRequired', true);

        return $this;
    }

    public function requireAttributeAggregationForSp($entityId)
    {
        $arp = $this->getServiceProvider($entityId)->attributeReleasePolicy;

        $rules = [];
        if ($arp !== null) {
            $rules = $arp->getAttributeRules();
        }

        $rules['test'] = [ 'source' => 'no-idp', 'value' => 'test' ];

        $this->getServiceProvider($entityId)->attributeReleasePolicy = new AttributeReleasePolicy($rules);

        return $this;
    }

    public function requireASignedResponse($entityId)
    {
        $this->setCoin($this->getServiceProvider($entityId), 'signResponse', true);
    }

    public function displayUnconnectedIdpsForSp($entityId, $displayUnconnected = true)
    {
        $this->setCoin($this->getServiceProvider($entityId), 'displayUnconnectedIdpsWayf', (bool) $displayUnconnected);

        return $this;
    }

    public function disconnectSp($spEntityId, $idpEntityId)
    {
        $sp = $this->getServiceProvider($spEntityId);

        $index = array_search($idpEntityId, $sp->allowedIdpEntityIds);

        if ($index !== false) {
            unset($sp->allowedIdpEntityIds[$index]);
        }

        return $this;
    }

    public function allowNoAttributeValuesForSp($entityId)
    {
        $this->getServiceProvider($entityId)->attributeReleasePolicy = new AttributeReleasePolicy(array());

        return $this;
    }

    public function allowAttributeValueForSp($entityId, $arpAttribute, $attributeValue, $attributeSource = null, $motivation = null)
    {
        /** @var AttributeReleasePolicy $arp */
        $arp = $this->getServiceProvider($entityId)->attributeReleasePolicy;

        $rules = [];

        if (!empty($arp)) {
            $rules = $arp->getAttributeRules();
        }

        if (empty($attributeSource)) {
            $attributeSource = 'idp';
        }

        $arpRule = [
            'value' => $attributeValue,
            'source' => $attributeSource,
            'motivation' => $motivation,
        ];

        $rules[$arpAttribute] = [$arpRule];

        $this->getServiceProvider($entityId)->attributeReleasePolicy = new AttributeReleasePolicy($rules);

        return $this;
    }

    public function setSpWorkflowState($entityId, $workflowState)
    {
        $this->getServiceProvider($entityId)->workflowState = $workflowState;

        return $this;
    }

    public function setSpStepupRequireLoa($entityId, $requiredLoa)
    {
        $this->setCoin($this->getServiceProvider($entityId), 'stepupRequireLoa', $requiredLoa);

        return $this;
    }

    public function setStepupForceAuthn($entityId, $isForceAuthn)
    {
        $this->setCoin($this->getServiceProvider($entityId), 'stepupForceAuthn', $isForceAuthn);

        return $this;
    }

    public function setSpStepupAllowNoToken($entityId)
    {
        $this->setCoin($this->getServiceProvider($entityId), 'stepupAllowNoToken', true);

        return $this;
    }

    public function setIdpStepupConnections($entityId, array $spLoaMapping)
    {
        $connections = new StepupConnections($spLoaMapping);
        $this->setCoin($this->getIdentityProvider($entityId), 'stepupConnections', $connections);

        return $this;
    }

    public function setMfaEntities($entityId, array $mfaEntities)
    {
        $mfaEntities = MfaEntityCollection::fromMetadataPush($mfaEntities);
        $this->setCoin($this->getIdentityProvider($entityId), 'mfaEntities', $mfaEntities);

        return $this;
    }

    public function setIdpLogo($entityId, $url)
    {
        $logo = new Logo($url);
        $logo->height = 100;
        $logo->width = 100;

        $idp = $this->getIdentityProvider($entityId);
        $this->setMdui($idp, 'Logo', $logo);

        return $this;
    }


    public function setHidden($entityId)
    {
        $idp = $this->getIdentityProvider($entityId);

        $this->setCoin($idp, 'hidden', true);

        return $this;
    }

    public function setIdpEntityWantsSignature($entityId)
    {
        $this->getIdentityProvider($entityId)->requestsMustBeSigned = true;

        return $this;
    }


    public function setIdpScope($entityId, $scope, $regexp = false)
    {
        $shibdScope = new ShibMdScope();
        $shibdScope->allowed = $scope;
        $shibdScope->regexp = $regexp;

        $this->getIdentityProvider($entityId)->shibMdScopes = [$shibdScope];

        return $this;
    }

    public function setConsentSettings($idpEntityId, $spEntityId, $consentType, $message = '')
    {
        $this->getIdentityProvider($idpEntityId)->setConsentSettings(
            new ConsentSettings([
                [
                    'name' => $spEntityId,
                    'type' => $consentType,
                    'explanation:en' => $message,
                    'explanation:nl' => $message,
                ]
            ])
        );

        return $this;
    }

    private function setCoin(AbstractRole $role, $key, $name)
    {
        $jsonData = $role->getCoins()->toJson();
        $data = json_decode($jsonData, true);
        $data[$key] = $name;
        $jsonData = json_encode($data);

        $coins = Coins::fromJson($jsonData);

        $object = new ReflectionClass($role);

        $property = $object->getProperty('coins');
        $property->setAccessible(true);
        $property->setValue($role, $coins);
    }
    private function setMdui(AbstractRole $role, string $elementName, MultilingualElement $value)
    {
        $jsonData = $role->getMdui()->toJson();
        $data = json_decode($jsonData, true);
        $data[$elementName] = $value->jsonSerialize();

        $jsonData = json_encode($data);

        $mdui = Mdui::fromJson($jsonData);
        $object = new ReflectionClass($role);

        $property = $object->getProperty('mdui');
        $property->setAccessible(true);
        $property->setValue($role, $mdui);
    }
}
