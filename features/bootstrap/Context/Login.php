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
     * Generic method to login at an Identity Provider
     *
     * @Given /^I log in at IP as "([^"]*)" with password "([^"]*)"$/
     * @param string $userName
     * @param string $password
     * @return void
     */
    public function iLogInAtIPAsWithPassword($userName, $password)
    {
        $this->getMainContext()->fillField('username', $userName);
        $this->getMainContext()->fillField('password', $password);
        $this->getMainContext()->pressButton('Login');
        $this->getMainContext()->pressButton('Submit'); // POST SAML to engineblock
    }

    /**
     * Custom method to log in at Surf guest Identity Provider
     *
     * @Given /^I log in at Surfguest IP as "([^"]*)" with password "([^"]*)"$/
     * @param string $userName
     * @param string $password
     * @return void
     */
    public function iLogAtSurfGuestIPAsWithPassword($userName, $password)
    {
        $this->getMainContext()->fillField('username', $userName);
        $this->getMainContext()->fillField('password', $password);
        $this->getMainContext()->pressButton('   Login   ');
        $this->getMainContext()->pressButton('Submit'); // POST SAML to engineblock
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
     * @Given /^I pass through Surfguest IP$/
     */
    public function iPassThroughSurfguestIP()
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