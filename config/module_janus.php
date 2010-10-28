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

    'auth' => 'database',
    'useridattr' => 'email',

    /**
     * Mailtoken specific stuff
     */

    // Token lifetime in seconds
    'token.lifetime' => 3600*24,

    // Content of token mail
    'email' => array(
        'en' => array(
            'body' => '
                <html>
                <head>
                <title>JANUS token</title>
                </head>
                <body>
                <p>To login to JANUS click the following link:</p>
                <a href="%RETURNURL%?token=%TOKEN%&source=mailtoken">%RETURNURL%?token=%TOKEN%&source=mailtoken</a>
                <p>If the link does not work, please try to copy the link
                directly into your browsers address bar.</p>
                <p>In case of problems contact the WAYF Secreteriat.</p>
                <br />
                <p>Best regards</p>
                <p>WAYF Secreteriat</p>
                <p>sekretariat@wayf.dk</p>
                </body>
                </html>',
            'headers' => 'MIME-Version: 1.0' . "\r\n".
                'Content-type: text/html; charset=iso-8859-1' . "\r\n".
                'From: JANUS <no-reply@wayf.dk>' . "\r\n" .
                'Reply-To: WAYF <sekretariatet@wayf.dk>' . "\r\n" .
                'X-Mailer: PHP/' . phpversion(),
            'subject' => 'JANUS: Login token',
        ),
        'da' => array(
            'body' => '
                <html>
                <head>
                <title>JANUS token</title>
                </head>
                <body>
                <p>For at logge ind i JANUS, klik p&aring; linket:</p>
                <a href="%RETUENURL%?token=%TOKEN%&source=mailtoken">%RETURNURL%?token=%TOKEN%&source=mailtoken</a>
                <p>Hvis det ikke virker, pr&oslash;v at kopiere linket til
                adressefeltet i din browser.</p>
                <p>I tilf&aelig;lde af problemer med JANUS, kontakt WAYF
                sekretariatet.</p>
                <br />
                <p>Venlig hilsen</p>
                <p>WAYF sekretariatet</p>
                <p>sekretariat@wayf.dk</p>
                </body>
                </html>
            ',
            'headers' => 'MIME-Version: 1.0' . "\r\n".
                'Content-type: text/html; charset=iso-8859-1' . "\r\n".
                'From: JANUS <no-reply@wayf.dk>' . "\r\n" .
                'Reply-To: WAYF <sekretariatet@wayf.dk>' . "\r\n" .
                'X-Mailer: PHP/' . phpversion(),
            'subject' => 'JANUS: Login token',
        ),
    ),

    /*
     * Configuration for the database connection.
     */
    'store' => array(
        'dsn'       => 'mysql:host=localhost;dbname=janus',
        'username'  => 'root',
        'password'  => 'engineblock', 
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
    'entity.prettyname' => 'displayname:nl',

    /*
     * Janus supports a blacklist (mark idps that are not allowed to connect to an sp) 
     * and/or a whitelist (mark idps that are allowed to connect to an sp). 
     * You can enable both to make this choice per entity.
     */
    'entity.useblacklist' => true,
    'entity.usewhitelist' => true,
    
    
    /*
     * Enable entity types
     */
    'enable.saml20-sp' =>   true,
    'enable.saml20-idp' =>  true,
    'enable.shib13-sp' =>   false,
    'enable.shib13-idp' =>  false,


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
     * Allowed attribute names
     */
    'attributes' => array(
        'cn' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'sn' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'gn' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'eduPersonPrincipalName' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'mail' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'eduPersonPrimaryAffiliation' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'organizationName' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'norEduPersonNIN' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'schacPersonalUniqueID' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'eduPersonScopedAffiliation' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'preferredLanguage' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'eduPersonEntitlement' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'norEduPersonLIN' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'eduPersonAssurance' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'schacHomeOrganization' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'eduPersonTargetdID' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
    ),

    /*
     * Allowed metadata names for IdPs.
     */
    'metadatafields.saml20-idp' => array(
        // Endpoint fields
        'SingleSignOnService:0:Binding' => array(
            'type' => 'text',
            'order' => 110,
            'default' => 'CHANGE THIS',
            'description' => array(
                'nl' => 'Endepunkts type for forbindelser der understøtter Single Sign On profilen [SAMLProf].',
                'en' => 'Binding for the single sign on endpoint for connection that supports Single Sign On profile [SAMLProf].',
                'es' => 'Uno o más elementos de tipo EndpointType que describen los receptores que soportan los perfiles del protocolo de Peticion de Autenticacion definidos en [SAMLProf].',
            ),
            'required' => true,
        ),
        'SingleSignOnService:0:Location' => array(
            'type' => 'text',
            'order' => 120,
            'default' => 'CHANGE THIS',
            'description' => array(
                'nl' => 'Endepunkt for forbindelser der understøtter Single Sign On profilen [SAMLProf].',
                'en' => 'Endpoint for connection that supports the Single Sign On profile [SAMLProf].',
                'es' => 'Uno o más elementos de tipo EndpointType que describen los receptores que soportan los perfiles del protocolo de Peticion de Autenticacion definidos en [SAMLProf].',
            ),
            'required' => true,
            'validate' => 'isurl',
        ),
        // Certificate fields 
        'certData' => array(
            'type' => 'text',
            'order' => 210,
            'default' => 'CHANGE THIS',
            'description' => array(
                'nl' => 'Base 64 encoded certifikat brugt til denne forbindelse.',
                'en' => 'Base 64 encoded certificate used for this connection.',
                'es' => 'Certificado codificado en base 64.',
            ),
            'required' => true,
        ),
        // Information fields
        'name:nl' => array(
            'type' => 'text',
            'order' => 310,
            'default' => '',
            'description' => array(
                'nl' => 'De nederlandse naam van deze verbinding',
                'en' => 'The dutch name of this connection.',
            ),
            'required' => true,
        ),
        'name:en' => array(
            'type' => 'text',
            'order' => 311,
            'default' => '',
            'description' => array(
                'nl' => 'Forbindelsens navn på engelsk',
                'en' => 'The english name of this connection.',
                'es' => 'El nombre Inglés de esta conexión.',
            ),
            'required' => true,
        ),
        'displayName:nl' => array(
            'type' => 'text',
            'order' => 320,
            'default' => '',
            'description' => array(
                'nl' => '',
                'en' => 'The dutch name of this connection.',
            ),
            'required' => true,
        ),
        'displayName:en' => array(
            'type' => 'text',
            'order' => 321,
            'default' => '',
            'description' => array(
                'nl' => '',
                'en' => 'The display name for this entity (used in discovery service).',
                'es' => '',
            ),
            'required' => true,
        ),
        'description:nl' => array(
            'type' => 'text',
            'order' => 330,
            'default' => '',
            'description' => array(
                'nl' => 'Dansk beskrivelse af forbindelsen.',
                'en' => 'A danish description of this connection.',
                'es' => 'Una descripción danés de esta conexión.',
            ),
            'required' => true,
        ),
        'description:en' => array(
            'type' => 'text',
            'order' => 331,
            'default' => 'defaultvalue',
            'description' => array(
                'nl' => 'Engelsk beskrivelse af forbindelsen.',
                'en' => 'A english description of this connection.',
                'es' => 'Una descripción Inglés de esta conexión.',
            ),
            'required' => true,
        ),
        'url:nl' => array(
            'type' => 'text',
            'order' => 400,
            'default' => 'defaultvalue',
            'description' => array(
                'nl' => 'En URL til flere informationer om forbindelsen.',
                'en' => 'An URL pointing to more information about the connection.',
                'es' => 'URL del proveedor de identidad.',
            ),
        ),
        'url:en' => array(
            'type' => 'text',
            'order' => 401,
            'default' => 'defaultvalue',
            'description' => array(
                'nl' => 'En URL til flere informationer om forbindelsen.',
                'en' => 'An URL pointing to more information about the connection.',
                'es' => 'URL del proveedor de identidad.',
            ),
        ),
        'logo:0:url'  => array(
            'type' => 'text',
            'order' => 500,
            'default' => 'https://.png',
            'description' => array(
                'nl' => 'Fil med logo som bliver vist sammen med forbindelsens navn i discovery servicen.',
                'en' => 'A URL to the logo which will be shown next to this IdP in the discovery service.',
            ),
        ),
        'logo:0:width' => array(
            'type' => 'text',
            'order' => 501,
            'default' => '',
            'description' => array(
                'nl' => '',
                'en' => 'Logo width in pixels',
            ),
        ),
        'logo:0:height' => array(
            'type' => 'text',
            'order' => 502,
            'default' => '35',
            'description' => array(
                'nl' => '',
                'en' => 'Logo height in pixels',
            ),
        ),
        'logo:0:href' => array(
            'type' => 'text',
            'order' => 503,
            'default' => 'https://.png',
            'description' => array(
                'nl' => 'Fil med logo som bliver vist sammen med forbindelsens navn i discovery servicen.',
                'en' => 'Link the user is redirected to after clicking on the logo.',
            ),
        ),
        'geoLocation' => array(
            'type' => 'text',
            'order' => 510,
            'default' => '',
            'description' => array(
                'nl' => '',
                'en' => 'Coordinates in decimal form using the World Geodetic System (2d) coordinate system.'.
                        '<a href="http://www.online-tech-tips.com/computer-tips/find-longitude-latitude/">'.
                        ' Use a mapping tool to find the coordinates of a place.</a>',
            ),
        ),
        // Organization fields
        'organization:OrganizationName' => array(
            'type' => 'text',
            'order' => 700,
            'default' => 'defaultvalue',
            'supported' => array('nl','en','es'),
            'description' => array(
                'nl' => 'Navn på organisationen som forbindelsen tilhører.',
                'en' => 'Optional element identifying the organization responsible for the SAML entity described by the element.',
                'es' => 'Elemento opcional que identifica la organización responsable de la entidad SAML.',
            ),
        ),
        'organization:OrganizationDisplayName' => array(
            'type' => 'text',
            'order' => 701,
            'default' => 'defaultvalue',
            'supported' => array('nl','en','es'),
            'description' => array(
                'nl' => 'Beskrivelse af organisationen som forbindelsen tilhører.',
                'en' => 'Optional element describing the organization responsible for the SAML entity.',
                'es' => 'Elemento opcional que describe la organización responsable de la entidad SAML.',
            ),
        ),
        'organization:OrganizationURL' => array(
            'type' => 'text',
            'order' => 702,
            'default' => 'defaultvalue',
            'supported' => array('nl','en','es'),
            'description' => array(
                'nl' => 'En URL til flere informationer om forbindelsen.',
                'en' => 'URL that specify a location to which to direct a user for additional information.',
                'es' => 'URL que especifica una dirección a la que se puede dirigir un usuario para obtener información adicional.',
            ),
            'validate' => 'isurl',
        ),
        // Control fields
        'redirect.sign' => array(
            'type' => 'boolean',
            'order' => 800,
            'default' => true,
            'description' => array(
                'nl' => 'Kræv signering af requests.',
                'en' => 'Demand signing of requests.',
            ),
            'required' => true,
        ),
        'redirect.validate' => array(
            'type' => 'boolean',
            'order' => 810,
            'default' => true,
            'description' => array(
                'nl' => 'Valider signatur på requests.',
                'en' => 'Validate signature on requests and responses',
            ),
            'required' => true,
        ),
    ),

    /*
     * Allowed metadata names for SPs.
     */
    'metadatafields.saml20-sp' => array(
        // Endpoint fields
        'AssertionConsumerService:0:Binding' => array(
            'type' => 'text',
            'order' => 110,
            'default' => 'CHANGE THIS',
            'description' => array(
                'nl' => 'Endepunkts type for forbindelser der understøtter Authentication Request protokollen [SAMLProf].',
                'en' => 'Binding for the endpoint for connection that supports the Authentication Request protocol [SAMLProf].',
                'es' => 'Uno o mas elementos que describen los endpoints indexados que soportan los perfiles del protocolo de Peticion de Autenticacion definido en [SAMLProf]. Todos los proveedores de servicios soportan al menos un endpoint por definicion.',
            ),
            'required' => true,
        ),
        'AssertionConsumerService:0:Location' => array(
            'type' => 'text',
            'order' => 120,
            'default' => 'CHANGE THIS',
            'description' => array(
                'nl' => 'Endepunkt for forbindelser der understøtter Authentication Request protokollen [SAMLProf].',
                'en' => 'Endpoint for connection that supports the Authentication Request protocol [SAMLProf].',
                'es' => 'Uno o mas elementos que describen los endpoints indexados que soportan los perfiles del protocolo de Peticion de Autenticacion definido en [SAMLProf]. Todos los proveedores de servicios soportan al menos un endpoint por definicion.',
            ),
            'required' => true,
            'validate' => 'isurl',
        ),
        'AssertionConsumerService:0:index' => array(
            'type' => 'text',
            'order' => 130,
            'default' => 'defaultvalue',
            'description' => array(
                'nl' => 'Endepunkts index for forbindelser der understøtter Authentication Request protokollen [SAMLProf].',
                'en' => 'Index for the endpoint for connection that supports the Authentication Request protocol [SAMLProf].',
                'es' => 'Uno o mas elementos que describen los endpoints indexados que soportan los perfiles del protocolo de Peticion de Autenticacion definido en [SAMLProf]. Todos los proveedores de servicios soportan al menos un endpoint por definicion.',
            ),
            'required' => false,
            'validate' => 'isurl',
        ),
        'coin:gadgetbaseurl' => array(
             'type'=>'text',
             'order' => 180,
             'default' => '',
             'description' => array(
                 'nl' => 'COIN Gadget Base URL regex (bijv. .*\\.gadgets\\.google\\.com)',
                 'en' => 'COIN Gadget Base URL regex (e.g. .*\\.gadgets\\.google\\.com)',
             ),
        ),
        'coin:oauth:secret' => array(
            'type' => 'text',
            'order' => 190,
            'default' => 'see the silver surfer surf said sea softly',
            'description' => array(
                'nl' => '',
                'en' => 'OAuth secret for this Service Provider.',
            ),
        ),
        'coin:oauth:consumer_key'=> array(
            'type' => 'text',
            'order' => 191,
            'default' => 'gadgetConsumer',
            'description' => array(
                'nl' => 'A value used by the Consumer to identify itself to the Service Provider.',
                'en' => 'A value used by the Consumer to identify itself to the Service Provider.',
            ),
        ),
        'coin:oauth:consumer_secret' => array(
            'type' => 'text',
            'order' => 192,
            'default' => 'gadgetSecret',
            'description' => array(
                'nl' => 'A secret used by the Consumer to establish ownership of the Consumer Key.',
                'en' => 'A secret used by the Consumer to establish ownership of the Consumer Key.',
            ),
        ),
        'coin:oauth:key_type' => array(
            'type' => 'text',
            'order' => 193,
            'default' => 'HMAC_SYMMETRIC',
            'description' => array(
                'nl' => '.',
                'en' => 'OAuth secret for this Service Provider.',
            ),
        ),
        // Certificate fields 
        'certData' => array(
            'type' => 'text',
            'order' => 210,
            'default' => 'CHANGE THIS',
            'description' => array(
                'nl' => 'Base 64 encoded certifikat brugt til denne forbindelse.',
                'en' => 'Base 64 encoded certificate used for this connection.',
                'es' => 'Certificado codificado en base 64.',
            ),
            'required' => true,
        ),
        // Information fields
        'name:nl' => array(
            'type' => 'text',
            'order' => 310,
            'default' => 'defaultvalue',
            'description' => array(
                'nl' => 'Forbindelsens navn på dansk.',
                'en' => 'The danishh name of this connection.',
                'es' => 'El nombre danés de esta conexión.',
            ),
            'required' => true,
        ),
        'name:en' => array(
            'type' => 'text',
            'order' => 311,
            'default' => 'defaultvalue',
            'description' => array(
                'nl' => 'Forbindelsens navn på engelsk',
                'en' => 'The english name of this connection.',
                'es' => 'El nombre Inglés de esta conexión.',
            ),
            'required' => true,
        ),
        'displayName:nl' => array(
            'type' => 'text',
            'order' => 320,
            'default' => '',
            'description' => array(
                'nl' => '',
                'en' => 'The Dutch display name for this entity (used in discovery service).',
            ),
            'required' => true,
        ),
        'displayName:en' => array(
            'type' => 'text',
            'order' => 321,
            'default' => '',
            'description' => array(
                'nl' => '',
                'en' => 'The English display name for this entity (used in discovery service).',
                'es' => '',
            ),
            'required' => true,
        ),
        'description:nl' => array(
            'type' => 'text',
            'order' => 330,
            'default' => 'defaultvalue',
            'description' => array(
                'nl' => 'Dansk beskrivelse af forbindelsen.',
                'en' => 'A danish description of this connection.',
                'es' => 'Una descripción danés de esta conexión.',
            ),
            'required' => true,
        ),
        'description:en' => array(
            'type' => 'text',
            'order' => 331,
            'default' => 'defaultvalue',
            'description' => array(
                'nl' => 'Engelsk beskrivelse af forbindelsen.',
                'en' => 'A english description of this connection.',
                'es' => 'Una descripción Inglés de esta conexión.',
            ),
            'required' => true,
        ),
        'url:nl' => array(
            'type' => 'text',
            'order' => 400,
            'default' => 'defaultvalue',
            'description' => array(
                'nl' => 'En URL til flere informationer om forbindelsen.',
                'en' => 'An URL pointing to more information about the connection.',
                'es' => 'URL del proveedor de identidad.',
            ),
        ),
        'url:en' => array(
            'type' => 'text',
            'order' => 401,
            'default' => 'defaultvalue',
            'description' => array(
                'nl' => 'En URL til flere informationer om forbindelsen.',
                'en' => 'An URL pointing to more information about the connection.',
                'es' => 'URL del proveedor de identidad.',
            ),
        ),
        // Organization fields
        'organization:OrganizationName' => array(
            'type' => 'text',
            'order' => 501,
            'default' => 'defaultvalue',
            'supported' => array('nl','en','es'),
            'description' => array(
                'nl' => 'Navn på organisationen som forbindelsen tilhører.',
                'en' => 'Optional element identifying the organization responsible for the SAML entity described by the element.',
                'es' => 'Elemento opcional que identifica la organización responsable de la entidad SAML.',
            ),
        ),
        'organization:OrganizationDisplayName' => array(
            'type' => 'text',
            'order' => 502,
            'default' => 'defaultvalue',
            'supported' => array('nl','en','es'),
            'description' => array(
                'nl' => 'Beskrivelse af organisationen som forbindelsen tilhører.',
                'en' => 'Optional element describing the organization responsible for the SAML entity.',
                'es' => 'Elemento opcional que describe la organización responsable de la entidad SAML.',
            ),
        ),
        'organization:OrganizationURL' => array(
            'type' => 'text',
            'order' => 503,
            'default' => 'defaultvalue',
            'supported' => array('nl','en','es'),
            'description' => array(
                'nl' => 'En URL til flere informationer om forbindelsen.',
                'en' => 'URL that specify a location to which to direct a user for additional information.',
                'es' => 'URL que especifica una dirección a la que se puede dirigir un usuario para obtener información adicional.',
            ),
            'validate' => 'isurl',
        ),
        // Control fields
        'redirect.sign' => array(
            'type' => 'boolean',
            'order' => 810,
            'default' => true,
            'description' => array(
                'nl' => 'Kræv signering af requests.',
                'en' => 'Demand signing of requests.',
            ),
            'required' => true,
        ),
        'redirect.validate' => array(
            'type' => 'boolean',
            'order' => 820,
            'default' => true,
            'description' => array(
                'nl' => 'Valider signatur på requests.',
                'en' => 'Validate signature on requests and responses',
            ),
            'required' => true,
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

    /*
     * Cron tags says when Janus hook is executed
     * Uncomment to enable the cron job
     */
    //'cron' => array('daily'),

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
        
        // Federation tab
        'federationtab' => array(
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
);
?>
