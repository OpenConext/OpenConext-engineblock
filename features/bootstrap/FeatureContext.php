<?php

require_once 'mink/autoload.php';
require_once 'File/Iterator.php';

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException,
    Behat\Mink\Behat\Context\MinkContext,
    Behat\Behat\Event\FeatureEvent;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    /**
     * @Given /^I select from the WAYF "([^"]*)"$/
     */
    public function iSelectFromTheWayf($idpName)
    {
        $this->pressButton($idpName);
    }

        /**
     * @When /^I go to the Test SP$/
     */
    public function iGoToTheTestSp()
    {
        $this->visit("https://testsp.test.surfconext.nl/");
    }

    /**
     * @Given /^I log in as "([^"]*)" with password "([^"]*)"$/
     */
    public function iLogInAsWithPassword($userName, $password)
    {
        $this->fillField('username', $userName);
        $this->fillField('password', $password);
        $this->pressButton('   Login   ');
        $this->pressButton('Submit'); // Once for SURFguest
    }

    /**
     * @Given /^I pass through EngineBlock$/
     */
    public function iPassThroughEngineBlock()
    {
        $this->printLastResponse();
        $this->pressButton('Submit'); // First one for EngineBlock
        $this->printLastResponse();
        $this->pressButton('Submit'); // Second one for EngineBlock
    }

    /**
     * @Then /^I should be on the Test SP$/
     */
    public function iShouldBeOnTheTestSp()
    {
        $this->assertPageAddress('https://testsp.test.surfconext.nl/testsp/home.shtml');
    }

    /**
     * @When /^I go to the Test SP with the explicit VO "([^"]*)"$/
     */
    public function iGoToTheTestSpWithTheExplicitVo($voId)
    {
        $url = "https://testsp.test.surfconext.nl/Shibboleth.sso/Login?entityID=" .
            urlencode("https://engine.test.surfconext.nl/authentication/idp/metadata/vo:" . $voId);
        $this->visit($url);
    }

    // ======== Background stuff, we COULD use this to dynamically set up the environment
    // ======== or test that a already set up environment is still sane
    // ======== but for now we just ignore.

    /**
     * @Given /^we are using the SP "([^"]*)" on the "([^"]*)" environment$/
     */
    public function weAreUsingTheSpOnTheEnvironment($argument1, $argument2)
    {
    }

    /**
     * @Given /^we have a Group VO with the id "([^"]*)" and group "([^"]*)"$/
     */
    public function weHaveAGroupVoWithTheIdAndGroup($argument1, $argument2)
    {
    }

    /**
     * @Given /^we have a Stem VO with the id "([^"]*)" and stem "([^"]*)"$/
     */
    public function weHaveAStemVoWithTheIdAndStem($argument1, $argument2)
    {
    }

    /**
     * @Given /^the SP "([^"]*)" is implicitly coupled to the VO "([^"]*)"$/
     */
    public function theSpIsImplicitlyCoupledToTheVo($argument1, $argument2)
    {
    }

    /**
     * @Given /^we have a SURFguest user with the username "([^"]*)", name "([^"]*)" and password "([^"]*)"$/
     */
    public function weHaveASurfguestUserWithTheUsernameNameAndPassword($argument1, $argument2, $argument3)
    {
    }

    /**
     * @Given /^user "([^"]*)" is a member of the Group "([^"]*)"$/
     */
    public function userIsAMemberOfTheGroup($argument1, $argument2)
    {
    }

    /**
     * @Given /^user "([^"]*)" is not a member of the Group "([^"]*)"$/
     */
    public function userIsNotAMemberOfTheGroup($argument1, $argument2)
    {
    }

    /**
     * @Given /^user "([^"]*)" is not a member of any Group$/
     */
    public function userIsNotAMemberOfAnyGroup($argument1)
    {
    }
}
