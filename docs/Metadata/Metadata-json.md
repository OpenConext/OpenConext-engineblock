# 

Both:
```
{
    'type': 'saml20-sp'|'saml20-idp'                    // assembleConnection
    'state': 'prodaccepted'|'testaccepted'               // assembleCommon
    'metadata': {
        'name': [
            'nl': (string)                              // assembleCommon
            'en': (string)                              // assembleCommon
        ],
        'displayName': [
            'nl': (string)                              // assembleCommon
            'en': (string)                              // assembleCommon
        ],
        'description': [
            'nl': (string)                              // assembleCommon
            'en': (string)                              // assembleCommon
        ],
        'logo': [   
            {
                'url': (string)                         // assembleLogo
                'width': opt.???                        // assembleLogo
                'height': opt.???                       // assembleLogo
            }
        ],
        'OrganizationName': [
            'nl': (string|opt.),                        // assembleOrganization
            'en': (string|opt.)                         // assembleOrganization
        ],
        'OrganizationDisplayName': [
            'nl': (string|opt.),                        // assembleOrganization
            'en': (string|opt.)                         // assembleOrganization
        ],
        'OrganizationURL': [
            'nl': (string|opt.),                        // assembleOrganization
            'en': (string|opt.)                         // assembleOrganization
        ],
        'keywords': [
            'nl': (string)                              // assembleCommon
            'en': (string)                              // assembleCommon
        ],
        'coin': {
            'publish_in_edugain': (bool)                // assembleCommon // assemblePublishInEdugainDate
            'disable_scoping': (bool)                   // assembleCommon
            'additional_logging': (bool)                // assembleCommon
        }
        'certData': (string|opt.)                       // assembleCertificates
        'certData2': (string|opt.)                      // assembleCertificates
        'certData3': (string|opt.)                      // assembleCertificates
        'contacts: [
            {
                'contactType': (string)                 // assembleContactPersons
                'emailAddress': (string)                // assembleContactPersons
                'givenName': (string)                   // assembleContactPersons
                'surName': (string)                     // assembleContactPersons
            }
        ],
        'NameIDFormat': (string)                        // assembleCommon
        'NameIDFormats': [                              // assembleCommon
            (string)                                    // assembleCommon
        ],
        'SingleLogoutService: [
            {
                'Location': (string)                    // assembleSingleLogoutServices
                'Binding': (string)                     // assembleSingleLogoutServices
            }
        ],
        'redirect': {
            'sign': ???                                 // assembleCommon
        },
    },
    'manipulation_code': (string)                       // assembleCommon
}
```

SP:
```
{
    'metadata' : {
        'coin': {
            'transparent_issuer': (bool)                // assembleSp
            'trusted_proxy': (bool)                     // assembleSp
            'implicit_vo_id': (bool)                    // assembleSp
            'display_unconnected_idps_wayf': (bool)     // assembleSp
            'eula': (string)                            // assembleSp
            'do_no_add_attribute_aliases': (bool)       // assembleSp
            'policy_enforcement_decision_required': (bool) // assembleSp
        },
        'AssertionConsumerService': [
            {
                'Location': (string)                    // assembleAssertionConsumerService
                'Binding': (string)                     // assembleAssertionConsumerService
                'Index': (int)                          // assembleAssertionConsumerService
            }
        ]
    },
    'arp_attributes': [                                 // assembleAttributeReleasePolicy
        'attribute_name': [                             // assembleAttributeReleasePolicy
            'allowed_value1',                           // assembleAttributeReleasePolicy
            'allowed_value2'                            // assembleAttributeReleasePolicy
        ],
    ]
}
```

IdP:
```
{
    'metadata': {
        'SingleSignOnService': [
            {
                'Location': (string),                   // assembleSingleSignOnServices
                'Binding': (string)                     // assembleSingleSignOnServices
            }
        ],
        'coin': {
            'guest_qualifier': (string)                 // assembleIdp
            'schacHomeOrganization': (string)           // assembleIdp
            'hidden': (bool)                            // assembleIdp
        },
        'shibmd': {                                     // assembleShibMdScope
            'scope': [                                  // assembleShibMdScope
                'allowed': (string)                     // assembleShibMdScope
                'regexp': (bool)                        // assembleShibMdScope
            ]
        }
    },
    'disable_consent_connections': [
        {
            'name': (string)                            // assembleSpEntityIdsWithoutConsent
        }
    ]
}
```
