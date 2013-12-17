<?php

interface EngineBlock_Corto_ServiceRegistry_AdapterInterface
{
    /**
     * Given a list of (SAML2) entities, filter out the idps that are not allowed
     * for the given Service Provider.
     *
     * @param array $entities
     * @param string $spEntityId
     * @return array Filtered entities
     */
    public function filterEntitiesBySp(array $entities, $spEntityId);

    /**
     * Given a list of (SAML2) entities, mark those idps that are not allowed
     * for the given Service Provider.
     *
     * @param array $entities
     * @param string $spEntityId
     * @return array the entities
     */
    public function markEntitiesBySp(array $entities, $spEntityId);

    /**
     * Given a list of (SAML2) entities, filter out the entities that do not have the requested workflow state
     *
     * @param array $entities
     * @param string $workflowState
     * @return array Filtered entities
     */
    public function filterEntitiesByWorkflowState(array $entities, $workflowState);

    /**
     * Check if a given SP may contact a given Idp
     *
     * @param string $spEntityId
     * @param string $idpEntityId
     * @return bool
     */
    public function isConnectionAllowed($spEntityId, $idpEntityId);

    /**
     * Get the metadata for all entities.
     *
     * @return array
     */
    public function getRemoteMetaData();

    /**
     * Get the details for a given entity.
     *
     * @param string $entityId
     * @return array
     */
    public function getEntity($entityId);

    /**
     * Get the Attribute Release Policy for a given Service Provider
     *
     * @param string $spEntityId
     * @return array
     */
    public function getArp($spEntityId);
}
