<?php

namespace OpenConext\EngineBlock\Validator;

class AllowedSchemeValidator implements ValidatorInterface
{
    /**
     * @var array
     */
    private $allowedAcsLocationSchemes;

    public function __construct(array $allowedAcsLocationSchemes)
    {
        $this->allowedAcsLocationSchemes = $allowedAcsLocationSchemes;
    }

    public function validate($acsLocation)
    {
        $parts = parse_url($acsLocation);

        if (!isset($parts['scheme'])) {
            return false;
        }

        if ($acsLocation && !in_array($parts['scheme'], $this->allowedAcsLocationSchemes)) {
            return false;
        }

        return true;
    }
}
