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

    /**
     * @Given /^we are using EngineBlock on the "([^"]*)" environment$/
     */
    public function weAreUsingEngineblockOnTheEnvironment($argument1)
    {
    }

    /**
     * @Given /^we have several IDPs configured$/
     */
    public function weHaveSeveralIdpsConfigured()
    {
    }

    /**
     * @Given /^we have a "([^"]*)" SP configured$/
     */
    public function weHaveASpConfigured($portalSp)
    {
    }

    /**
     * @Given /^we have a "([^"]*)" IdP configured$/
     */
    public function weHaveAIdpConfigured($argument1)
    {
    }

    /**
     * @Given /^the "([^"]*)" IdP is configured to allow-none SPs$/
     */
    public function theIdpIsConfiguredToAllowNoneSps($argument1)
    {
    }

    /**
     * @Given /^the "([^"]*)" IdP is configured not to allow "([^"]*)"$/
     */
    public function theIdpIsConfiguredNotToAllow($argument1, $argument2)
    {
    }

    /**
     * @Given /^the "([^"]*)" IdP is configured to allow "([^"]*)"$/
     */
    public function theIdpIsConfiguredToAllow($argument1, $argument2)
    {
    }

    /**
     * @Given /^we have a "([^"]*)" user with the username "([^"]*)", name "([^"]*)" and password "([^"]*)"$/
     */
    public function weHaveAUserWithTheUsernameNameAndPassword($argument1, $argument2, $argument3, $argument4)
    {
    }

    /**
     * @Given /^we have a Twitter user with the username "([^"]*)", name "([^"]*)" and password "([^"]*)"$/
     */
    public function weHaveATwitterUserWithTheUsernameNameAndPassword($argument1, $argument2, $argument3)
    {
    }

    /**
     * @Given /^we have an Idp VO with the id "([^"]*)" and IdP "([^"]*)"$/
     */
    public function weHaveAnIdpVoWithTheIdAndIdp($id, $idpEntityId)
    {
    }

    /**
     * @Given /^the "([^"]*)" SP allows logins from "([^"]*)" IdP$/
     */
    public function theSpAllowsLoginsFromIdp($argument1, $argument2)
    {
    }

    /**
     * @Given /^the "([^"]*)" SP does NOT allow logins from "([^"]*)" IdP$/
     */
    public function theSpDoesNotAllowLoginsFromIdp($argument1, $argument2)
    {
    }

    /**
     * @Given /^we have a WrongCertIdP user with the username "([^"]*)", name "([^"]*)" and password "([^"]*)"$/
     */
    public function weHaveAWrongcertidpUserWithTheUsernameNameAndPassword($argument1, $argument2, $argument3)
    {
    }

    /**
     * @Given /^we have configured a "([^"]*)" SP that uses a wrong signing certificate$/
     */
    public function weHaveConfiguredASpThatUsesAWrongSigningCertificate($argument1)
    {
    }

    /**
     * @Given /^we have configured an "([^"]*)" IdP that uses a wrong signing certificate$/
     */
    public function weHaveConfiguredAnIdpThatUsesAWrongSigningCertificate($argument1)
    {
    }

    /**
     * @Given /^the "([^"]*)" IP is configured not allow "([^"]*)"$/
     */
    public function theIpIsConfiguredNotAllow($argument1, $argument2)
    {
    }

    /**
     * @Given /^we have a "([^"]*)" IP configured$/
     */
    public function weHaveAIpConfigured($argument1)
    {
    }

    /**
     * @Given /^the "([^"]*)" IP is configured not to allow "([^"]*)"$/
     */
    public function theIpIsConfiguredNotToAllow($argument1, $argument2)
    {
    }

    /**
     * @Given /^the "([^"]*)" IP is configured to allow "([^"]*)"$/
     */
    public function theIpIsConfiguredToAllow($argument1, $argument2)
    {
    }

    /**
     * @Given /^we have configured an "([^"]*)" IdP that does not provide the schacHomeOrganization$/
     */
    public function weHaveConfiguredAnIdpThatDoesNotProvideTheSchachomeorganization($argument1)
    {
    }

    /**
     * @Given /^we have a WrongAttrIdP user with the username "([^"]*)", name "([^"]*)" and password "([^"]*)"$/
     */
    public function weHaveAWrongattridpUserWithTheUsernameNameAndPassword($argument1, $argument2, $argument3)
    {
    }

    /**
     * @Given /^we have a IdP that returns a transient non existing uid on each login$/
     */
    public function weHaveAIdpThatReturnsATransientNonExistingUidOnEachLogin()
    {
    }

    /**
     * @Given /^we have an attribute manipulation for "([^"]*)" that transforms "([^"]*)" to superman$/
     */
    public function weHaveAnAttributeManipulationForThatTransformsToSuperman($argument1, $argument2)
    {
    }

    /**
     * @Given /^we have mapped "([^"]*)" to "([^"]*)" in shibboleth "([^"]*)"$/
     */
    public function weHaveMappedToInShibboleth($argument1, $argument2, $argument3)
    {
    }

    /**
     * @Given /^we print the "([^"]*)" shibboleth header in the testsp application$/
     */
    public function wePrintTheShibbolethHeaderInTheTestspApplication($argument1)
    {
    }


}