<?php

class EngineBlock_AttributeProvider_VoManage implements EngineBlock_AttributeProvider_Interface
{
    protected $_voId;
    protected $_spEntityId;

    public function __construct($voId, $spEntityId)
    {
        $this->_voId        = $voId;
        $this->_spEntityId  = $spEntityId;
    }

    public function getStrategy()
    {
        return EngineBlock_AttributeProvider_Interface::STRATEGY_ADD;
    }

    public function getAttributes($subjectId, $format = self::FORMAT_SAML)
    {
        if ($format === self::FORMAT_SAML) {
            $attributeFieldName = 'attribute_name_saml';
        }
        else if ($format === self::FORMAT_OPENSOCIAL) {
            $attributeFieldName = 'attribute_name_opensocial';
        }
        else {
            throw new EngineBlock_Exception("Unknown format '$format' for VoManage Attribute Provider");
        }

        $query = "
        SELECT $attributeFieldName, attribute_value, user_id_pattern
        FROM virtual_organisation_attribute
        WHERE vo_id=? AND sp_entity_id = ?";
        $statement = $this->getDatabaseConnection()->prepare($query);
        $statement->execute(array($this->_voId, $this->_spEntityId));
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $attributes = array();
        foreach ($rows as $row) {
            $userIdRegex = $this->_convertPatternToRegex($row['user_id_pattern']);
            if (preg_match($userIdRegex, $subjectId)) {
                $attributes[$row[$attributeFieldName]][] = $row['attribute_value'];
            }
        }

        return $attributes;
    }

    protected function _convertPatternToRegex($pattern)
    {
        // Convert wildcards to something that does not contain regex characters
        $pattern = str_replace('*', '&SEARCH&', $pattern);
        // Escape the pattern for use in a regex
        $pattern = preg_quote($pattern);
        // Convert the wildcards to a regex pattern (.* = one or more characters of any kind)
        return '|' . str_replace('&SEARCH&', '.*', $pattern) . '|';
    }

    protected function getDatabaseConnection()
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);
    }
}
