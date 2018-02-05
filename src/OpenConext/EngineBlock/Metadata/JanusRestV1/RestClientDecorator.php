<?php

namespace OpenConext\EngineBlock\Metadata\JanusRestV1;

/**
 * RestClientDecorator adds methods to retrieve metadata for a single entity / idp or sp.
 *
 * Warning: Using this without some kind of cache will lead to horrible performance.
 *
 * @package OpenConext\EngineBlock\Metadata\JanusRestV1
 */
class RestClientDecorator implements RestClientInterface
{
    /**
     * @var RestClientInterface
     */
    private $client;

    /**
     * @param RestClientInterface $client
     */
    public function __construct(RestClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param $entityId
     * @return null
     */
    public function findMetadataByEntityId($entityId)
    {
        $serviceProvider = $this->findServiceProviderMetadataByEntityId($entityId);
        if ($serviceProvider) {
            return $serviceProvider;
        }

        $identityProvider = $this->findIdentityProviderMetadataByEntityId($entityId);
        if ($identityProvider) {
            return $identityProvider;
        }

        return null;
    }

    /**
     * @param $entityId
     * @return null
     */
    public function findServiceProviderMetadataByEntityId($entityId)
    {
        $serviceProvidersMetadata = $this->client->getSpList();

        if (!isset($serviceProvidersMetadata[$entityId])) {
            return null;
        }

        return $serviceProvidersMetadata[$entityId];
    }

    /**
     * @param $entityId
     * @return null
     */
    public function findIdentityProviderMetadataByEntityId($entityId)
    {
        $identityProvidersMetadata = $this->client->getIdpList();

        if (!isset($identityProvidersMetadata[$entityId])) {
            return null;
        }

        return $identityProvidersMetadata[$entityId];
    }

    /**
     * Retrieve the allowed IDPs for an SP. The SP is only
     * allowed to make connections to the retrieved IDP's.
     *
     * @param string $spEntityId the URN of the SP entity.
     * @return array containing the URN's of the IDP's that this SP is allowed to make a connection to.
     */
    public function getAllowedIdps($spEntityId)
    {
        return $this->client->getAllowedIdps($spEntityId);
    }

    /**
     * Get full information for a given entity.
     *
     * @param $entityId
     * @return array
     */
    public function getEntity($entityId)
    {
        return $this->client->getEntity($entityId);
    }

    /**
     * Retrieve a list of metadata values of all available
     * IDP entities.
     * @param array $keys An array of keys to retrieve. Retrieves
     *                    all available keys if omited or empty
     * @param String $forSpEntityId An optional identifier of an SP
     *               If present, idplist will return a list of only the
     *               idps that this sp is allowed to authenticate against.
     * @return array An associative array of values, indexed by IDP
     *               identifier. Each value is another associative
     *               array with key/value pairs containing the metadata.
     */
    public function getIdpList($keys = array(), $forSpEntityId = null)
    {
        return $this->client->getIdpList($keys, $forSpEntityId);
    }

    /**
     * Find entities based on metadata.
     *
     * Finds the identifiers (URNS) of all SPs/IDPs that match a certain
     * metadata value. The rest webservice that's behind this call supports
     * regular expressions in the metadata values in its database. So you
     * can pass "www.google.com" as value to this function and match
     * entities that have '.*\.google\.com in their url:en metadata field.
     *
     * @param string $key The key you want to match against
     * @param string $value The value you want to match against
     * @return array An array of URNS of entities that match the request.
     */
    public function findIdentifiersByMetadata($key, $value)
    {
        return $this->client->findIdentifiersByMetadata($key, $value);
    }

    /**
     * Retrieve a list of metadata values of all available
     * SP entities.
     * @param array $keys An array of keys to retrieve. Retrieves
     *                    all available keys if omited or empty
     * @return array An associative array of values, indexed by SP
     *               identifier. Each value is another associative
     *               array with key/value pairs containing the metadata.
     */
    public function getSpList($keys = array())
    {
        return $this->client->getSpList($keys);
    }
}
