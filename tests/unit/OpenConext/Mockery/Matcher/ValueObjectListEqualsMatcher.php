<?php

namespace OpenConext\Mockery\Matcher;

use InvalidArgumentException;
use Mockery\Matcher\MatcherAbstract;

final class ValueObjectListEqualsMatcher extends MatcherAbstract
{
    /**
     * @param object[] $valueObjects An array of value objects of the same class, implementing an equals() method
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $valueObjects)
    {
        $message = sprintf(
            'Argument 1 passed to %s must be an array of value objects of the same class, implementing an equals() method',
            __METHOD__
        );

        $first = array_values($valueObjects)[0];

        if (!is_object($first) || !method_exists($first, 'equals')) {
            throw new InvalidArgumentException($message);
        }

        foreach ($valueObjects as $valueObject) {
            if (!is_object($valueObject) || get_class($valueObject) !== get_class($first)) {
                throw new InvalidArgumentException($message);
            }
        }

        parent::__construct($valueObjects);
    }

    /**
     * @param array $actual
     *
     * @return bool
     */
    public function match(&$actual)
    {
        if (!is_array($actual) || count($actual) !== count($this->_expected)) {
            return false;
        }

        foreach ($actual as $index => $valueObject) {
            if (get_class($valueObject) !== get_class($this->_expected[$index])) {
                return false;
            }

            if (!$valueObject->equals($this->_expected[$index])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('<ValueObjectListEqualsMatcher(%s)>', $this->_expected);
    }
}
