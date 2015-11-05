<?php

namespace OpenConext\EngineBlock\ApiBundle\Dto;

final class ConsentList
{
    /**
     * @var Consent[]
     */
    private $consents = array();

    /**
     * @param Consent[] $consents
     */
    public function __construct(array $consents)
    {
        foreach ($consents as $consent) {
            $this->initialiseWith($consent);
        }
    }

    public function jsonSerialize()
    {
        return array_map(
            function (Consent $consent) {
                return $consent->jsonSerialize();
            },
            $this->consents
        );
    }

    private function initialiseWith(Consent $consent)
    {
        $this->consents[] = $consent;
    }
}
