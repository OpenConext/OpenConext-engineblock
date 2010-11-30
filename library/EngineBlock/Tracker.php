<?php

class EngineBlock_Tracker
{
    public function __construct() 
    {
    }
    
    public function trackLogin($spEntityId, $idpEntityId, $subjectId) 
    {
        $db = $this->_getDbConnection();
        
        $stmt = $db->prepare("INSERT INTO log_logins (loginstamp, userid, spentityid, idpentityid) VALUES (now(), :userid, :spentityid, :idpentityid)");
        $stmt->bindParam("userid", $subjectId);
        $stmt->bindParam("spentityid", $spEntityId);
        $stmt->bindParam("idpentityid", $idpEntityId);
        
        $stmt->execute();
    }
    
    protected function _getDbConnection()
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);  
    }
}