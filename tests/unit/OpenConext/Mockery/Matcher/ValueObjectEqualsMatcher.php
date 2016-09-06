<?php

namespace OpenConext\Mockery\Matcher;

use Mockery\Exception\RuntimeException;
use Mockery\Matcher\MatcherAbstract;

/**
 * A Mockery matcher that can be used to verify equality of value objects
 */
class ValueObjectEqualsMatcher extends MatcherAbstract
{
    public function __construct($expected)
    {
        if (!is_object($expected) || !method_exists($expected, 'equals')) {
            throw new RuntimeException(
                'In order to use the ValueObjectEqualsMatcher an object that implements "equals" method to compare'
                . ' itself against an instance of itself should be given.'
            );
        }

        parent::__construct($expected);
    }

    public function match(&$actual)
    {
        if (get_class($actual) !== get_class($this->_expected)) {
            return false;
        }

        return $this->_expected->equals($actual);
    }

    public function __toString()
    {
        return sprintf('<ValueObjectEqualsMatcher(%s)>', $this->_expected);
    }
}
