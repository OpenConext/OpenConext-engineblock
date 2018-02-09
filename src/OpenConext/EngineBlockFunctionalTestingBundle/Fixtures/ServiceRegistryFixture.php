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
    const TYPE_SP = 1;
    const TYPE_IDP = 2;

    protected $fixture;
    protected $directory;

    /**
     * @var AbstractRole[]
     */
    protected $data;

    public function __construct(AbstractDataStore $dataStore, $directory)
    {
        $this->fixture = $dataStore;
        $this->directory = $directory;

        $this->data = $dataStore->load();
    }

    /**
     * Create a metadata repository to read the fixture data.
     *
     * @return InMemoryMetadataRepository
     */
    public function createInMemoryMetadataRepository()
    {
        $idps = $this->data[self::TYPE_IDP] ?: [];
        $sps = $this->data[self::TYPE_SP] ?: [];

        return new InMemoryMetadataRepository($idps, $sps);
    }

    /**
     * @param $entityId
     * @param $role
     * @return AbstractRole
     * @throws \Exception
     */
    private function getEntity($entityId, $role)
    {
        $entity = null;

        if (isset($this->data[$role][$entityId])) {
            $entity = $this->data[$role][$entityId];
        }

        if (!$entity instanceof AbstractRole) {
            throw new \Exception(
                "Entity '{$entityId} was not registered with registerSp() or registerIdp()"
            );
        }

        return $entity;
    }

    public function reset()
    {
        $this->data = [];

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

        $this->data[self::TYPE_SP][$entityId] = $sp;

        if (isset($this->data[self::TYPE_IDP])) {
            foreach ($this->data[self::TYPE_IDP] as $entity) {
                if ($entity instanceof IdentityProvider) {
                    $sp->allowedIdpEntityIds[] = $entity->entityId;
                }
            }
        }

        return $this;
    }

    public function spRequiresPolicyEnforcementDecisionForSp($entityId)
    {
        $this->getEntity($entityId, self::TYPE_SP)->policyEnforcementDecisionRequired = true;

        return $this;
    }

    public function requireAttributeAggregationForSp($entityId)
    {
        $this->getEntity($entityId, self::TYPE_SP)->attributeAggregationRequired = true;

        return $this;
    }

    public function displayUnconnectedIdpsForSp($entityId, $displayUnconnected = true)
    {
        $this->getEntity($entityId, self::TYPE_SP)->displayUnconnectedIdpsWayf = (bool) $displayUnconnected;

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

        $this->data[self::TYPE_IDP][$entityId] = $idp;

        if (isset($this->data[self::TYPE_SP])) {
            foreach ($this->data[self::TYPE_SP] as $entity) {
                if ($entity instanceof ServiceProvider) {
                    $entity->allowedIdpEntityIds[] = $idp->entityId;
                }
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

    public function remove($entityId)
    {
        unset($this->data[self::TYPE_SP][$entityId]);

        return $this;
    }

    public function setSpEntityNoConsent($entityId)
    {
        $this->getEntity($entityId, self::TYPE_SP)->isConsentRequired = false;

        return $this;
    }

    public function setSpEntityWantsSignature($entityId)
    {
        $this->getEntity($entityId, self::TYPE_SP)->requestsMustBeSigned = true;

        return $this;
    }

    public function setSpEntityTrustedProxy($entityId)
    {
        $this->getEntity($entityId, self::TYPE_SP)->isTrustedProxy = true;

        return $this;
    }

    public function setSpEntityManipulation($entityId, $manipulation)
    {
        $this->getEntity($entityId, self::TYPE_SP)->manipulation = $manipulation;

        return $this;
    }

    public function setSpEntityNameIdFormatUnspecified($entityId)
    {
        $this->getEntity($entityId, self::TYPE_SP)->nameIdFormat = Constants::NAMEID_UNSPECIFIED;

        return $this;
    }

    public function setSpEntityNameIdFormatPersistent($entityId)
    {
        $this->getEntity($entityId, self::TYPE_SP)->nameIdFormat = Constants::NAMEID_PERSISTENT;

        return $this;
    }

    public function setSpEntityNameIdFormatTransient($entityId)
    {
        $this->getEntity($entityId, self::TYPE_SP)->nameIdFormat = Constants::NAMEID_TRANSIENT;

        return $this;
    }

    public function disconnectSp($spEntityId, $idpEntityId)
    {
        $sp = $this->getEntity($spEntityId, self::TYPE_SP);

        $index = array_search($idpEntityId, $sp->allowedIdpEntityIds);

        if ($index !== false) {
            unset($sp->allowedIdpEntityIds[$index]);
        }

        return $this;
    }

    public function allowNoAttributeValuesForSp($entityId)
    {
        $this->getEntity($entityId, self::TYPE_SP)->attributeReleasePolicy = new AttributeReleasePolicy(array());

        return $this;
    }

    public function allowAttributeValueForSp($entityId, $arpAttribute, $attributeValue, $attributeSource = null)
    {
        /** @var AttributeReleasePolicy $arp */
        $arp = $this->getEntity($entityId, self::TYPE_SP)->attributeReleasePolicy;

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

        $this->getEntity($entityId, self::TYPE_SP)->attributeReleasePolicy = new AttributeReleasePolicy($rules);

        return $this;
    }

    public function setSpWorkflowState($entityId, $workflowState)
    {
        $this->getEntity($entityId, self::TYPE_SP)->workflowState = $workflowState;

        return $this;
    }

    public function setIdpLogo($entityId, $url)
    {
        $logo = new Logo($url);
        $logo->height = 100;
        $logo->width = 100;

        $this->getEntity($entityId, self::TYPE_IDP)->logo = $logo;

        return $this;
    }

    public function save()
    {
        $this->fixture->save($this->data);
    }

    public function __destruct()
    {
        $this->save();
    }
}
