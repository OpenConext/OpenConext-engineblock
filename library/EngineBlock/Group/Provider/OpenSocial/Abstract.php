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

require 'Osapi/Loader.php';

/**
 * The OpenSocial group provider, gets groups from an OpenSocial REST API.
 */ 
abstract class EngineBlock_Group_Provider_OpenSocial_Abstract extends EngineBlock_Group_Provider_Abstract
{
    /**
     * @var string
     */
    protected $_name;

    /**
     * @var string
     */
    protected $_url;

    /**
     * @var \osapi
     */
    protected $_openSocialClient;

    public static function createFromConfigsWithAuth(Zend_Config $config, osapiAuth $auth, $userId)
    {
        $provider = new static(
            $config->id,
            $config->name,
            $config->url,
            $auth
        );
        $provider->setUserId($userId);

        $provider->configurePreconditions($config);
        $provider->configureGroupFilters($config);
        $provider->configureGroupMemberFilters($config);
        $decoratedProvider = $provider->configureDecoratorChain($config);

        return $decoratedProvider;
    }

    protected function __construct($id, $name, $endpointUrl, osapiAuth $auth)
    {
        $this->_id   = $id;
        $this->_name = $name;
        $this->_url  = $endpointUrl;
        $this->_openSocialAuth = $auth;
    }

    public function getDisplayName()
    {
        return $this->_name;
    }

    public function getGroups()
    {
        $this->_requireUserId();

        $openSocialClient = $this->_getOpenSocialClient();

        /**
         * @var osapiGroups $groupsService
         */
        $groupsService = $openSocialClient->groups;

        $batch = $openSocialClient->newBatch();
        $batch->add($groupsService->get(array('userId'=>$this->_userId)));
        $result = array_shift($batch->execute());

        if ($result instanceof osapiError) {
            $this->_handleError($result);
        }

        /**
         * @var osapiCollection $collection
         */
        $collection  = $result['data'];
        $osapiGroups = $collection->getList();

        $groups = array();
        foreach ($osapiGroups as $osapiGroup) {
            $groups[] = $this->_mapOsapiGroupToEngineBlockGroup($osapiGroup);
        }
        return $groups;
    }

    public function getMembers($groupIdentifier)
    {
        $this->_requireUserId();
        
        $openSocialClient = $this->_getOpenSocialClient();

        /**
         * @var osapiGroups $peopleService
         */
        $peopleService = $openSocialClient->people;

        $batch = $openSocialClient->newBatch();
        $batch->add($peopleService->get(array(
            'userId'    =>  $this->_userId,
            'groupId'   =>  $this->_getStemmedGroupId($groupIdentifier))
        ));
        $results = $batch->execute();

        /**
         * @var osapiCollection $collection
         */
        $collection = $results['data'];
        $osapiPeople = $collection->getList();

        $members = array();
        foreach ($osapiPeople as $osapiPerson) {
            $members[] = $this->_mapOsapiPersonToEngineBlockGroupMember($osapiPerson);
        }
        return $members;
    }

    public function isMember($groupIdentifier)
    {
        $this->_requireUserId();
        
        $openSocialClient = $this->_getOpenSocialClient();

        /**
         * @var osapiGroups $groupsService
         */
        $groupsService = $openSocialClient->groups;

        $batch = $openSocialClient->newBatch();
        $batch->add($groupsService->get(array('userId'=>$this->_userId)));
        $results = $batch->execute();

        /**
         * @var osapiCollection $collection
         */
        $collection = $results['data'];
        $osapiGroups = $collection->getList();
        /**
         * @var osapiGroup $osapiGroup
         */
        foreach ($osapiGroups as $osapiGroup) {
            if ($osapiGroup->id === $this->_getStemmedGroupId($groupIdentifier)) {
                return true;
            }
        }
        return false;
    }

    protected function _mapOsapiGroupToEngineBlockGroup(osapiGroup $osapiGroup)
    {
        $group = new EngineBlock_Group_Model_Group();
        $group->id          = $osapiGroup->id;
        $group->title       = $osapiGroup->title;
        $group->description = $osapiGroup->description;

        $filters = $this->getGroupFilters();
        foreach ($filters as $filter) {
            $group = $filter->filter($group);
        }

        return $group;
    }

    protected function _mapOsapiPersonToEngineBlockGroupMember(osapiPerson $osapiPerson)
    {
        $groupMember = new EngineBlock_Group_Model_GroupMember();
        $groupMember->id = $osapiPerson->id;

        $filters = $this->getMemberFilters();
        foreach ($filters as $filter) {
            $groupMember = $filter->filter($groupMember);
        }

        return $groupMember;
    }

    /**
     * @return osapi
     */
    protected function _getOpenSocialClient()
    {
        if (isset($this->_openSocialClient)) {
            return $this->_openSocialClient;
        }

        $this->_setDefaultOpenSocialClient();
        return $this->_openSocialClient;
    }

    /**
     * @return osapi
     */
    protected function _setDefaultOpenSocialClient()
    {
        $provider = new Osapi_Provider_PlainRest($this->_name, $this->_url);
        $openSocialClient = new osapi($provider, $this->_openSocialAuth);
        
        $this->_openSocialClient = $openSocialClient;
    }

    protected function _handleError(osapiError $error)
    {
        if ($error->getErrorCode() === 401) {
            throw new EngineBlock_Group_Provider_Exception_Unauthorized(
                "OpenSocial client error: " . var_export($error, true)
            );
        }
        else {
            throw new EngineBlock_Group_Provider_Exception(
                "OpenSocial client error: " . var_export($error, true)
            );
        }
    }
}