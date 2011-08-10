<?php

class VoManage_Service_VirtualOrganisationAttribute
{
    protected $dbConnection;
    
    public function __construct() {
        $this->dbConnection = $this->getDatabaseConnection();
    }

    protected function getDatabaseConnection()
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);
    }
    
    public function listSearch(Surfnet_Search_Parameters $params)
    {
        return $this->_searchWhere($params);
    }

    protected function _searchWhere(Surfnet_Search_Parameters $params)
    {
        $searchParams = $params->getSearchParams();
        if (!isset($searchParams['vo_id'])) throw new EngineBlock_Exception("Invalid VO id!");
        
        // select VO Attribute record(s)
        $statement = $this->dbConnection->prepare("SELECT voa.* FROM virtual_organisation_attribute voa WHERE voa.vo_id = ?");
        $statement->execute(array($searchParams['vo_id']));
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        return new Surfnet_Search_Results($params, $rows, count($rows));
    }

    public function save($data)
    {
        $vo = new VoManage_Model_VirtualOrganisationAttribute();
        $vo->populate($data);
        $vo->errors = array();

        $form = new VoManage_Form_VirtualOrganisationAttribute();
        if (!$form->isValid($vo->toArray())) {
            $formErrors = $form->getErrors();
            $modelErrors = array();
            foreach ($formErrors as $fieldName => $fieldErrors) {
                foreach ($fieldErrors as $fieldError) {
                    switch ($fieldError) {
                        case 'isEmpty':
                            $error = 'Field is obligatory, but no input given';
                            break;
                        default:
                            $error = $fieldError;
                    }

                    if (!isset($modelErrors[$fieldName])) {
                        $modelErrors[$fieldName] = array();
                    }
                    $modelErrors[$fieldName][] = $error;
                }
            }
            $vo->errors = $modelErrors;
        }
        if (trim($form->getValue('attribute_name_saml')) == '' && trim($form->getValue('attribute_name_opensocial')) == '') {
            $vo->errors = array_merge($vo->errors, array('attribute_name_saml' => 'xor_error', 'attribute_name_opensocial' => 'xor_error'));
        }
        if (count($vo->errors) > 0) return $vo;
        
        $result = array();
        if (isset($data['id'])) {
            // Update
            $statement = $this->dbConnection->prepare("UPDATE virtual_organisation_attribute SET 
                                                       sp_entity_id = ?,
                                                       user_id_pattern = ?,
                                                       attribute_name_saml = ?,
                                                       attribute_name_opensocial = ?,
                                                       attribute_value = ?
                                                       WHERE id = ?");
            $statement->execute(array($vo->sp_entity_id, $vo->user_id_pattern, $vo->attribute_name_saml, $vo->attribute_name_opensocial, $vo->attribute_value, $vo->id));
        } else {
            // Insert
            $statement = $this->dbConnection->prepare("INSERT INTO virtual_organisation_attribute (vo_id, sp_entity_id, user_id_pattern, attribute_name_saml, attribute_name_opensocial, attribute_value) VALUES (?, ?, ?, ?, ?, ?)");
            $statement->execute(array($vo->vo_id, $vo->sp_entity_id, $vo->user_id_pattern, $vo->attribute_name_saml, $vo->attribute_name_opensocial, $vo->attribute_value));            
        }
        if ($statement->errorCode() != '00000') 
            $vo->errors = array('sql' => $statement->errorCode());
        return $vo;
    }

    public function fetch($vo_id, $id) {
        $statement = $this->dbConnection->prepare("SELECT voa.* FROM virtual_organisation_attribute voa WHERE voa.vo_id = ? AND voa.id = ?");
        $statement->execute(array($vo_id, intval($id)));
        return $statement->fetch(PDO::FETCH_ASSOC);
    }
    
    public function delete($vo_id, $id)
    {
        // create return object
        $vo = new VoManage_Model_VirtualOrganisationAttribute();
        // Delete
        $statement = $this->dbConnection->prepare("DELETE FROM virtual_organisation_attribute WHERE id = ? AND vo_id = ?");
        $statement->execute(array($id, $vo_id));            
        if ($statement->errorCode() != '00000') 
            $vo->errors = array('sql' => $statement->errorCode());
        return $vo;
    }

}