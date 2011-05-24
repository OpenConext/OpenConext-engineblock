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

class Authentication_Controller_Proxy extends EngineBlock_Controller_Abstract
{    
    /**
     * 
     *
     * @param string $encodedIdPEntityId
     * @return void
     */
    public function idPsMetaDataAction()
    {
        $this->setNoRender();

        // @todo Give the metaData for all known IdPs, where the SSO locations all go through EB
        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->idPsMetadata(EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getQueryString());
    }

    public function processedAssertionAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processedAssertionConsumer();
    }
}