<?php

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Assert\Assertion;

/**
 * Represents the value of a SAML Attribute with the attribute name
 * urn:mace:terena.org:attribute-def:schacHomeOrganization
 */
final class SchacHomeOrganization
{
    const URN_MACE = 'urn:mace:terena.org:attribute-def:schacHomeOrganization';

    /**
     * @var string
     */
    private $schacHomeOrganization;

    /**
     * @param string $schacHomeOrganization
     */
    public function __construct($schacHomeOrganization)
    {
        Assertion::nonEmptyString($schacHomeOrganization, 'schacHomeOrganization');

        $this->schacHomeOrganization = $schacHomeOrganization;
    }

    /**
     * @return string
     */
    public function getSchacHomeOrganization()
    {
        return $this->schacHomeOrganization;
    }

    /**
     * @param SchacHomeOrganization $other
     * @return bool
     */
    public function equals(SchacHomeOrganization $other)
    {
        return $this->schacHomeOrganization === $other->schacHomeOrganization;
    }

    public function __toString()
    {
        return sprintf('SchacHomeOrganization(%s)', $this->schacHomeOrganization);
    }
}
