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

class Profile_Controller_Index extends Default_Controller_LoggedIn
{
    public function indexAction()
    {
        $normalizer = new EngineBlock_Attributes_Normalizer($this->attributes);
        $this->attributes = $normalizer->normalize();

        $this->metadata = new EngineBlock_Attributes_Metadata();
        $this->aggregator = EngineBlock_Group_Provider_Aggregator_MemoryCacheProxy::createFromDatabaseFor(
            $this->attributes['nameid'][0]
        );
        $this->groupOauth = $this->user->getUserOauth();

        $serviceRegistryClient = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getServiceRegistryClient();
        $this->spList = $serviceRegistryClient->getSpList();

        $this->consent = $this->user->getConsent();
        $this->spAttributesList = $this->_getSpAttributeList($this->spList);

        try {
            $this->spOauthList = $this->_getSpOauthList($this->spList);
        }

        catch (Exception $e) {
            $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::create()
                ->setUserId($this->user->getUid())
                ->setDetails($e->getTraceAsString());

            EngineBlock_ApplicationSingleton::getLog()->crit($e->getMessage(), $additionalInfo);
        }
    }

    /**
     * @param $spList all service providers
     * @return all service providers that have an entry in the oauth (consent can be revoked)
     */
    protected function _getSpOauthList($spList)
    {
        /** @var $user EngineBlock_User */
        $user = $this->user;
        $oauthList = $user->getThreeLeggedShindigOauth();
        $results = array();
        foreach ($spList as $spId => $sp) {
            if (array_key_exists('coin:gadgetbaseurl', $sp)) {
                $pattern = '#' . $sp['coin:gadgetbaseurl'] . '#';
                foreach ($oauthList as $oauth) {
                    if (preg_match($pattern, $oauth)) {
                        $results[$spId] = $oauth;
                    }
                }
            }
        }
        return $results;
    }

    /**
     * Returns an array with attributes that are released to each SP.
     *
     * For now we use the attributes that are released to the profile SP. Because we do not have an APR yet and therefore
     * each SP receives the same set of attributes.
     * TODO If the ARP is implemented change the code below to actually retrieve the set of attributes that is released to that specific SP
     *
     * @param $spList all service providers
     * @return all service providers that have an entry in the oauth (consent can be revoked)
     */
    protected function _getSpAttributeList($spList)
    {
        $results = array();

        foreach ($spList as $spId => $sp) {
            $results[$spId] = $this->attributes;
        }

        return $results;
    }
}