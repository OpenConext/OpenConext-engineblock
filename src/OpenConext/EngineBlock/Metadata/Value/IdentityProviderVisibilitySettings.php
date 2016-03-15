<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class IdentityProviderVisibilitySettings implements Serializable
{
    /**
     * @var bool
     */
    private $isHidden;

    /**
     * @var bool
     */
    private $enabledInWayf;

    /**
     * @param bool $isHidden
     * @param bool $enabledInWayf
     */
    public function __construct($isHidden, $enabledInWayf)
    {
        Assertion::boolean($isHidden);
        Assertion::boolean($enabledInWayf);

        $this->isHidden = $isHidden;
        $this->enabledInWayf = $enabledInWayf;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->isHidden;
    }

    /**
     * @return bool
     */
    public function isEnabledInWay()
    {
        return $this->enabledInWayf;
    }

    /**
     * @param IdentityProviderVisibilitySettings $other
     * @return bool
     */
    public function equals(IdentityProviderVisibilitySettings $other)
    {
        return $this->isHidden === $other->isHidden && $this->enabledInWayf === $other->enabledInWayf;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist($data, ['hidden', 'enabled_in_wayf']);

        return new self($data['hidden'], $data['enabled_in_wayf']);
    }

    public function serialize()
    {
        return [
            'hidden'          => $this->isHidden,
            'enabled_in_wayf' => $this->enabledInWayf
        ];
    }

    public function __toString()
    {
        return sprintf(
            'IdentityProviderVisibilitySettings(hidden=%s, enabledInWayf=%s)',
            ($this->isHidden ? 'true' : 'false'),
            ($this->enabledInWayf ? 'true' : 'false')
        );
    }
}
