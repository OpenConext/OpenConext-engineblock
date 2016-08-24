<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Saml\Metadata\Common\Endpoint;
use OpenConext\Value\Serializable;

final class SingleSignOnServices implements Countable, IteratorAggregate, Serializable
{
    /**
     * @var Endpoint[]
     */
    private $endpoints;

    /**
     * @param Endpoint[] $endpoints
     */
    public function __construct(array $endpoints)
    {
        Assertion::allIsInstanceOf($endpoints, Endpoint::class);

        $this->endpoints = $endpoints;
    }

    /**
     * @return Endpoint[]
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }

    /**
     * @param callable $predicate
     * @return null|Endpoint
     */
    public function find(callable $predicate)
    {
        foreach ($this->endpoints as $endpoint) {
            if (call_user_func($predicate, $endpoint) === true) {
                return $endpoint;
            }
        }

        return null;
    }

    /**
     * @param SingleSignOnServices $other
     * @return bool
     */
    public function equals(SingleSignOnServices $other)
    {
        if (count($this->endpoints) !== count($other->endpoints)) {
            return false;
        }

        foreach ($this->endpoints as $index => $endpoint) {
            if (!$endpoint->equals($other->endpoints[$index])) {
                return false;
            }
        }

        return true;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->endpoints);
    }

    public function count()
    {
        return count($this->endpoints);
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);

        $endpoints = array_map(function ($endpoint) {
            return Endpoint::deserialize($endpoint);
        }, $data);

        return new self($endpoints);
    }

    public function serialize()
    {
        return array_map(function (Endpoint $endpoint) {
            return $endpoint->serialize();
        }, $this->endpoints);
    }

    public function __toString()
    {
        return sprintf('SingleSignOnServices[%s]', implode(', ', $this->endpoints));
    }
}
