<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

class SynchronizationResult
{
    public $success = true;
    public $createdServiceProviders = array();
    public $createdIdentityProviders = array();
    public $updatedServiceProviders = array();
    public $updatedIdentityProviders = array();
    public $removedServiceProviders = array();
    public $removedIdentityProviders = array();
}
