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

class Profile_Controller_AttributeSupport extends Default_Controller_LoggedIn
{
    public function indexAction()
    {
        $this->_sendAttributeSupportMail();
        $this->setNoRender(true);
        $this->_redirectToUrl('/profile?mailSend=success');
    }

    protected function _sendAttributeSupportMail() {
        $normalizer = new EngineBlock_Attributes_Normalizer($this->attributes);
        $normalizedAttributes = $normalizer->normalize();
        $email = EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue('email')->help;
        $nameId = $normalizedAttributes['nameid'][0];

        $view = $this->_getView();
        $view->setData(
            array(
                'metadata' => new EngineBlock_Attributes_Metadata(),
                'userAttributes' => $normalizedAttributes,
                'lang' => $view->language()
            )
        );
        $body = $view->render(ENGINEBLOCK_FOLDER_MODULES . '/Profile/View/AttributeSupport/ProfileMail.phtml', false);

        $mailer = new Zend_Mail('UTF-8');
        $mailer->setFrom($email);
        $mailer->addTo($email);
        $mailer->setSubject(sprintf("Personal debug info of %s", $nameId));
        $mailer->setBodyHtml($body);
        $mailer->send();
    }


}
