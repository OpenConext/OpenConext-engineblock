<?php

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
                'metadata' => EngineBlock_ApplicationSingleton::getInstance()
                    ->getDiContainer()
                    ->getAttributeMetadata(),
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
