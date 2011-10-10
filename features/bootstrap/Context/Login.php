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
     * @Given /^I pass through SURFguest$/
     */
    public function iPassThroughSURFguest()
    {
        $this->getMainContext()->pressButton('Submit'); // Already logged into SURFguest, press submit to post SAML Response
    }

    /**
     * @Given /^I log in at SURFguest as "([^"]*)" with password "([^"]*)"$/
     */
    public function iLogInAtSurfguestAsWithPassword($userName, $password)
    {
        $this->getMainContext()->fillField('username', $userName);
        $this->getMainContext()->fillField('password', $password);
        $this->getMainContext()->pressButton('Login');
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
     * @Then /^EngineBlock directly gives me the error "([^"]*)"$/
     */
    public function engineblockDirectlyGivesMeTheError($errorMessage)
    {
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

    /**
     * @Given /^I log in to WrongCertIdp as "([^"]*)" with password "([^"]*)"$/
     */
    public function iLogInToWrongcertidpAsWithPassword($username, $password)
    {
        $this->getMainContext()->fillField('username', $username);
        $this->getMainContext()->fillField('password', $password);
        $this->getMainContext()->pressButton('Login');
    }

    /**
     * @Given /^I log in to WrongAttrIdp as "([^"]*)" with password "([^"]*)"$/
     */
    public function iLogInToWrongattridpAsWithPassword($username, $password)
    {
        $this->iLogInToWrongcertidpAsWithPassword($username,$password);
    }

    /**
     * @Given /^I log in to PerfTestPersistentIdp as "([^"]*)" with password "([^"]*)"$/
     */
    public function iLogInToPerftestpersistentidpAsWithPassword($username, $password)
    {
        $this->iLogInToWrongcertidpAsWithPassword($username,$password);
        $this->getMainContext()->pressButton('Submit');

    }

    /**
     * @Given /^I log in to PerfTestTransientIdp as "([^"]*)" with password "([^"]*)"$/
     */
    public function iLogInToPerftesttransientidpAsWithPassword($username, $password)
    {
        $this->iLogInToPerftestpersistentidpAsWithPassword($username, $password);
    }



    /**
     * @When /^I visit "([^"]*)"$/
     */
    public function iVisit($url)
    {
        $this->getMainContext()->visit($url);
    }

    /**
     * @Then /^I should should be on the WAYF$/
     */
    public function iShouldShouldBeOnTheWayf()
    {
        $currentUrl = $this->getMainContext()->getSession()->getCurrentUrl();
        preg_match("/^https:\/\/engine.test.surfconext.nl\/authentication\/idp\/single-sign-on/",$currentUrl);

    }

}