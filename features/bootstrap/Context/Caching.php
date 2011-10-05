<?php

namespace EngineBlock\Behat\Context;

require_once 'mink/autoload.php';

use \Behat\Behat\Context\BehatContext;

class Caching extends BehatContext
{
    /**
     * @Given /^I go to Google$/
     */
    public function iGoToGoogle()
    {
        $this->getMainContext()->visit("http://www.google.com/");
    }

    /**
     * @Given /^I go back to the testSP$/
     */
    public function iGoBackToTheTestsp()
    {
        $this->getMainContext()->visit('https://testsp.test.surfconext.nl/testsp/');
    }
}