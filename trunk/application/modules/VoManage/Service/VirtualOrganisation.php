<?php

class VoManage_Service_VirtualOrganisation
{
    protected $dbConnection;
    
    public function __construct() {
        $this->dbConnection = $this->getDatabaseConnection();
    }

    protected function getDatabaseConnection()
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);
    }

    public function fetchAll() {
        $voRecords = array();
        $query = "SELECT vo.vo_id, vo.vo_type FROM virtual_organisation vo ORDER BY vo.vo_id";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute(array());
        $voRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $voRecords;
    }

}
