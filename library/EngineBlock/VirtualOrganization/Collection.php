<?php
class EngineBlock_VirtualOrganization_Collection
{
    protected $_db;

    /**
     * @throws Exception
     * @return array $voList List of Virtual Organizations
     */
    public function load()
    {
        $db = $this->_getDbConnection();
        $stmt = $db->prepare("
            SELECT      vo.vo_id,
                        vo.vo_type
            FROM        `virtual_organisation` AS vo
        ");
        $stmt->execute(array($this->_id));

        $voList = array();
        while($voData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $voList[] = new EngineBlock_VirtualOrganization($voData['vo_id']);
        }

        if (empty($voList)) {
            throw new EngineBlock_Exception(
                "No Virtual Organizations found"
            );
        }

        return $voList;
    }

    /**
     * Creates db connection
     *
     * @todo this was copied from EngineBlock_VirtualOrganization, this should be reused somehow instead of copied
     * @return PDO $this->_db
     */
    protected function _getDbConnection()
    {
        if (isset($this->_db)) {
            return $this->_db;
        }

        $factory = new EngineBlock_Database_ConnectionFactory();
        $this->_db = $factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);

        return $this->_db;
    }
}
