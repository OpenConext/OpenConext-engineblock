<?php

namespace EngineBlock\Behat\Context;

require_once 'mink/autoload.php';

use \Behat\Behat\Context\BehatContext;

class Background extends BehatContext
{
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