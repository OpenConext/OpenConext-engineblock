<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use Doctrine\ORM\EntityManager;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\MetadataRepository\DoctrineMetadataRepository;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateFactory;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateLazyProxy;
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
     * @param $entityId
     * @return ServiceProvider
     * @throws \Exception
     */
    private function getServiceProvider($entityId)
    {
        $entity = $this->repository->findServiceProviderByEntityId($entityId);

        if ($entity === null) {
            throw new \Exception(
                "Entity '{$entityId} was not registered with registerSp()"
            );
        }

        $this->entityManager->persist($entity);

        return $entity;
    }

    /**
     * @param $entityId
     * @return IdentityProvider
     * @throws \Exception
     */
    private function getIdentityProvider($entityId)
    {
        $entity = $this->repository->findIdentityProviderByEntityId($entityId);

        if ($entity === null) {
            throw new \Exception(
                "Entity '{$entityId} was not registered with registerIdp()"
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

        $sp->termsOfServiceUrl = 'http://welcome.vm.openconext.org';
        $sp->logo = new Logo('/images/placeholder.png');

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
        $this->getServiceProvider($entityId)->isConsentRequired = false;

        return $this;
    }

    public function setSpEntityWantsSignature($entityId)
    {
        $this->getServiceProvider($entityId)->requestsMustBeSigned = true;

        return $this;
    }

    public function setSpEntityTrustedProxy($entityId)
    {
        $this->getServiceProvider($entityId)->isTrustedProxy = true;

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
        $this->getServiceProvider($entityId)->policyEnforcementDecisionRequired = true;

        return $this;
    }

    public function requireAttributeAggregationForSp($entityId)
    {
        $this->getServiceProvider($entityId)->attributeAggregationRequired = true;

        return $this;
    }

    public function displayUnconnectedIdpsForSp($entityId, $displayUnconnected = true)
    {
        $this->getServiceProvider($entityId)->displayUnconnectedIdpsWayf = (bool) $displayUnconnected;

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

    public function setIdpLogo($entityId, $url)
    {
        $logo = new Logo($url);
        $logo->height = 100;
        $logo->width = 100;

        $this->getIdentityProvider($entityId)->logo = $logo;

        return $this;
    }
}
