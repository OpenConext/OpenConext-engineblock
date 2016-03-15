<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class Logo implements Serializable
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @param string $url
     * @param int    $width
     * @param int    $height
     */
    public function __construct($url, $width, $height)
    {
        Assertion::nonEmptyString($url, 'url');
        Assertion::integer($width);
        Assertion::integer($height);

        $this->url = $url;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param Logo $other
     * @return bool
     */
    public function equals(Logo $other)
    {
        return $this->url === $other->url && $this->width === $other->width && $this->height === $other->height;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist($data, ['url', 'width', 'height']);

        return new self($data['url'], $data['width'], $data['height']);
    }

    public function serialize()
    {
        return [
            'url'    => $this->url,
            'width'  => $this->width,
            'height' => $this->height
        ];
    }

    public function __toString()
    {
        return sprintf('Logo(url=%s, width=%d, height=%d', $this->url, $this->width, $this->height);
    }
}
