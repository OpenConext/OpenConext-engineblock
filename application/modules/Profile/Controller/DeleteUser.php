<?php

class Profile_Controller_DeleteUser extends EngineBlock_Controller_Abstract
{
    protected $_identity;

    public function indexAction()
    {
        $currentUser = $this->_initAuthentication();
        $currentUser->delete();

        $this->setNoRender(true);
        $this->_redirectToUrl('/profile/delete-user/success');
    }

    public function successAction()
    {
    }
}
