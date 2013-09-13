<?php

class VoManage_Controller_Index extends Default_Controller_LoggedIn
{
    protected $m_vo_id = '';
    protected $m_action = '';
    protected $m_allowedActions = array('Index', 'SelectVo', 'Add', 'Edit', 'Delete');
    
    /**
     * VO selection
     */
    public function selectVoAction() 
    {
        // selected VO is posted to itself, redirect to index
        $vo_id = trim(html_entity_decode($this->_getRequest()->getPostParameter("vo_id")));
        if (strlen($vo_id) > 0) {
            // go to attribute list
            $this->m_vo_id = $vo_id;
            $this->handleAction("Index");
            return;
        }
        // show VO list
        $service = new VoManage_Service_VirtualOrganisation();
        $this->resultSet = $service->fetchAll();
    }
    
    /**
     * List view of user attributes
     */
    public function indexAction($dispatch=TRUE)
    {
        //var_dump($this->_getRequest()->getPostParameters());
        
        // VO selected?
        $this->getSelectedVO();

        // dispatch
        if ($dispatch) $this->dispatchAction($this->_getRequest()->getPostParameters());
        
        // index action 
        $params = Surfnet_Search_Parameters::create();
        $params->addSearchParam('vo_id', $this->m_vo_id);
        $service = new VoManage_Service_VirtualOrganisationAttribute();
        $results = $service->listSearch($params);

        $resultSet = $results->getResults();
        $this->vo_id = $this->m_vo_id;
        
        $spList = $this->getServiceProviders();
        foreach($resultSet as $key => &$record) {
            $record['sp_entity_id_display'] = (isset($spList[$record['sp_entity_id']]) ? $spList[$record['sp_entity_id']]['id_display'] : '- unknown -');
        }
        // sort
        $resultSet = $this->sortByColumn($resultSet, 'sp_entity_id_display');
        // assign to view
        $this->resultSet = $resultSet;
    }

    public function sortByColumn($recordset, $column) {
        // put the column values into tmp
        $tmp = array();
        foreach ($recordset as $key => $record) {
            $tmp[$key] = (is_string($record[$column]) ? strtolower($record[$column]) : $record[$column]);
        }
        // multisort
        array_multisort($tmp, SORT_ASC, $recordset);
        // done!
        return $recordset;
    }
    
    public function addAction() 
    {
        $formAction = html_entity_decode($this->_getRequest()->getPostParameter("formaction"));
        $this->vo_id = $this->m_vo_id;
        if ($formAction == 'save') {
            $data = $this->_getRequest()->getPostParameters();
            $recordData = array_intersect_key($data, array_fill_keys(array('vo_id','sp_entity_id','user_id_pattern','attribute_name_saml','attribute_name_opensocial','attribute_value'), 0));
            $service = new VoManage_Service_VirtualOrganisationAttribute();
            $result = $service->save($recordData);
            $this->errors = $result->errors;
            $this->data = $data;
        } elseif ($formAction == 'cancel') {
            // return to index, ignore dispatching
            $this->handleAction('Index', array(FALSE));
        } else {
            // initial values
            $this->data = array('user_id_pattern' => 'urn:collab:person:*');
        }
        // form data
        $this->spList = $this->getServiceProviders();
    }

    public function editAction() {
        $formAction = html_entity_decode($this->_getRequest()->getPostParameter("formaction"));
        $this->vo_id = $this->m_vo_id;
        $service = new VoManage_Service_VirtualOrganisationAttribute();
        
        if ($formAction == '') {
            $this->data = $service->fetch($this->m_vo_id, intval($this->_getRequest()->getPostParameter('id')));
        } elseif ($formAction == 'save') {
            $data = $this->_getRequest()->getPostParameters();
            $recordData = array_intersect_key($data, array_fill_keys(array('id','vo_id','sp_entity_id','user_id_pattern','attribute_name_saml','attribute_name_opensocial','attribute_value'), 0));
            $result = $service->save($recordData);
            $this->errors = $result->errors;
            $this->data = $data;
        } elseif ($formAction == 'cancel') {
            // return to index, ignore dispatching
            $this->handleAction('Index', array(FALSE));
        }
        // form data
        $this->spList = $this->getServiceProviders();        
    }
    
