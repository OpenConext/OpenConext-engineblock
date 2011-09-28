<?php

namespace EngineBlock\Behat\Context;

require_once 'mink/autoload.php';

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
}