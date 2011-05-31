<?php
/**
 * Config file for JANUS
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author Sixto Martín, <smartin@yaco.es>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id: module_janus.php 419 2010-06-07 13:07:31Z jach@wayf.dk $
 */
$config = array(

    'admin.name' => 'WAYF sekretariatet',
    'admin.email' => 'sekretariatet@wayf.dk',

    /*
     * Auth source used to gain access to JANUS
     */
    'auth' => 'default-sp',
    #'auth'=>'admin',
    /*
     * Attibute used to identify users
     */
    'useridattr' => 'NameID',
    #'useridattr' => 'user',

    /*
     * Configuration for the database connection.
     */
    'store' => array(
        'dsn'       => 'mysql:host=localhost;dbname=serviceregistry',
        'username'  => 'serviceregistry',
        'password'  => 'serviceregistry',
        'prefix'    => 'janus__',
    ),

    /*
     * Automatically create a new user if user do not exists on login
     */
    'user.autocreate' => true,

    /*
     * Dashboard configuration.
     */
    'dashboard.inbox.paginate_by' => 20,

    /*
     * Metadata field used as pretty name for entities
     */
    'entity.prettyname' => 'displayname:en',

    /*
     * Enable entity types
     */
    'enable.saml20-sp' =>   true,
    'enable.saml20-idp' =>  true,
    'enable.shib13-sp' =>   false,
    'enable.shib13-idp' =>  false,

    /*
     * Janus supports a blacklist (mark idps that are not allowed to connect to an sp) 
     * and/or a whitelist (mark idps that are allowed to connect to an sp). 
     * You can enable both to make this choice per entity.
     */
    'entity.useblacklist' => true,
    'entity.usewhitelist' => true,
    
    /*
     * Enable self user creation
     */
    'usercreation.allow' => true,

    /*
     * Configuration of systems in JANUS.
     */
    'workflowstates' => array(
        'testaccepted' => array(
            'name' => array(
                'en' => 'Test',
                'da' => 'Test',
                'es' => 'testaccepted - es',
            ),
            'description' => array(
                'en' => 'All test should be performed in this state',
                'da' => 'I denne tilstand skal al test foretages',
                'es' => 'Desc 1 es',
            ),
        ),
        'QApending' => array(
            'name' => array(
                'en' => 'Pending QA',
                'da' => 'Afventer QA',
                'es' => 'QApending - es',
            ),
            'description' => array(
                'en' => 'Move the connection to QA. The operations team will check that all coonditions for entering QA is meet.',
                'da' => 'Flyt forbindelsen til QA. Driften vil kontrollerer at forbindelsen overholder alle betingelser før forbindelsen flyttes til QA',
                'es' => 'Desc 2 es',
            ),
        ),
        'QAaccepted' => array(
            'name' => array(
                'en' => 'QA',
                'da' => 'QA',
                'es' => 'QAaccepted - es',
            ),
            'description' => array(
                'en' => 'The connection is on the QA system.',
                'da' => 'Forbindelsen er på QA systemet.',
                'es' => 'Desc 3 es',
            ),
        ),
        'prodpending' => array(
            'name' => array(
                'en' => 'Pending Production',
                'da' => 'Afventer Produktion',
                'es' => 'prodpending - es',
            ),
            'description' => array(
                'en' => 'Move the connection to Production. The operations team will check that all coonditions for entering Production is meet.',
                'da' => 'Flyt forbindelsen til Produktion. Driften vil kontrollerer at forbindelsen overholder alle betingelser før forbindelsen flyttes til Produktion',
                'es' => 'Desc 4 es',
            ),
        ),
        'prodaccepted' => array(
            'name' => array(
                'en' => 'Production',
                'da' => 'Produktion',
                'es' => 'prodaccepted - es',
            ),
            'description' => array(
                'en' => 'The connection is on the Production system',
                'da' => 'Forbindelsen er på Produktions systemet',
                'es' => 'Desc 5 es',
            ),
        ),
    ),

    // Default workflow state when creating new entities
    'workflowstate.default' => 'testaccepted',

    /*
     * Allowed attributes
     */
    'attributes' => array(
        'cn',
        'sn',
        'gn',
        'eduPersonPrincipalName',
        'mail',
        'eduPersonPrimaryAffiliation',
        'organizationName',
        'norEduPersonNIN',
        'schacPersonalUniqueID',
        'eduPersonScopedAffiliation',
        'preferredLanguage',
        'eduPersonEntitlement',
        'norEduPersonLIN',
        'eduPersonAssurance',
        'schacHomeOrganization',
        'eduPersonTargetedID',
    ),

    /*
     * Allowed metadata names for IdPs.
     */
    'metadatafields.saml20-idp' => array(
        // Endpoint fields
        'SingleSignOnService:#:Binding' => array(
            'type' => 'select',
            'select_values' => array(
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'urn:oasis:names:tc:SAML:2.0:bindings:PAOS',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
                'urn:oasis:names:tc:SAML:2.0:bindings:URI'
            ),
            'default' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'default_allow' => true,
            'order' => 110,
            'required' => true,
            'supported' => array(0),
        ),
        'SingleSignOnService:#:Location' => array(
            'type' => 'text',
            'order' => 120,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'validate' => 'isurl',
            'validate_error' => 'The value must be a valid url (e.g. https://example.com/hello.html)',
            'supported' => array(0),
        ),
        'SingleLogoutService:#:Binding' => array(
            'type' => 'select',
            'select_values' => array(
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'urn:oasis:names:tc:SAML:2.0:bindings:PAOS',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
                'urn:oasis:names:tc:SAML:2.0:bindings:URI'
            ),
            'default' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'default_allow' => true,
            'order' => 130,
            'required' => false,
            'supported' => array(0),
        ),
        'SingleLogoutService:#:Location' => array(
            'type' => 'text',
            'order' => 140,
            'default' => '',
            'default_allow' => false,
            'required' => false,
            'validate' => 'isurl',
            'validate_error' => 'The value must be a valid url (e.g. https://example.com/hello.html)',
            'supported' => array(0),
        ),
        'SingleLogoutService:#:ResponseLocation' => array(
            'type' => 'text',
            'order' => 145,
            'default' => '',
            'default_allow' => false,
            'required' => false,
            'validate' => 'isurl',
            'validate_error' => 'The value must be a valid url (e.g. https://example.com/hello.html)',
            'supported' => array(0),
        ),
        'coin:guest_qualifier' => array(
            'type' => 'text',
            'order' => 193,
            'default' => 'All',
            'default_allow' => true,
            'required' => true,
        ),
        
        // Certificate fields 
        'certData' => array(
            'type' => 'text',
            'order' => 210,
            'default' => '',
            'default_allow' => false,
            'required' => false,
        ),
        'certFingerprint:#' => array(
            'type' => 'text',
            'order' => 220,
            'default' => '',
            'default_allow' => false,
            'required' => false,
            'validate' => 'leneq40',
            'validate_error' => 'The value must be 40 characters long',
            'supported' => array(0),
        ),
        'certificate' => array(
            'type' => 'file',
            'order' => 230,
            'filetype' => '*.pem', // *.jpg; *.gif; *.*
            'maxsize' => '3 M', // Valid units are B, KB, MB, and GB. The default unit is KB.
            'required' => false,
        ),

        // Information fields
        'name:#' => array(
            'type' => 'text',
            'order' => 310,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array('en', 'nl'),
        ),
        'displayName:#' => array(
            'type' => 'text',
            'order' => 315,
            'default' => '',
            'default_allow' => false,
            'supported' => array('en', 'nl'),
            'required'=>true,
        ),
        'description:#' => array(
            'type' => 'text',
            'order' => 320,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array('en', 'nl'),
        ),
        'url:#' => array(
            'type' => 'text',
            'order' => 330,
            'default' => '',
            'default_allow' => false,
            'supported' => array('en', 'nl'),
        ),
        'icon' => array(
            'type' => 'file',
            'order' => 340,
            'filetype' => '*.jpg', // *.jpg; *.gif; *.*
            'maxsize' => '100', // Valid units are B, KB, MB, and GB. The default unit is KB.
        ),
        
        // MDUI stuff
        'logo:#:url'  => array(
            'type' => 'text',
            'order' => 500,
            'default' => 'https://.png',
            'default_allow' => false,
            'required' => true,
            'supported' => array(0)
        ),
        'logo:#:width' => array(
            'type' => 'text',
            'order' => 501,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array(0)
        ),
        'logo:#:height' => array(
            'type' => 'text',
            'order' => 502,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array(0)
        ),
        'logo:#:href' => array(
            'type' => 'text',
            'order' => 503,
            'default' => 'https://.png',
            'default_allow' => false,
            'required' => true,
            'supported' => array(0)
        ),
        'geoLocation' => array(
            'type' => 'text',
            'order' => 510,
            'default' => '',
        ),
        'keywords:#' => array(
            'type' => 'text',
            'order' => 511,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported'=>array('en','nl'),
        ),
        
        // Contact person fields
        'contacts:#:contactType' => array(
            'type' => 'select',
            'order' => 410,
            'default' => 'technical',
            'select_values' => array("technical", "support", "administrative", "billing", "other"),
            'default_allow' => true,
            'required' => true,
            'supported' => array(0)
        ),
        'contacts:#:givenName' => array(
            'type' => 'text',
            'order' => 421,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array(0)
        ),
        'contacts:#:surName' => array(
            'type' => 'text',
            'order' => 422,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array(0)
        ),
        'contacts:#:emailAddress' => array(
            'type' => 'text',
            'order' => 430,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'validate' => 'isemail',
            'validate_error' => 'The value must be a valid email address',
            'supported' => array(0)
        ),
        'contacts:#:telephoneNumber' => array(
            'type' => 'text',
            'order' => 440,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array(0)
        ),
        'contacts:#:company' => array(
            'type' => 'text',
            'order' => 450,
            'default' => '',
            'default_allow' => false,
            'supported' => array(0)
        ),
        
        // Organization fields
        'OrganizationName:#' => array(
            'type' => 'text',
            'order' => 510,
            'default' => '',
            'default_allow' => false,
            'supported' => array('en', 'nl'),
        ),
        'OrganizationDisplayName:#' => array(
            'type' => 'text',
            'order' => 520,
            'default' => '',
            'default_allow' => false,
            'supported' => array('en', 'nl'),
        ),
        'OrganizationURL:#' => array(
            'type' => 'text',
            'order' => 530,
            'default' => '',
            'default_allow' => false,
            'supported' => array('en', 'nl'),
            'validate' => 'isurl',
            'validate_error' => 'The value must be a valid url (e.g. https://example.com/hello.html)',
        ),
        
        // Control fields
        'redirect.sign' => array(
            'type' => 'boolean',
            'order' => 810,
            'default' => true,
            'default_allow' => true,
            'required' => false,
        ),
        'redirect.validate' => array(
            'type' => 'boolean',
            'order' => 820,
            'default' => true,
            'default_allow' => true,
            'required' => false,
        ),
        'base64attributes' => array(
            'type' => 'boolean',
            'order' => 830,
            'default' => true,
            'default_allow' => true,
            'required' => false,
        ),
    ),

    /*
     * Allowed metadata names for SPs.
     */
    'metadatafields.saml20-sp' => array(
        // Endpoint fields
        'AssertionConsumerService:#:Binding' => array(
            'type' => 'select',
            'select_values' => array(
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'urn:oasis:names:tc:SAML:2.0:bindings:PAOS',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
                'urn:oasis:names:tc:SAML:2.0:bindings:URI'
            ),
            'default' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'default_allow' => true,
            'order' => 110,
            'required' => true,
            'supported' => array(0),
        ),
        'AssertionConsumerService:#:Location' => array(
            'type' => 'text',
            'order' => 120,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'validate' => 'isurl',
            'validate_error' => 'The value must be a valid url (e.g. https://example.com/hello.html)',
            'supported' => array(0),
        ),
        'AssertionConsumerService:#:index' => array(
            'type' => 'text',
            'order' => 130,
            'default' => '',
            'default_allow' => false,
            'required' => false,
            'supported' => array(0),
        ),
        'SingleLogoutService:#:Binding' => array(
            'type' => 'select',
            'select_values' => array(
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'urn:oasis:names:tc:SAML:2.0:bindings:PAOS',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
                'urn:oasis:names:tc:SAML:2.0:bindings:URI'
            ),
            'default' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'default_allow' => true,
            'order' => 130,
            'required' => false,
            'supported' => array(0),
        ),
        'SingleLogoutService:#:Location' => array(
            'type' => 'text',
            'order' => 140,
            'default' => '',
            'default_allow' => false,
            'required' => false,
            'validate' => 'isurl',
            'validate_error' => 'The value must be a valid url (e.g. https://example.com/hello.html)',
            'supported' => array(0),
        ),
        'SingleLogoutService:#:ResponseLocation' => array(
            'type' => 'text',
            'order' => 145,
            'default' => '',
            'default_allow' => false,
            'required' => false,
            'validate' => 'isurl',
            'validate_error' => 'The value must be a valid url (e.g. https://example.com/hello.html)',
            'supported' => array(0),
        ),

        // COIN stuff
        'coin:gadgetbaseurl' => array(
            'type'=>'text',
            'order' => 180,
            'default' => '',
            'default_allow' => false,
            'validate' => 'isurl',
            'validate_error' => 'The value must be a valid url (e.g. https://example.com/.*)',
            'required' => false,
        ),
        'coin:oauth:secret' => array(
            'type' => 'text',
            'order' => 190,
            'default' => 'see the silver surfer surf said sea softly',
            'default_allow' => true,
            'validate' => 'lengteq20',
            'validate_error' => 'The value must be at least 20 characters long',
            'required' => false,
        ),
        'coin:oauth:consumer_key'=> array(
            'type' => 'text',
            'order' => 191,
            'default' => '',
            'default_allow' => false,
            'required' => false,
        ),
        'coin:oauth:consumer_secret' => array(
            'type' => 'text',
            'order' => 192,
            'default' => '',
            'default_allow' => false,
            'validate' => 'lengteq20',
            'validate_error' => 'The value must be at least 20 characters long',
            'required' => false,
        ),
        'coin:oauth:key_type' => array(
            'type' => 'text',
            'order' => 193,
            'default' => 'HMAC_SYMMETRIC',
            'default_allow' => true,
            'required' => false,
        ),
        'coin:oauth:public_key' => array(
            'type' => 'text',
            'order' => 194,
            'default' => '',
            'default_allow' => false,
            'required' => false,
        ),
        'coin:oauth:app_title' => array(
            'type' => 'text',
            'order' => 195,
            'default' => 'Application Title',
            'default_allow' => false,
            'required' => false,
        ),
        'coin:oauth:app_description' => array(
            'type' => 'text',
            'order' => 196,
            'default' => 'description',
            'default_allow' => false,
            'required' => false,

        ),  
        'coin:oauth:app_thumbnail' => array(
            'type' => 'text',
            'order' => 197,
            'default' => 'http://www.surfnet.nl/thumb.png',
            'validate' => 'isurl',
            'validate_error' => 'The value must be a valid url (e.g. https://example.com/hello.html)',
            'default_allow' => false,
            'required' => false,

        ),
        'coin:oauth:app_icon' => array(
            'type' => 'text',
            'order' => 198,
            'default' => 'http://www.surfnet.nl/icon.gif',
            'validate' => 'isurl',
            'validate_error' => 'The value must be a valid url (e.g. https://example.com/hello.html)',
            'default_allow' => false,
            'required' => false,
        ),
        'coin:oauth:callback_url' => array(
            'type' => 'text',
            'order' => 199,
            'default' => '',
            'validate' => 'isurl',
            'validate_error' => 'The value must be a valid url (e.g. https://example.com/hello.html)',
            'default_allow' => false,
            'required' => false,
        ),
        'coin:expects_oids' => array(
            'type' => 'boolean',
            'order' => 200,
            'default' => false,
            'required' => FALSE,
        ),

        // Certificate fields
        'certData' => array(
            'type' => 'text',
            'order' => 210,
            'default' => '',
            'default_allow' => false,
            'required' => false,
        ),
        'certFingerprint:#' => array(
            'type' => 'text',
            'order' => 220,
            'default' => '',
            'default_allow' => false,
            'required' => false,
            'validate' => 'leneq40',
            'validate_error' => 'The value must be 40 characters long',
            'supported' => array(0),
        ),
        'certificate' => array(
            'type' => 'file',
            'order' => 230,
            'filetype' => '*.pem', // *.jpg; *.gif; *.*
            'maxsize' => '3 M', // Valid units are B, KB, MB, and GB. The default unit is KB.
            'required' => false,
        ),

        // Information fields
        'name:#' => array(
            'type' => 'text',
            'order' => 310,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array('en', 'nl')
        ),
        'displayName:#' => array(
            'type' => 'text',
            'order' => 315,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array('en', 'nl')
        ),
        'description:#' => array(
            'type' => 'text',
            'order' => 320,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array('en', 'nl')
        ),
        'url:#' => array(
            'type' => 'text',
            'order' => 330,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array('en', 'nl')
        ),
        'icon' => array(
            'type' => 'file',
            'order' => 340,
            'filetype' => '*.jpg', // *.jpg; *.gif; *.*
            'maxsize' => '100', // Valid units are B, KB, MB, and GB. The default unit is KB.
        ),

        // Contact person fields
        'contacts:#:contactType' => array(
            'type' => 'select',
            'order' => 410,
            'default' => 'technical',
            'select_values' => array("technical", "support", "administrative", "billing", "other"),
            'default_allow' => true,
            'required' => true,
            'supported' => array(0)
        ),
        'contacts:#:givenName' => array(
            'type' => 'text',
            'order' => 421,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array(0)
        ),
        'contacts:#:surName' => array(
            'type' => 'text',
            'order' => 422,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array(0)
        ),
        'contacts:#:emailAddress' => array(
            'type' => 'text',
            'order' => 430,
            'default' => '',
            'default_allow' => false,
            'validate' => 'isemail',
            'validate_error' => 'The value must be a valid email address',
            'required' => true,
            'supported' => array(0)
        ),
        'contacts:#:telephoneNumber' => array(
            'type' => 'text',
            'order' => 440,
            'default' => '',
            'default_allow' => false,
            'required' => true,
            'supported' => array(0)
        ),
        'contacts:#:company' => array(
            'type' => 'text',
            'order' => 450,
            'default' => '',
            'default_allow' => false,
            'supported' => array(0)
        ),

        // Organization fields
        'OrganizationName:#' => array(
            'type' => 'text',
            'order' => 510,
            'default' => '',
            'default_allow' => false,
            'supported' => array('en', 'nl'),
        ),
        'OrganizationDisplayName:#' => array(
            'type' => 'text',
            'order' => 520,
            'default' => '',
            'default_allow' => false,
            'supported' => array('en', 'nl'),
        ),
        'OrganizationURL:#' => array(
            'type' => 'text',
            'order' => 530,
            'default' => '',
            'default_allow' => false,
            'supported' => array('en', 'nl'),
            'validate' => 'isurl',
            'validate_error' => 'The value must be a valid url (e.g. https://example.com/hello.html)',
        ),
        
        // Control fields
        'redirect.sign' => array(
            'type' => 'boolean',
            'order' => 810,
            'default' => true,
            'default_allow' => true,
            'required' => false,
        ),
        'redirect.validate' => array(
            'type' => 'boolean',
            'order' => 820,
            'default' => true,
            'default_allow' => true,
            'required' => false,
        ),
        'base64attributes' => array(
            'type' => 'boolean',
            'order' => 830,
            'default' => true,
            'default_allow' => true,
            'required' => false,
        ),
        'assertion.encryption' => array(
            'type' => 'boolean',
            'order' => 830,
            'default' => false,
            'default_allow' => true,
            'required' => false,
        ),
    ),

    /*
     * Configuration of usertypes in JANUS.
     */
    'usertypes' => array(
        // Buildin admin user type. Define if you want to create more admin user
        // accounts.
        'admin',
        'operations',
        'secretariat',
        //SAML 2.0 contact types
        'technical',
        'support',
        'administrative',
        'billing',
        'other',
    ),

    'messenger.external' => array(
        'mail' => array(
            'class' => 'janus:SimpleMail',
            'name' => 'Mail',
            'option' => array(
                'headers' => 'MIME-Version: 1.0' . "\r\n".
                    'Content-type: text/html; charset=iso-8859-1' . "\r\n".
                    'From: JANUS <no-reply@example.org>' . "\r\n" .
                    'Reply-To: JANUS Admin <admin@example.org>' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion(),
            ),
        ),
    ),

    /*
     * Cron tags says when Janus hook is executed
     * Uncomment to enable the cron job
     */
    //'cron' => array('daily'),


    /*
     * Configuration of JANUS aggregators
     */
    'aggregators' => array(
        'prod-sp' => array(
            'state' => 'prodaccepted',
            'type' => 'saml20-sp',
        ),
        'prod-idp' => array(
            'state' => 'prodaccepted',
            'type' => 'saml20-idp',
        ),
    ),

    'export.external' => array(
        'filesystem' => array(
            'class' => 'janus:FileSystem',
            'name' => 'Filesystem',
            'option' => array(
                'path' => '/path/to/put/metadata.xml',
            ),
        ),
        'FTP' => array(
            'class' => 'janus:FTP',
            'name' => 'FTP',
            'option' => array(
                'host' => 'hostname',
                'path' => '/path/to/put/metadata.xml',
                'username' => 'jach',
                'password' => 'xxx',
            ),
        ),   
    ),

    'export.entitiesDescriptorName' => 'Federation',

    'maxCache'      => 60*60*24, // 24 hour cache time
    'maxDuration'   => 60*60*24*5, // Maximum 5 days duration on ValidUntil.

    /* Whether metadata should be signed. */
    'sign.enable' => FALSE,

    /* Private key which should be used when signing the metadata. */
    'sign.privatekey' => 'server.pem',

    /* Password to decrypt private key, or NULL if the private key is unencrypted. */
    'sign.privatekey_pass' => NULL,

    /* Certificate which should be included in the signature. Should correspond to the private key. */
    'sign.certificate' => 'server.crt',

        /*
     * Access configuration of JANUS.
     *
     * If a permission is not set for a given user for a given system, the default
     * permission is given.
     */
    'access' => array(
        // Change entity type
        'changeentitytype' => array(
            'default' => FALSE,
            'testaccepted' => array(
                'role' => array(
                    'all',
                ),
            ),
        ),

        // Export metadata
        'exportmetadata' => array(
            'default' => FALSE,
            'testaccepted' => array(
                'role' => array(
                    'all',
                ),
            ),
            'QAaccepted' => array(
                'role' => array(
                    'secretariat',
                    'operations',
                ),
            ),
            'prodaccepted' => array(
                'role' => array(
                    'secretariat',
                    'operations',
                ),
            ),
        ),

        // Block or unblock remote entities
        'blockremoteentity' => array(
            'default' => FALSE,
            'testaccepted' => array(
                'role' => array(
                    'all',
                ),
            ),
            'QAaccepted' => array(
                'role' => array(
                    'technical',
                    'secretariat',
                ),
            ),
        ),

        // Change workflow state
        'changeworkflow' => array(
            'default' => TRUE,
        ),

        // Change entityID
        'changeentityid' => array(
            'default' => TRUE,
            'testaccepted' => array(
                'role' => array(
                    'all',
                ),
            ),
        ),

        // Change ARP
        'changearp' => array(
            'default' => FALSE,
            'testaccepted' => array(
                'role' => array(
                    'all',
                ),
            ),
        ),

        // Edit ARP
        'editarp' => array(
            'default' => FALSE,
            'testaccepted' => array(
                'role' => array(
                    'all',
                ),
            ),
        ),

        // Add ARP
        'addarp' => array(
            'default' => FALSE,
            'testaccepted' => array(
                'role' => array(
                    'all',
                ),
            ),
        ),

        // Add metadata
        'addmetadata' => array(
            'default' => FALSE,
            'testaccepted' => array(
                'role' => array(
                    'all',
                ),
            ),
        ),

        // Delete metadata
        'deletemetadata' => array(
            'default' => FALSE,
            'testaccepted' => array(
                'role' => array(
                    'all',
                ),
            ),
        ),

        // Modify metadata
        'modifymetadata' => array(
            'default' => FALSE,
            'testaccepted' => array(
                'role' => array(
                    'all',
                ),
            ),
        ),

        // Import metadata
        'importmetadata' => array(
            'default' => FALSE,
            'testaccepted' => array(
                'role' => array(
                    'all',
                ),
            ),
        ),

        // History
        'entityhistory' => array(
            'default' => TRUE,
        ),

        // Disable consent
        'disableconsent' => array(
            'default' => FALSE,
            'testaccepted' => array(
                'role' => array(
                    'all',
                ),
            ),
            'QAaccepted' => array(
                'role' => array(
                    'technical',
                ),
            ),
        ),

        /*
         * General permissions
         */

        // Create new entity
        'createnewentity' => array(
            'role' => array(
                'all',
            ),
        ),

        // Show subscriptions
        'showsubscriptions' => array(
            'role' => array(
                'secretariat',
                'operations',
            ),
        ),

        // Add subscriptions
        'addsubscriptions' => array(
            'role' => array(
                'secretariat',
                'operations',
            ),
        ),

        // Edit subscriptions
        'editsubscriptions' => array(
            'role' => array(
                'secretariat',
                'operations',
            ),
        ),

        // Delete subscriptions
        'deletesubscriptions' => array(
            'role' => array(
                'secretariat',
                'operations',
            ),
        ),

        // Export all entities
        'exportallentities' => array(
            'role' => array(
                'operations',
                'admin',
                'secretariat',
            ),
        ),
        // ARP editor
        'arpeditor' => array(
            'role' => array(
                'operations',
                'admin',
                'secretariat',
            ),
        ),

        // Federation tab
        'federationtab' => array(
            'role' => array(
                'operations',
                'admin',
                'secretariat',
            ),
        ),

        // Adminitsartion tab
        'admintab' => array(
            'role' => array(
                'admin',
            ),
        ),

        // Adminitsartion users tab
        'adminusertab' => array(
            'role' => array(
                'admin',
            ),
        ),

        // Access to all entities
        'allentities' => array(
            'role' => array(
                'admin',
            ),
        ),
        'experimental' => array(
            'role' => array(
                'experimental'
            ),
        ),
    ),

    'workflow_states' => array(

        'testaccepted' => array(
            'QApending' => array(
                'role' => array(
                    'technical',
                    'secretariat',
                ),
            ),
        ),

        'QApending' => array(
            'QAaccepted' => array(
                'role' => array(
                    'secretariat',
                ),
            ),
            'testaccepted' => array(
                'role' => array(
                    'operations',
                    'technical',
                    'secretariat',
                ),
            ),
        ),

        'QAaccepted' => array(
            'testaccepted' => array(
                'role' => array(
                    'operations',
                    'technical',
                    'secretariat',
                ),
            ),
            'prodpending' => array(
                'role' => array(
                    'operations',
                ),
            ),
        ),

        'prodpending' => array(
            'prodaccepted' => array(
                'role' => array(
                    'secretariat',
                ),
            ),
            'testaccepted' => array(
                'role' => array(
                    'operations',
                    'technical',
                    'secretariat',
                ),
            ),
        ),

        'prodaccepted' => array(
            'testaccepted' => array(
                'role' => array(
                    'operations',
                    'technical',
                    'secretariat',
                ),
            ),
            'QApending' => array(
                'role' => array(
                    'operations',
                    'secretariat',               
                ),                     
            ),
        ),
    ),


    /**
     * Metalising configuration options
     *
     * The following options are for the metadlisting extension under the
     * federtion tab.
     * NOTE this extension is not experimental and not yet done. Also note that
     * this extension relies on to other modules in order to use the full
     * features of this extension:
     *
     *  - x509 https://forja.rediris.es/svn/confia/x509/trunk/
     *  - metalisting http://simplesamlphp-labs.googlecode.com/svn/trunk/modules/metalisting
     *
     *  Expect these options to change in the future
     */
    /*
    'cert.strict.validation' => true,
    'cert.allowed.warnings' => array(),
    'notify.cert.expiring.before' => 30,
    'notify.meta.expiring.before' =>  5,
     */
);

$localConfig = dirname(__FILE__) . '/module_janus.local.php';
if (file_exists($localConfig)) {
    require $localConfig;
}
