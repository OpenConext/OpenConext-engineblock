<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class EngineBlock_Tracker
{
    /**
     * @param array $spEntityMetadata
     * @param array $idpEntityMetadata
     * @param string $subjectId
     * @param string $voContext
     * @return array
     */
    public function parseLogin($spEntityMetadata, $idpEntityMetadata, $subjectId, $voContext)
    {
        $spEntityName  = (isset($spEntityMetadata['Name']['en']) && !empty($spEntityMetadata['Name']['en']) ? $spEntityMetadata['Name']['en'] : $spEntityMetadata['EntityId']);
        $idpEntityName = (isset($idpEntityMetadata['Name']['en']) && !empty($idpEntityMetadata['Name']['en']) ? $idpEntityMetadata['Name']['en'] : $idpEntityMetadata['EntityId']);
        $request = EngineBlock_ApplicationSingleton::getInstance()->getInstance()->getHttpRequest();
        $loginStamp = new DateTime();
        $params = array(
            'loginstamp' => $loginStamp->format(DATE_ISO8601),
            'userid' => $subjectId,
            'spentityid' => $spEntityMetadata['EntityId'],
            'spentityname' => $spEntityName,
            'idpentityid' => $idpEntityMetadata['EntityId'],
            'idpentityname' => $idpEntityName,
            'useragent' => $request->getHeader('User-Agent'),
            'voname' => $voContext,
        );

        return $params;
    }

    /**
     * Stores login in database
     *
     * @param array $params
     */
    public function storeInDatabase(array $params)
    {
        $db = $this->_getDbConnection();
        $stmt = $db->prepare("
            INSERT INTO log_logins (loginstamp, userid , spentityid , spentityname , idpentityid , idpentityname, useragent, voname)
            VALUES                 (:loginstamp, :userid, :spentityid, :spentityname, :idpentityid, :idpentityname, :useragent, :voname)"
        );
        foreach($params as $paramName => $paramValue) {
            $stmt->bindParam($paramName, $params[$paramName]);
        }
        if ($stmt->execute())
        {
            return true;
        }
    }
    
    protected function _getDbConnection()
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);  
    }
}
