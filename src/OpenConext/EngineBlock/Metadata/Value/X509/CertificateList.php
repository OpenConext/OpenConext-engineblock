<?php

namespace OpenConext\EngineBlock\Metadata\Value\X509;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\IndexOutOfBoundsException;
use OpenConext\Value\Serializable;

/**
 * A list of certificates. A list has been chosen on purpose, as a set requires deduplication where there may be
 * instances where a certificate could be duplicated on purpose (e.g. as result of a key rollover)
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) due to adhering to VO List structure and interfaces
 */
final class CertificateList implements Countable, IteratorAggregate, Serializable
{
    /**
     * @var Certificate[]
     */
    private $certificates;

    public function __construct(array $certificates)
    {
        Assertion::allIsInstanceOf($certificates, Certificate::class);

        $this->certificates = $certificates;
    }

    /**
     * @param Certificate $certificate
     * @return CertificateList
     */
    public function add(Certificate $certificate)
    {
        return new self(array_merge($this->certificates, [$certificate]));
    }

    /**
     * @param Certificate $certificate
     * @return bool
     */
    public function contains(Certificate $certificate)
    {
        foreach ($this->certificates as $listedCertificate) {
            if ($listedCertificate->equals($certificate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Certificate $certificate
     * @return int
     */
    public function indexOf(Certificate $certificate)
    {
        foreach ($this->certificates as $index => $listedCertificate) {
            if ($listedCertificate->equals($certificate)) {
                return $index;
            }
        }

        return -1;
    }

    /**
     * @param int $index
     * @return Certificate
     */
    public function get($index)
    {
        Assertion::integer($index);

        if ($index < 0) {
            throw IndexOutOfBoundsException::tooLow($index, 0);
        }

        if ($index > count($this->certificates) - 1) {
            throw IndexOutOfBoundsException::tooHigh($index, count($this->certificates) - 1);
        }

        return $this->certificates[$index];
    }

    /**
     * @param Callable $predicate
     * @return null|Certificate
     */
    public function find(callable $predicate)
    {
        foreach ($this->certificates as $certificate) {
            if (call_user_func($predicate, $certificate) === true) {
                return $certificate;
            }
        }

        return null;
    }

    /**
     * @param CertificateList $other
     * @return bool
     */
    public function equals(CertificateList $other)
    {
        if (count($this->certificates) !== count($other->certificates)) {
            return false;
        }

        foreach ($this->certificates as $index => $certificate) {
            if (!$certificate->equals($other->certificates[$index])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Certificate[]
     */
    public function toArray()
    {
        return $this->certificates;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->certificates);
    }

    public function count()
    {
        return count($this->certificates);
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);

        $certificates = array_map(function ($certificate) {
            return Certificate::deserialize($certificate);
        }, $data);

        return new self($certificates);
    }

    public function serialize()
    {
        return array_map(function (Certificate $certificate) {
            return $certificate->serialize();
        }, $this->certificates);
    }

    public function __toString()
    {
        return sprintf('CertificateList[%s]', implode(', ', $this->certificates));
    }
}
