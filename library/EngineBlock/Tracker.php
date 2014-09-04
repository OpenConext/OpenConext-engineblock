<?php

class EngineBlock_Tracker
{
    public function trackLogin($spEntityMetadata, $idpEntityMetadata, $subjectId, $voContext, $keyId)
    {
        $request = EngineBlock_ApplicationSingleton::getInstance()->getInstance()->getHttpRequest();
        $db = $this->_getDbConnection();
        
        $stmt = $db->prepare("
            INSERT INTO log_logins
              (loginstamp, userid , spentityid , spentityname , idpentityid , idpentityname, useragent, voname, keyid)
            VALUES
              (now()     , :userid, :spentityid, :spentityname, :idpentityid, :idpentityname, :useragent, :voname, :keyid)"
        );
        $spEntityName  = (!empty($spEntityMetadata['Name']['en'])
            ? $spEntityMetadata['Name']['en']
            : $spEntityMetadata['EntityID']);
        $idpEntityName = (!empty($idpEntityMetadata['Name']['en'])
            ? $idpEntityMetadata['Name']['en']
            : $idpEntityMetadata['EntityID']);
        $stmt->bindParam('userid'       , $subjectId);
        $stmt->bindParam('spentityid'   , $spEntityMetadata['EntityID']);
        $stmt->bindParam('spentityname' , $spEntityName);
        $stmt->bindParam('idpentityid'  , $idpEntityMetadata['EntityID']);
        $stmt->bindParam('idpentityname', $idpEntityName);
        $stmt->bindParam('useragent'    , $request->getHeader('User-Agent'));
        $stmt->bindParam('voname'       , $voContext);
        $stmt->bindParam('keyid'        , $keyId);
        $stmt->execute();
    }
    
    protected function _getDbConnection()
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);  
    }
}
