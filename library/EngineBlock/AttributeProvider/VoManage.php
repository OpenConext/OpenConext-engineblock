<?php

class EngineBlock_AttributeProvider_VoManage implements EngineBlock_AttributeProvider_Interface
{
    protected $_voId;
    protected $_idpEntityId;
    protected $_spEntityId;

    public function __construct($voId, $idpEntityId, $spEntityId)
    {
        $this->_voId        = $voId;
        $this->_idpEntityId = $idpEntityId;
        $this->_spEntityId  = $spEntityId;
    }

    public function getAttributes($subjectId)
    {
        $attributes = array();

        $query = "SELECT * FROM virtual_organisation_attributes WHERE vo_id=? AND sp_entity_id = ?";
        $statement = $this->getDatabaseConnection()->prepare($query);
        $statement->execute(array($this->_voId, $this->_spEntityId));
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $userIdPattern = $row['user_id_pattern'];
            $userIdRegex = str_replace('&SEARCH&', '.*', preg_quote(str_replace('*', '&SEARCH&', $userIdPattern)));
            if (preg_match($userIdRegex, $subjectId)) {
                $attributes[$row['attribute_name']][] = $row['attribute_value'];
            }
        }
        return $attributes;
    }

    protected function getDatabaseConnection()
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);
    }
}