<?php

/**
 * The Surfnet_Identity class is responsible for storing
 * the metadata of a user.
 *
 * Usually the metadata is provided by an external source
 * like an Identity Provider.
 *
 * @author marc
 */
class SurfConext_Identity
{
    /**
     * Unique identifier for this identity
     *
     * @var string
     */
    public $id;

    /**
     * Display name to use in the interface for this user.
     * 
     * @var string
     */
    public $displayName;

    /**
     * @param string $id Unique Identifier
     */
    public function __construct($id)
    {
        $this->id = $id;
    }
}
