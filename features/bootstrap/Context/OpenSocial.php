<?php

namespace EngineBlock\Behat\Context;

require_once 'mink/autoload.php';
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use \Behat\Behat\Context\BehatContext;

class OpenSocial extends BehatContext
{
    /**
     * @return \Behat\Mink\Behat\Context\MinkContext
     */
    public function getMainContext()
    {
        return parent::getMainContext();
    }

    /**
     * @Given /^I remove my access tokens$/
     */
    public function iRemoveMyAccessTokens()
    {
        $this->getMainContext()->clickLink('Remove Service Provider Access Tokens');
    }


    /**
     * @Given /^I renew user oauth consent$/
     */
    public function iRenewUserOauthConsent()
    {
        $this->getMainContext()->pressButton('Renew User OAuth Consent');
        $this->getMainContext()->pressButton('Login');
        $this->getMainContext()->pressButton('Submit');
        $this->getMainContext()->pressButton('Submit');
        $this->getMainContext()->pressButton('Login');
        $this->getMainContext()->pressButton('Grant Access');
    }

    /**
     * @Given /^I retrieve the person info for "([^"]*)"$/
     */
    public function iRetrieveThePersonInfoFor($personUrn)
    {
        $this->fillFieldAndSubmit('personId', $personUrn);
    }


    /**
     * @Given /^I retrieve the member info for "([^"]*)"$/
     */
    public function iRetrieveTheMemberInfoFor($groupUrn)
    {
        $this->fillFieldAndSubmit('memberGroupId', $groupUrn);
    }

    /**
     * @Given /^I retrieve the groups info for "([^"]*)"$/
     */
    public function iRetrieveTheGroupsInfoFor($personUrn)
    {
        $this->fillFieldAndSubmit('groupPersonId', $personUrn);
    }


    /**
     * @Given /^I clean up my access tokens$/
     */
    public function iCleanUpMyAccessTokens()
    {
        $this->getMainContext()->visit('https://testsp.test.surfconext.nl/testsp/home.shtml');
        $this->iRemoveMyAccessTokens();

    }

    protected function fillFieldAndSubmit($fieldId, $urn)
    {
        $this->getMainContext()->fillField($fieldId, $urn);
        $this->getMainContext()->pressButton('Retrieve Open Social information');
    }


}
