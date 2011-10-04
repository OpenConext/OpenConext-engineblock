<?php

namespace EngineBlock\Behat\Context;

require_once 'mink/autoload.php';
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use \Behat\Behat\Context\BehatContext;

class Portal extends BehatContext
{
    /**
     * @When /^I go the Portal with "([^"]*)" as the entity ID$/
     */
    public function iGoThePortalWithAsTheEntityId($entityId)
    {
        $url = "https://portal.test.surfconext.nl/Shibboleth.sso/Login?target=" . urlencode("https://portal.test.surfconext.nl/coin/home.shtml") . "&entityID=".
               urlencode($entityId);
        $this->getMainContext()->visit($url);
    }

    /**
     * @Then /^I should be on the Portal$/
     */
    public function iShouldBeOnThePortal()
    {
        $this->getMainContext()->assertPageAddress('https://portal.test.surfconext.nl/coin/home.shtml');
    }

    /**
     * @Then /^I see the error "([^"]*)"$/
     */
    public function iSeeTheError($errorMessage)
    {
        $this->getMainContext()->assertPageContainsText($errorMessage);
    }
}