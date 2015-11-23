<?php

class EngineBlock_VirtualOrganization
{
    protected $_data;
    protected $_db;

    public function __construct($id)
    {
        $this->_load($id);
    }

    public function getType()
    {
        return $this->_data['vo_type'];
    }

    public function getGroupsIdentifiers()
    {
        $db = $this->_getDbConnection();
        $stmt = $db->prepare(
            "SELECT vog.group_id
             FROM `virtual_organisation_group` vog
             WHERE vog.vo_id = ?"
        );
        $stmt->execute(array($this->_getId()));
        $groupRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $groups = array();
        foreach ($groupRecords as $groupRecord) {
            $groups[] = $groupRecord['group_id'];
        }
        return $groups;
    }

    public function getIdpIdentifiers()
    {
        $db = $this->_getDbConnection();
        $stmt = $db->prepare(
            "SELECT voi.idp_id
             FROM `virtual_organisation_idp` voi
             WHERE voi.vo_id = ?"
        );
        $stmt->execute(array($this->_getId()));
        $idpRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $idps = array();
        foreach ($idpRecords as $idpRecord) {
            $idps[] = $idpRecord['idp_id'];
        }
        return $idps;
    }

    protected function _load($id)
    {
        $db = $this->_getDbConnection();
        $stmt = $db->prepare(
            "SELECT vo.vo_id, vo.vo_type
             FROM `virtual_organisation` vo
             WHERE vo.vo_id = ?
             LIMIT 0,1"
        );
        $stmt->execute(array($id));
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (empty($data)) {
            throw new EngineBlock_VirtualOrganization_VoIdentifierNotFoundException(
                "No data found for Virtual Organization '{$id}'"
            );
        }

        $this->_data = $data;
    }

    protected function _getDbConnection()
    {
        if (isset($this->_db)) {
            return $this->_db;
        }

        $factory = new EngineBlock_Database_ConnectionFactory();
        $this->_db = $factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);

        return $this->_db;
    }

    /**
     * @return id
     */
    protected function _getId()
    {
        return $this->_data['vo_id'];
    }
}