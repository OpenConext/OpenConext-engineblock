<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\MetadataRepository\InMemoryMetadataRepository;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateFactory;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateLazyProxy;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use SAML2\Constants;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ServiceRegistryFixture
{
    const TYPE_SP = 'sp';
    const TYPE_IDP = 'idp';

    protected $fixture;
    protected $directory;

    /**
     * @var IdentityProvider[]
     */
    protected $idpFixtures;

    /**
     * @var ServiceProvider[]
     */
    protected $spFixtures;

    public function __construct(AbstractDataStore $dataStore, $directory)
    {
        $this->fixture = $dataStore;
        $this->directory = $directory;

        $data = $dataStore->load();

        $this->idpFixtures = $data[self::TYPE_IDP] ?: [];
        $this->spFixtures = $data[self::TYPE_SP] ?: [];
    }

    /**
     * Create a metadata repository to read the fixture data.
     *
     * @return InMemoryMetadataRepository
     */
    public function createInMemoryMetadataRepository()
    {
        return new InMemoryMetadataRepository($this->idpFixtures, $this->spFixtures);
    }

    /**
     * @param $entityId
     * @return ServiceProvider
     * @throws \Exception
     */
    private function getServiceProvider($entityId)
    {
        $entity = null;

        if (isset($this->spFixtures[$entityId])) {
            $entity = $this->spFixtures[$entityId];
        }

        if ($entity === null) {
            throw new \Exception(
                "Entity '{$entityId} was not registered with registerSp()"
            );
        }

        return $entity;
    }

    /**
     * @param $entityId
     * @return IdentityProvider
     * @throws \Exception
     */
    private function getIdentityProvider($entityId)
    {
        $entity = null;

        if (isset($this->idpFixtures[$entityId])) {
            $entity = $this->idpFixtures[$entityId];
        }

        if ($entity === null) {
            throw new \Exception(
                "Entity '{$entityId} was not registered with registerIdp()"
            );
        }

        return $entity;
    }

    public function reset()
    {
        $this->idpFixtures = [];
        $this->spFixtures = [];

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

        $this->spFixtures[$entityId] = $sp;

        foreach ($this->idpFixtures as $entity) {
            if ($entity instanceof IdentityProvider) {
                $sp->allowedIdpEntityIds[] = $entity->entityId;
            }
        }

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

        $this->idpFixtures[$entityId] = $idp;

        foreach ($this->spFixtures as $entity) {
            if ($entity instanceof ServiceProvider) {
                $entity->allowedIdpEntityIds[] = $idp->entityId;
            }
        }

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

    public function allowAttributeValueForSp($entityId, $arpAttribute, $attributeValue, $attributeSource = null)
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

    public function save()
    {
        $this->fixture->save([
            self::TYPE_IDP => $this->idpFixtures,
            self::TYPE_SP => $this->spFixtures,
        ]);
    }

    public function __destruct()
    {
        $this->save();
    }
}
