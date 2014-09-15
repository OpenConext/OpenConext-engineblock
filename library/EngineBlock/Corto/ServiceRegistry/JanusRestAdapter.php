<?php

class EngineBlock_Corto_ServiceRegistry_JanusRestAdapter implements EngineBlock_Corto_ServiceRegistry_AdapterInterface
{
    /**
     * @var Janus_Client
     */
    protected $_serviceRegistry;

    public function __construct($serviceRegistry)
    {
        $this->_serviceRegistry = $serviceRegistry;
    }

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
        $allowedEntities = $this->_serviceRegistry->getAllowedIdps($spEntityId);
        foreach ($entities as $entityId => $entityData) {
            if (isset($entityData['SingleSignOnService'])) {
                // entity is an idp
                if (in_array($entityId, $allowedEntities)) {
                    $entities[$entityId]['Access'] = true;
                } else {
                    unset($entities[$entityId]);
                }
            }
        }
        return $entities;
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
        $allowedEntities = $this->_serviceRegistry->getAllowedIdps($spEntityId);
        foreach ($entities as $entityId => $entityData) {
            if (isset($entityData['SingleSignOnService'])) {
                // entity is an idp
                $entities[$entityId]['Access'] = in_array($entityId, $allowedEntities);
            }
        }
        return $entities;
    }

    /**
     * Given a list of (SAML2) entities, filter out the entities that do not have the requested workflow state
     *
     * @param array $entities
     * @param string $workflowState
     * @return array Filtered entities
     */
    public function filterEntitiesByWorkflowState(array $entities, $workflowState) {
        foreach ($entities as $entityId => $entityData) {
            if (!isset($entityData['WorkflowState']) || $entityData['WorkflowState'] !== $workflowState) {
                unset($entities[$entityId]);
            }
        }

        return $entities;
    }

    /**
     * Check if a given SP may contact a given Idp
     *
     * @param string $spEntityId
     * @param string $idpEntityId
     * @return bool
     */
    public function isConnectionAllowed($spEntityId, $idpEntityId)
    {
        return $this->_serviceRegistry->isConnectionAllowed($spEntityId, $idpEntityId);
    }

    /**
     * Get the metadata for all entities.
     *
     * @return array
     */
    public function getRemoteMetaData()
    {
        return $this->_getRemoteSPsMetaData() + $this->_getRemoteIdPsMetadata();
    }

    /**
     * Get the details for a given entity.
     *
     * @param string $entityId
     * @return array
     */
    public function getEntity($entityId)
    {
        return $this->_serviceRegistry->getEntity($entityId);
    }

    /**
     * Get the Attribute Release Policy for a given Service Provider
     *
     * @param string $spEntityId
     * @return array
     */
    public function getArp($spEntityId)
    {
        return $this->_serviceRegistry->getArp($spEntityId);
    }

    protected function _getRemoteIdPsMetadata()
    {
        $metadata = array();
        $mapper = new EngineBlock_Corto_Mapper_ServiceRegistry_KeyValue();

        $idPs = $this->_serviceRegistry->getIdpList();
        foreach ($idPs as $idPEntityId => $idP) {
            try {
                $idP = $mapper->fromKeyValue($idP);
                $idP['EntityID'] = $idPEntityId;
                $metadata[$idPEntityId] = $idP;
            } catch (Exception $e) {
                // Whoa, something went wrong trying to convert the SR entity to a Corto entity
                // We can't use this entity, but we can continue after we've reported
                // this serious error
                $application = EngineBlock_ApplicationSingleton::getInstance();
                $application->reportError($e);
                continue;
            }
        }
        return $metadata;
    }

    protected function _getRemoteSPsMetaData()
    {
        $metadata = array();
        $mapper = new EngineBlock_Corto_Mapper_ServiceRegistry_KeyValue();

        $sPs = $this->_serviceRegistry->getSPList();
        foreach ($sPs as $spEntityId => $sp) {
            try {
                $sp = $mapper->fromKeyValue($sp);
                $sp['EntityID'] = $spEntityId;
                $metadata[$spEntityId] = $sp;
            } catch (Exception $e) {
                // Whoa, something went wrong trying to convert the SR entity to a Corto entity
                // We can't use this entity, but we can continue after we've reported
                // this serious error
                $application = EngineBlock_ApplicationSingleton::getInstance();
                $application->reportError($e);
                continue;
            }
        }
        return $metadata;
    }
}