    public function deleteAction() {
        $formAction = html_entity_decode($this->_getRequest()->getPostParameter("formaction"));
        $this->vo_id = $this->m_vo_id;
        $service = new VoManage_Service_VirtualOrganisationAttribute();
        $this->id = intval($this->_getRequest()->getPostParameter('id'));
        
        if ($formAction == '') {
            $record = $service->fetch($this->m_vo_id, $this->id);
            $spList = $this->getServiceProviders();
            $record['sp_entity_id_display'] = (isset($spList[$record['sp_entity_id']]) ? $spList[$record['sp_entity_id']]['id_display'] : '- unknown -');
            $this->data = $record;
        } elseif ($formAction == 'delete') {
            $data = $this->_getRequest()->getPostParameters();
            $result = $service->delete($this->m_vo_id, $this->id);
            $this->errors = $result->errors;
            $this->data = $data;
        } elseif ($formAction == 'cancel') {
            // return to index, ignore dispatching
            $this->handleAction('Index', array(FALSE));
        }
    }
    
    protected function dispatchAction($postVars) {
        $this->m_action = trim(html_entity_decode($this->_getRequest()->getPostParameter("action")));
        if (in_array($this->m_action, $this->m_allowedActions) && $this->m_action != 'Index') {
            $this->handleAction($this->m_action);
        }
        return;
    }
    
    /**
     * Get the selected VO from the POST variable, except when it is already known.
     * 
     * @return string
     */
    protected function getSelectedVO() {
        if (strlen($this->m_vo_id) == 0) {
            $vo_id = trim(html_entity_decode($this->_getRequest()->getPostParameter("vo_id")));
            if (strlen($vo_id) == 0 ) {
                $this->handleAction("SelectVo");
                return;
            }
            $this->m_vo_id = $vo_id;
        }
        // return the vo_id in case you want to assign it directly to something else
        return $this->m_vo_id;
    }

    protected function getServiceProviders() {
        $registry = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getServiceRegistryClient();
        $spList = $registry->getSpList();
        // get human-readable names where possible
        $lng = $this->getLanguage();
        foreach ($spList as $id => &$record) {
            $record['id_display'] = 
                (isset($record['displayName:'.$lng]) ? $record['displayName:'.$lng]: 
                    (isset($record['name']) ? $record['name']: 
                        (isset($record['description']) ? $record['description']: 
                            $id
                        )
                    )
                );
        }
        return $spList;
    }
    
    public function getLanguage()
    {
        $translator = EngineBlock_ApplicationSingleton::getInstance()->getTranslator()->getAdapter();
        return $translator->getLocale();
    }    
    
    protected function getOpenSocialPersonFields() {
        return array(
            // 11.1.1
            "person.anniversary",
            "person.birthday",
            "person.connected",
            "person.displayName",
            "person.gender",
            "person.id",
            "person.name",
            "person.nickname",
            "person.note",
            "person.preferredUsername",
            "person.published",
            "person.updated",
            "person.utcOffset",
            "person.aboutMe",
            "person.bodyType",
            "person.currentLocation",
            "person.drinker",
            "person.ethnicity",
            "person.fashion",
            "person.happiestWhen",
            "person.humor",
            "person.livingArrangement",
            "person.lookingFor",
            "person.profileSong",
            "person.profileVideo",
            "person.relationshipStatus",
            "person.religion",
            "person.romance",
            "person.scaredOf",
            "person.sexualOrientation",
            "person.smoker",
            "person.status",
            // 11.1.2
            "person.value",
            "person.type",
            "person.primary",
            "person.emails",
            "person.phoneNumbers",
            "person.ims",
            "person.photos",
            "person.tags",
            "person.relationships",
            "person.addresses",
            "person.organizations",
            "person.accounts",
            "person.appdata",
            "person.activities",
            "person.books",
            "person.cars",
            "person.children",
            "person.food",
            "person.heroes",
            "person.interests",
            "person.jobInterests",
            "person.languages",
            "person.languagesSpoken",
            "person.movies",
            "person.music",
            "person.pets",
            "person.politicalViews",
            "person.quotes",
            "person.sports",
            "person.turnOffs",
            "person.turnOns",
            "person.tvShows",
            // 11.1.3
            "person.name.formatted",
            "person.name.familyName",
            "person.name.givenName",
            "person.name.middleName",
            "person.name.honorificPrefix",
            "person.name.honorificSuffix",
            // 11.1.4
            "person.address.formatted",
            "person.address.streetAddress",
            "person.address.locality",
            "person.address.region",
            "person.address.postalCode",
            "person.address.country",
            "person.address.latitude",
            "person.address.longitude",
            "person.address.type",
            // 11.1.5
            "person.organization.name",
            "person.organization.department",
            "person.organization.title",
            "person.organization.type",
            "person.organization.startDate",
            "person.organization.endDate",
            "person.organization.location",
            "person.organization.description",
            // 11.1.6
            "person.account.domain",
            "person.account.username",
            "person.account.userid",
        );
    }
    
}