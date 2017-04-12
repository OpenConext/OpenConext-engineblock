<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class Url implements Serializable
{
    /**
     * @var string
     */
    private $url;

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        Assertion::nonEmptyString($url, 'url');

        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param Url $other
     * @return bool
     */
    public function equals(Url $other)
    {
        return $this->url === $other->url;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keyExists($data, 'url');

        return new self($data['url']);
    }

    public function serialize()
    {
        return array(
            'url' => $this->url,
        );
    }

    public function __toString()
    {
        return sprintf('Url(url=%)', $this->url);
    }
}
