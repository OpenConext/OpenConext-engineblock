<?php

class EngineBlock_Group_Acl_GroupProviderAcl
{
    /**
     * @var EngineBlock_Database_ConnectionFactory
     */
    protected $_factory = NULL;

    /**
     * Get all ServiceProviderGroupAcls (array where the key is the identifier
     * with as value an array of permissions
     *
     * @param $spEntityId the identifier of the Service Provider
     * @return array with the identifier being the group provider
     */
    public function getSpGroupAcls($spEntityId) {
        $db = $this->_getReadDatabase();
        $statement = $db->prepare('SELECT gp.identifier, spga.allow_groups, spga.allow_members FROM service_provider_group_acl spga, group_provider gp WHERE spga.group_provider_id = gp.id and spga.spentityid = ?');
        $statement->execute(array($spEntityId));
        $rows = $statement->fetchAll();
        $spGroupAcls = array();
        foreach ($rows as $row) {
            $spGroupAcls[$row['identifier']] = array(
                        'allow_groups'        => $row['allow_groups'],
                        'allow_members'       => $row['allow_members'],
            );
        }
        return $spGroupAcls;
    }

    protected function _getReadDatabase()
    {
        if ($this->_factory == NULL) {
            $this->_factory = new EngineBlock_Database_ConnectionFactory();
        }
        return $this->_factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);
    }
}
