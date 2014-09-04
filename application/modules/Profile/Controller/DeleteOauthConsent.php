<?php

class Profile_Controller_DeleteOauthConsent extends Default_Controller_LoggedIn
{
    public function indexAction()
    {
        $consumerKey = $this->_getRequest()->getQueryParameter('consumer_key');
        $this->user->deleteOauthConsent(urldecode($consumerKey));

        $this->setNoRender(true);
        $this->_redirectToUrl('/profile/#MyApps');
    }

}
