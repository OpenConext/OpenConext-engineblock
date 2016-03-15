<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class SamlEntityConfiguration implements Serializable
{
    /**
     * @var bool
     */
    private $requiresAdditionalLogging;

    /**
     * @var bool
     */
    private $disableScoping;

    /**
     * @var bool
     */
    private $requiresSignedRequests;

    /**
     * @param bool $requiresAdditionalLogging
     * @param bool $disableScoping
     * @param bool $requiresSignedRequests
     */
    public function __construct($requiresAdditionalLogging, $disableScoping, $requiresSignedRequests)
    {
        Assertion::boolean($requiresAdditionalLogging);
        Assertion::boolean($disableScoping);
        Assertion::boolean($requiresSignedRequests);

        $this->requiresAdditionalLogging = $requiresAdditionalLogging;
        $this->disableScoping            = $disableScoping;
        $this->requiresSignedRequests    = $requiresSignedRequests;
    }

    /**
     * @return bool
     */
    public function requiresAdditionalLogging()
    {
        return $this->requiresAdditionalLogging;
    }

    /**
     * @return bool
     */
    public function isScopingDisabled()
    {
        return $this->disableScoping;
    }

    /**
     * @return bool
     */
    public function requiresSignedRequests()
    {
        return $this->requiresSignedRequests;
    }

    /**
     * @param SamlEntityConfiguration $other
     * @return bool
     */
    public function equals(SamlEntityConfiguration $other)
    {
        return $this->requiresAdditionalLogging === $other->requiresAdditionalLogging
                && $this->disableScoping === $other->disableScoping
                && $this->requiresSignedRequests === $other->requiresSignedRequests;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist(
            $data,
            ['requires_additional_logging', 'disable_scoping', 'requires_signed_requests']
        );

        return new self(
            $data['requires_additional_logging'],
            $data['disable_scoping'],
            $data['requires_signed_requests']
        );
    }

    public function serialize()
    {
        return [
            'requires_additional_logging' => $this->requiresAdditionalLogging,
            'disable_scoping'             => $this->disableScoping,
            'requires_signed_requests'    => $this->requiresSignedRequests
        ];
    }

    public function __toString()
    {
        return sprintf(
            'SamlEntityConfiguration(requiresAdditionalLogging=%s, disableScoping=%s, requiresSignedRequests=%s)',
            ($this->requiresAdditionalLogging ? 'true' : 'false'),
            ($this->disableScoping ? 'true' : 'false'),
            ($this->requiresSignedRequests ? 'true' : 'false')
        );
    }
}
