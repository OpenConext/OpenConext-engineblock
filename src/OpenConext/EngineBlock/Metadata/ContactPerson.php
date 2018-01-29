<?php

namespace OpenConext\EngineBlock\Metadata;

/**
 * Class ContactPerson
 * @package OpenConext\EngineBlock\Metadata
 */
class ContactPerson
{
    public $contactType;
    public $emailAddress = '';
    public $telephoneNumber = '';
    public $givenName = '';
    public $surName = '';

    /**
     * @param $contactType
     */
    public function __construct($contactType)
    {
        $this->contactType = $contactType;
    }
}
