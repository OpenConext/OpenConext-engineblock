<?php

namespace EngineBlock\Behat\Context;

require_once 'mink/autoload.php';

use \Behat\Behat\Context\BehatContext;

class Login extends BehatContext
{
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

}