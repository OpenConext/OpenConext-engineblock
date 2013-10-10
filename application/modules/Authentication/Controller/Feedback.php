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
class Authentication_Controller_Feedback extends EngineBlock_Controller_Abstract
{
    public function vomembershiprequiredAction()
    {
        header('HTTP/1.1 403 Forbidden');
    }

    public function unableToReceiveMessageAction()
    {
        header('HTTP/1.1 400 Bad Request');
    }

    public function sessionLostAction()
    {
        header('HTTP/1.1 400 Bad Request');
    }

    public function unknownIssuerAction()
    {
        header('HTTP/1.1 404 Not Found');
        $this->__set('entity-id', htmlspecialchars($this->_getRequest()->getQueryParameter('entity-id')));
        $this->__set('destination', htmlspecialchars($this->_getRequest()->getQueryParameter('destination')));
    }

    public function unknownServiceProviderAction()
    {
        header('HTTP/1.1 400 Bad Request');
        $this->__set('entity-id', htmlspecialchars($this->_getRequest()->getQueryParameter('entity-id')));
    }

    public function missingRequiredFieldsAction()
    {
        header('HTTP/1.1 400 Bad Request');
    }

    public function noConsentAction()
    {

    }

    public function customAction()
    {
        $proxyServer = new EngineBlock_Corto_ProxyServer();
        $proxyServer->startSession();
    }

    public function invalidAcsLocationAction()
    {
        header('HTTP/1.1 400 Bad Request');
    }

    public function invalidAcsBindingAction()
    {
        // @todo Send 4xx or 5xx header depending on invalid binding came from request or configured metadata
    }

    public function receivedErrorStatusCodeAction()
    {
        // @todo Send 4xx or 5xx header?
    }

    public function receivedInvalidResponseAction()
    {
        // @todo Send 4xx or 5xx header?
    }

    public function noIdpsAction()
    {
        // @todo Send 4xx or 5xx header?
    }
}
