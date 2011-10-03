<?php

namespace EngineBlock\Behat\Context;

require_once 'mink/autoload.php';

use \Behat\Behat\Context\BehatContext;

class Login extends BehatContext
{
    /**
     * @return \Behat\Mink\Behat\Context\MinkContext
     */
    public function getMainContext()
    {
        return parent::getMainContext();
    }

    /**
     * @Given /^I select from the WAYF "([^"]*)"$/
     */
    public function iSelectFromTheWayf($idpName)
    {
        $this->getMainContext()->pressButton($idpName);
    }

    /**
     * @Given /^I log in as "([^"]*)" with password "([^"]*)"$/
     */
    public function iLogInAsWithPassword($userName, $password)
    {
        $this->getMainContext()->fillField('username', $userName);
        $this->getMainContext()->fillField('password', $password);
        $this->getMainContext()->pressButton('   Login   ');
        $this->getMainContext()->pressButton('Submit'); // Once for SURFguest
    }

    /**
     * @Given /^I pass through EngineBlock$/
     */
    public function iPassThroughEngineBlock()
    {
        $this->getMainContext()->pressButton('Submit'); // First one for EngineBlock
        $this->getMainContext()->pressButton('Submit'); // Second one for EngineBlock
    }

    /**
     * @Then /^EngineBlock gives me the error "([^"]*)"$/
     */
    public function engineblockGivesMeTheError($errorMessage)
    {
        $this->getMainContext()->pressButton('Submit');
        $this->getMainContext()->assertPageContainsText($errorMessage);
    }

    /**
     * @Given /^at the Invited Guests IdP I select "([^"]*)"$/
     */
    public function atTheInvitedGuestsIdpISelect($authSource)
    {
        $this->getMainContext()->clickLink(strtolower($authSource));
    }

    /**
     * @Given /^at Twitter I log in as "([^"]*)" with password "([^"]*)"$/
     */
    public function atTwitterILogInAsWithPassword($username, $password)
    {
        $this->getMainContext()->fillField('username_or_email', $username);
        $this->getMainContext()->fillField('password', $password);
        $this->getMainContext()->pressButton('Inloggen');
        $this->getMainContext()->clickLink('klik hier om door te gaan');
    }

    /**
     * @Given /^I pass through the Invited Guests$/
     */
    public function iPassThroughTheInvitedGuests()
    {
        $this->getMainContext()->pressButton('Submit');
    }
}