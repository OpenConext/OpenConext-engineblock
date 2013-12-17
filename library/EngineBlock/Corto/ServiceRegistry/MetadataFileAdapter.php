<?php

class EngineBlock_Corto_ServiceRegistry_MockAdapter implements EngineBlock_Corto_ServiceRegistry_AdapterInterface
{
    /**
     * Given a list of (SAML2) entities, filter out the idps that are not allowed
     * for the given Service Provider.
     *
     * @param array $entities
     * @param string $spEntityId
     * @return array Filtered entities
     */
    public function filterEntitiesBySp(array $entities, $spEntityId)
    {
        // TODO: Implement filterEntitiesBySp() method.
    }

    /**
     * Given a list of (SAML2) entities, mark those idps that are not allowed
     * for the given Service Provider.
     *
     * @param array $entities
     * @param string $spEntityId
     * @return array the entities
     */
    public function markEntitiesBySp(array $entities, $spEntityId)
    {
        // TODO: Implement markEntitiesBySp() method.
    }

    /**
     * Given a list of (SAML2) entities, filter out the entities that do not have the requested workflow state
     *
     * @param array $entities
     * @param string $workflowState
     * @return array Filtered entities
     */
    public function filterEntitiesByWorkflowState(array $entities, $workflowState)
    {
        // TODO: Implement filterEntitiesByWorkflowState() method.
    }

    public function isConnectionAllowed($spEntityId, $idpEntityId)
    {
        // TODO: Implement isConnectionAllowed() method.
    }

    public function getRemoteMetaData()
    {
        // TODO: Implement getRemoteMetaData() method.
    }

    public function getEntity($entityId)
    {
        // TODO: Implement getEntity() method.
    }

    public function getArp($spEntityId)
    {
        // TODO: Implement getArp() method.
    }
}
