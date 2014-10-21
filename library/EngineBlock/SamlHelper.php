<?php
/**
 * Temporary workaround
 *
 * @todo move methods message and entity objects *
 */
class EngineBlock_SamlHelper
{
    /**
     * Do we need to enable additional logging for any of the specified entities (SP or IdP).
     *
     * @param array $entities
     * @return bool
     */
    public static function doRemoteEntitiesRequireAdditionalLogging(array $entities) {
        foreach ($entities as $entity) {
            if (!empty($entity['AdditionalLogging'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the 'chain' of SP requesters, if available. Furthest removed SP first.
     *
     * @param array $spEntityMetadata
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param EngineBlock_Corto_ProxyServer $server
     * @return array
     */
    public static function getSpRequesterChain(
        array $spEntityMetadata,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        EngineBlock_Corto_ProxyServer $server
    ) {
        $chain = array($spEntityMetadata);

        $destinationSpMetadata = self::getDestinationSpMetadata($spEntityMetadata, $request, $server);
        if ($destinationSpMetadata !== $spEntityMetadata) {
            array_unshift($chain, $destinationSpMetadata);
        }

        return $chain;
    }

    /**
     * Get the 'Destination' SP metadata. Depending on the SP configuration, may be the SP metadata or it's requester.
     *
     * @param array $spEntityMetadata
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param EngineBlock_Corto_ProxyServer $server
     * @return array
     */
    public static function getDestinationSpMetadata(
        array $spEntityMetadata,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        EngineBlock_Corto_ProxyServer $server
    ) {
        if (!isset($spEntityMetadata['TrustedProxy']) || !$spEntityMetadata['TrustedProxy']) {
            return $spEntityMetadata;
        }

        if (!$request->wasSigned()) {
            return $spEntityMetadata;
        }

        // Requester IDs are appended to as they pass through a proxy, so we always want the last RequesterID
        // Note that this is not specified in the spec, but this is what we do and what SSP does.
        $requesterIds = $request->getRequesterIds();
        $lastRequesterEntityId = end($requesterIds);

        if ($lastRequesterEntityId && !$server->hasRemoteEntity($lastRequesterEntityId)) {
            return $spEntityMetadata;
        }

        return $server->getRemoteEntity($lastRequesterEntityId);
    }
}
