<?php

require_once 'mink/autoload.php';


use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException,
    Behat\Mink\Behat\Context\MinkContext,
    Behat\Behat\Event\FeatureEvent;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Engineblock\Behat\Context;

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
    public function __construct(array $parameters)
    {
        parent::__construct($parameters);
        $this->useContext('background'  , new Context\Background($parameters));
        $this->useContext('login'       , new Context\Login($parameters));
        $this->useContext('testsp'      , new Context\TestSp($parameters));
        $this->useContext('portalsp'    , new Context\Portal($parameters));
        $this->useContext('metadata'    , new Context\Metadata($parameters));
        $this->useContext('caching'     , new Context\Caching($parameters));
        $this->useContext('wrongcertsp' , new Context\WrongCertSP($parameters));
        $this->useContext('provisioning', new Context\Provisioning($parameters));
        $this->useContext('opensocial'  , new Context\OpenSocial($parameters));
    }
}
