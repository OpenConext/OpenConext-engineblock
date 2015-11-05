<?php

namespace OpenConext\EngineBlock\ApiBundle\Dto;

final class Service
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var string
     */
    public $displayName;

    /**
     * @var string|null
     */
    public $eulaUrl;

    /**
     * @var string|null
     */
    public $supportUrl;

    /**
     * @var string|null
     */
    public $supportEmail;

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'locale'        => $this->locale,
            'display_name'  => $this->displayName,
            'eula_url'      => $this->eulaUrl,
            'support_url'   => $this->supportUrl,
            'support_email' => $this->supportEmail,
        );
    }
}
