<?php

namespace OpenConext\EngineBlock\Metadata\Value\X509;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

/**
 * Represents a PEM encoded public key certificate structured as CertData in a Metadata xml:
 * no header, footer, or newlines
 */
final class Certificate implements Serializable
{
    /**
     * @var string
     */
    private $certificate;

    /**
     * @param string $certificate
     */
    public function __construct($certificate)
    {
        Assertion::nonEmptyString($certificate, 'certificate');

        $this->certificate = $certificate;
    }

    public function getCertificate()
    {
        return $this->certificate;
    }

    public function equals(Certificate $other)
    {
        return $this->certificate === $other->certificate;
    }

    public static function deserialize($data)
    {
        return new self($data);
    }

    public function serialize()
    {
        return $this->certificate;
    }

    public function __toString()
    {
        return sprintf('Certificate(%s)', $this->certificate);
    }
}
