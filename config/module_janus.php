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
    'entity.prettyname' => 'name:en',

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
            'isDeployable' => true
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
            'prodaccepted' => array(
                'role' => array(
                    'admin',
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

        // Add metadata
        'validatemetadata' => array(
            'default' => TRUE,
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
            'prodaccepted' => array(
                'role' => array(
                    'admin',
                ),
            ),
        ),

        'prodaccepted' => array(
            'testaccepted' => array(
                'role' => array(
                    'admin',
                ),
            ),
        ),
    ),

    'metadata_refresh_cron_tags'      => array('hourly'),
    'validate_entity_certificate_cron_tags' => array('daily'),
    'validate_entity_endpoints_cron_tags' => array('daily'),
    'ca_bundle_file' => '/etc/pki/tls/certs/ca-bundle.crt',

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

require 'module_janus_metadata_fields.php';
$config += $fields;

$localConfig = '/etc/surfconext/serviceregistry.module_janus.php';
if (!file_exists($localConfig)) {
    die('No local JANUS config file at ' . $localConfig);
}
require $localConfig;
