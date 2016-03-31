<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\LogicException;
use OpenConext\Value\Saml\Metadata\Common\IndexedEndpoint;
use OpenConext\Value\Serializable;

final class AssertionConsumerServices implements Countable, IteratorAggregate, Serializable
{
    /**
     * @var IndexedEndpoint[]
     */
    private $indexedEndpoints;

    /**
     * @param IndexedEndpoint[] $indexedEndpoints
     */
    public function __construct(array $indexedEndpoints)
    {
        Assertion::allIsInstanceOf($indexedEndpoints, IndexedEndpoint::class);

        $this->indexedEndpoints = $indexedEndpoints;
    }

    /**
     * @return bool
     */
    public function hasDefaultEndpoint()
    {
        foreach ($this->indexedEndpoints as $endpoint) {
            if ($endpoint->isDefault()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return IndexedEndpoint
     */
    public function getDefaultEndpoint()
    {
        if (!$this->hasDefaultEndpoint()) {
            throw new LogicException(
                'Cannot get default endpoint when no default endpoint is present. Did you verify presence of a default '
                . 'endpoint with hasDefaultEndpoint()?'
            );
        }

        return $this->find(function (IndexedEndpoint $endpoint) {
            return $endpoint->isDefault();
        });
    }

    /**
     * @return IndexedEndpoint[]
     */
    public function getEndpoints()
    {
        return $this->indexedEndpoints;
    }

    /**
     * @param callable $predicate
     * @return null|IndexedEndpoint
     */
    public function find(callable $predicate)
    {
        foreach ($this->indexedEndpoints as $endpoint) {
            if (call_user_func($predicate, $endpoint) === true) {
                return $endpoint;
            }
        }

        return null;
    }

    /**
     * @param AssertionConsumerServices $other
     * @return bool
     */
    public function equals(AssertionConsumerServices $other)
    {
        if (count($this->indexedEndpoints) !== count($other->indexedEndpoints)) {
            return false;
        }

        foreach ($this->indexedEndpoints as $index => $endpoint) {
            if (!$endpoint->equals($other->indexedEndpoints[$index])) {
                return false;
            }
        }

        return true;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->indexedEndpoints);
    }

    public function count()
    {
        return count($this->indexedEndpoints);
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);

        $indexedEndpoints = array_map(function ($indexedEndpoint) {
            return IndexedEndpoint::deserialize($indexedEndpoint);
        }, $data);

        return new self($indexedEndpoints);
    }

    public function serialize()
    {
        return array_map(function (IndexedEndpoint $indexedEndpoint) {
            return $indexedEndpoint->serialize();
        }, $this->indexedEndpoints);
    }

    public function __toString()
    {
        return sprintf('AssertionConsumerServices[%s]', implode(', ', $this->indexedEndpoints));
    }
}
