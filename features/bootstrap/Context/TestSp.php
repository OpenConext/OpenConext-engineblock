<?php

namespace EngineBlock\Behat\Context;

require_once 'mink/autoload.php';
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use \Behat\Behat\Context\BehatContext;

class TestSp extends BehatContext
{
    /**
     * @When /^I go to the Test SP$/
     */
    public function iGoToTheTestSp()
    {
        $this->getMainContext()->visit("https://testsp.test.surfconext.nl/Shibboleth.sso/Login");
    }

    /**
     * @When /^I go the Test SP using "([^"]*)" as the entity ID$/
     */
    public function iGoTheTestSpUsingAsTheEntityId($entityId)
    {
        $url = "https://testsp.test.surfconext.nl/Shibboleth.sso/Login?entityID=" .
               urlencode("$entityId");
        $this->getMainContext()->visit($url);
    }

    /**
     * @Then /^I should be on the Test SP$/
     */
    public function iShouldBeOnTheTestSp()
    {
        $this->getMainContext()->assertPageAddress('https://testsp.test.surfconext.nl/testsp/');
    }

    /**
     * @When /^I go to the Test SP with the explicit VO "([^"]*)"$/
     */
    public function iGoToTheTestSpWithTheExplicitVo($voId)
    {
        $url = "https://testsp.test.surfconext.nl/Shibboleth.sso/Login?entityID=" .
               urlencode("https://engine.test.surfconext.nl/authentication/idp/metadata/vo:" . $voId);
        $this->getMainContext()->visit($url);
    }

    /**
     * @Then /^I should not be able to select "([^"]*)" from the WAYF$/
     */
    public function iShouldNotBeAbleToSelectFromTheWayf($behavIdp)
    {
        $button = $this->getMainContext()->getSession()->getPage()->findButton($behavIdp);
        assertNull($button);
    }

    /**
     * @Given /^I should be able to select "([^"]*)" from the WAYF$/
     */
    public function iShouldBeAbleToSelectFromTheWayf($guestIdp)
    {
        $button = $this->getMainContext()->getSession()->getPage()->findButton($guestIdp);
        assertNotNull($button);
    }
}