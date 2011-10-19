<?php

define('JANUS_FIELDS_TYPE_ALL' , '*');
define('JANUS_FIELDS_TYPE_IDP' , 'saml20-idp');
define('JANUS_FIELDS_TYPE_SP'  , 'saml20-sp');

$template = array(
    JANUS_FIELDS_TYPE_ALL => array(
        'name:#'                    => array('required'=>TRUE, 'supported' => array('en', 'nl')),
        'displayName:#'             => array(                  'supported' => array('en', 'nl')),
        'description:#'             => array('required'=>TRUE, 'supported' => array('en', 'nl')),

        'certData'                  => array(),
        'certData2'                 => array(),

        'contacts:#:contactType'    => array(
            'type' => 'select',
            'required' => TRUE,
            'supported' => array(0,1,2),
            'select_values' => array('technical', 'support', 'administrative', 'billing', 'other')
        ),
        'contacts:#:givenName'      => array('required' => TRUE, 'supported' => array(0,1,2)),
        'contacts:#:surName'        => array('required' => TRUE, 'supported' => array(0,1,2)),
        'contacts:#:emailAddress'   => array('required' => TRUE, 'supported' => array(0,1,2), 'validate'=>'isemail'),
        'contacts:#:telephoneNumber'=> array(                    'supported' => array(0,1,2)),

        'OrganizationName:#'        => array(                    'supported' => array('en', 'nl')),
        'OrganizationDisplayName:#' => array(                    'supported' => array('en', 'nl')),
        'OrganizationURL:#'         => array('validate' => 'isurl', 'supported' => array('en', 'nl')),

        'NameIDFormat' => array(
            'type' => 'select',
            'required'=>TRUE,
            'select_values' => array(
                'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                'urn:oasis:names:tc:SAML:1.1:nameid-format:persistent',
            ),
            'default' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
        ),
    ),

    JANUS_FIELDS_TYPE_IDP => array(
        // Endpoint fields
        'SingleSignOnService:0:Binding' => array(
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
            'required' => true,
        ),
        'SingleSignOnService:0:Location' => array('required' => true, 'validate' => 'isurl'),
        'certData'                  => array('required'=>TRUE),

        'coin:guest_qualifier' => array('required' => true, 'default' => 'All'),

        // MDUI stuff
        'keywords:#'    => array('required' => true, 'supported'=>array('en','nl')),
        'logo:0:url'    => array('required' => true, 'default' => 'https://.png', 'default_allow' => false),
        'logo:0:width'  => array('required' => true, 'default' => '120'),
        'logo:0:height' => array('required' => true, 'default' => '60'),
    ),

    JANUS_FIELDS_TYPE_SP => array(
        'AssertionConsumerService:0:Binding' => array(
            'type' => 'select',
            'select_values' => array(
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ),
            'default' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'required' => true,
        ),
        'AssertionConsumerService:0:Location' => array('required' => TRUE, 'validate' => 'isurl'),
        'redirect.sign'                       => array('type' => 'boolean', 'required' => TRUE, 'default' => false),

        'coin:eula'                     => array('validate' => 'isurl'),

        'coin:alternate_public_key'     => array(),
        'coin:alternate_private_key'    => array(),

        // OAuth
        'coin:gadgetbaseurl'            => array('validate' => 'isurl'),
        'coin:oauth:secret'             => array('validate' => 'lengteq20'),
        'coin:oauth:consumer_key'       => array(),
        'coin:oauth:consumer_secret'    => array('validate' => 'lengteq20'),
        'coin:oauth:key_type'           => array(
            'type'=>'select',
            'select_values'=>array('HMAC_SHA1', 'RSA_PRIVATE'),
            'default' => 'HMAC_SHA1',
        ),
        'coin:oauth:public_key'         => array(),
        'coin:oauth:app_title'          => array('default' => 'Application Title','default_allow' => false),
        'coin:oauth:app_description'    => array(),
        'coin:oauth:app_thumbnail'      => array('validate' => 'isurl', 'default' => 'https://www.surfnet.nl/thumb.png', 'default_allow' => false),
        'coin:oauth:app_icon'           => array('validate' => 'isurl', 'default' => 'https://www.surfnet.nl/icon.gif' ,'default_allow' => false),
        'coin:oauth:callback_url'       => array('validate' => 'isurl'),

        // Provisioning
        'coin:is_provision_sp'          => array('type' => 'boolean'),
        'coin:provision_domain'         => array(),
        'coin:provision_admin'          => array(),
        'coin:provision_password'       => array(),
        'coin:provision_type'           => array(
            'type' => 'select',
            'select_values' => array("none", "google"),
            'default' => 'google'
        ),
    ),
);

$fieldTemplates = new sspmod_janus_fieldsTemplates($template);
$fields = array(
    'metadatafields.saml20-idp' => $fieldTemplates->getIdpFields(),
    'metadatafields.saml20-sp'  => $fieldTemplates->getSpFields(),
);

/**
 * Fill out some defaults and apply ordering
 */
class sspmod_janus_fieldsTemplates
{
    protected $_templates;

    public function __construct($templates)
    {
        $this->_templates = $templates;
    }

    public function getSpFields()
    {
        return $this->_getFields(JANUS_FIELDS_TYPE_SP);
    }

    public function getIdpFields()
    {
        return $this->_getFields(JANUS_FIELDS_TYPE_IDP);
    }

    protected function _getFields($entityType)
    {
        $fields = array();
        foreach ($this->_templates[JANUS_FIELDS_TYPE_ALL] as $fieldName => $fieldTemplate) {
            $field = $this->_applyDefaults($fieldTemplate);
            $fields[$fieldName] = $field;
        }

        $templates = $this->_templates[$entityType];
        $entityFields = array();
        foreach ($templates as $fieldName => $fieldTemplate) {
            $field = $this->_applyDefaults($fieldTemplate);
            $entityFields[$fieldName] = $field;
        }
        $fields = static::_merge($fields, $entityFields);

        $fields = $this->_orderFields($fields);
        return $fields;
    }

    protected function _applyDefaults($fieldTemplate)
    {
        $field = $fieldTemplate;
        if (!isset($field['type'])) {
            $field['type'] = 'text';
        }
        if (isset($field['default']) && !isset($field['default_allow'])) {
            $field['default_allow'] = true;
        }
        if (!isset($field['default'])) {
            $field['default'] = '';
            $field['default_allow'] = false;
        }
        if (!isset($field['required'])) {
            $field['required'] = false;
        }
        return $field;
    }

    protected function _orderFields($fields)
    {
        $order = 0;
        foreach ($fields as &$field) {
            $order += 10;
            $field['order'] = $order;
        }
        return $fields;
    }

    protected static function _merge($array1, $array2)
    {
        foreach($array2 as $key => $Value)
        {
            if (array_key_exists($key, $array1) && is_array($Value)) {
              $array1[$key] = static::_merge($array1[$key], $array2[$key]);
            }
            else {
              $array1[$key] = $Value;
            }
        }
        return $array1;
    }
}