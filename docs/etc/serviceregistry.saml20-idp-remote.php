<?php

/**
 * REQUIRED environment configuration for JANUS module
 * For a complete set of the available configuration options
 * please see serviceregistry/metadata/saml20-idp-remote.php
 */

$metadata['https://engineblock.example.edu/authentication/idp/metadata'] = array(
        'SingleSignOnService'   => 'https://engineblock.example.edu/authentication/idp/single-sign-on',
        'certificate'           =>'engineblock.crt',
);