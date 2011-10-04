<?php

namespace EngineBlock\Behat\Context;

require_once 'mink/autoload.php';
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use Behat\Behat\Context\BehatContext;

class WrongCertSP extends BehatContext
{
    /**
     * @return \Behat\Mink\Behat\Context\MinkContext
     */
    public function getMainContext()
    {
        return parent::getMainContext();
    }

    /**
     * @When /^I go to the Wrong Cert SP$/
     */
    public function iGoToTheWrongCertSp()
    {
        $this->getMainContext()->visit('https://wrongcertsp.dev.surfconext.nl/simplesaml/module.php/core/authenticate.php?as=wrong-cert-sp');
    }

    /**
     * @Given /^at the Wrong Cert SP I select "([^"]*)"$/
     */
    public function atTheWrongCertSpISelect($authSource)
    {
        $this->getMainContext()->selectOption('idpentityid', $authSource);
        $this->getMainContext()->pressButton('Select');
    }
}