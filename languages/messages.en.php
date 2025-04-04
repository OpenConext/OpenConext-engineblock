<?php

$overrides = [];
$overridesFile = __DIR__ . '/overrides.en.php';
if (file_exists($overridesFile)) {
    $overrides = require $overridesFile;
}

return $overrides + [
    // Values used in placeholders for other translations
    // %suiteName%: OpenConext (default), SURFconext, ACMEconext
    'suite_name' => 'OpenConext',

    // Example translation message:
    //     'Find an %organisationNoun%'
    //
    // Becomes:
    //     'Find an organisation' (default)
    // or: 'Find an institution' (when overridden)
    'organisation_noun' => 'organisation',
    'organisation_noun_plural' => 'organisations',

    // Example translation message:
    //     'Use your %accountNoun%'
    //
    // Becomes:
    //     'Use your organisation account' (default)
    // or: 'Use your institutional account' (when overridden)
    'account_noun' => 'organisation account',

    // Email
    // %supportUrl% can be used as a placeholder for other translations
    'openconext_support_url' => 'https://example.org',
    'openconext_terms_of_use_url' => 'https://example.org',
    'name_id_support_url' => 'https://example.org',

    // General
    'value'                 => 'Value',
    'post_data'             => 'POST Data',
    'processing'            => 'Connecting to the service',
    'processing_waiting'    => 'Waiting for a response from the service.',
    'processing_long'       => 'Please be patient, it may take a while...',
    'go_back'               => '&lt;&lt; Go back',
    'note'                  => 'Note',
    'note_no_script'        => 'Since your browser does not support JavaScript, you must press the button below to proceed.',
    'unknown_organization_name' => 'Unknown',
    'site_notice'           => '<p>There is nothing wrong with your television set. <strong>Do not attempt to adjust the picture.</strong> We are controlling transmission. If we wish to make it louder, we will bring up the volume. If we wish to make it softer, we will tune it to a whisper. We will control the horizontal. We will control the vertical. We can roll the image, make it flutter. We can change the focus to a soft blur, or sharpen it to crystal clarity. <strong></p><p>For the next hour, sit quietly and we will control all that you see and hear.</strong> We repeat: There is nothing wrong with your television set. You are about to participate in a great adventure. You are about to experience the awe and mystery which reaches from the inner mind to... <a href="https://en.wikiquote.org/wiki/The_Outer_Limits_(1963_TV_series)">The Outer Limits</a>.</p>',

        // Feedback
    'requestId'             => 'UR ID',
    'identityProvider'      => 'IdP',
    'serviceProvider'       => 'SP',
    'serviceProviderName'   => 'SP Name',
    'proxyServiceProvider'  => 'Proxy SP',
    'ipAddress'             => 'IP',
    'statusCode'            => 'Status Code',
    'artCode'               => 'EC',
    'statusMessage'         => 'Status Message',
    'attributeName'         => 'Attribute Name',
    'attributeValue'        => 'Attribute Value',

    // WAYF
    'our_suggestion'            => 'Previously chosen:',
    'edit'                      => 'edit',
    'done'                      => 'done',
    'idps_with_access'          => '%organisationNounPlural% with access:',
    'idps_without_access'       => '%organisationNounPlural% without access:',
    'loading_idps'              => 'Loading %organisationNounPlural%...',
    'request_access'            => 'Request access',
    'no_idp_results'            => 'Your search did not return any results.',
    'no_idp_results_request_access' => 'Can\'t find your %organisationNoun%? &nbsp;<a href="#no-access" class="noaccess">Request access</a>&nbsp;or try tweaking your search.',
    'more_idp_results'          => '%arg1% results not shown. Refine your search to show more specific results.',
    'return_to_sp'              => 'Return to Service Provider',

    // Help page
    'help'                  => 'Help',
    'help_header'       => 'Help',
    'help_page_content' => <<<HTML
<p>No help content available.</p>
HTML
    ,

    // Remove cookies
    'remember_choice'           => 'Remember my choice',
    'cookie_removal_header'     => 'Remove cookies',
    'cookie_remove_button'      => 'Remove',
    'cookie_remove_all_button'  => 'Remove all',
    'cookie_removal_description' => 'Below you will find an overview of your cookies and the possibility to remove them individually or all at once.',
    'cookie_removal_confirm'     => 'Your cookie has been removed.',
    'cookies_removal_confirm'    => 'Your cookies have been removed.',

    // Footer
    'service_by'            => 'This is a service connected through',
    'serviceprovider_link_text'  => '%suiteName%',
    'serviceprovider_link_target'  => 'https://openconext.org/',
    'terms_of_service_link_text' => 'Terms of Service',
    'terms_of_service_link_target' => '#',

    // Form
    'request_access_instructions_head' => 'Unfortunately, you do not have access to the service you are looking for. What can you do?',
    'request_access_instructions_text' => 'If you want to access this service, please fill out the form below.
                               We will then forward your request to the person responsible for the services
                               portfolio management at your %organisationNoun%.',
    'name'                  => 'Name',
    'name_error'            => 'Enter your name',
    'email'                 => 'E-mail',
    'email_error'           => 'Enter your (correct) e-mail address',
    '%organisationNoun%'    => '%organisationNoun%',
    'institution_error'     => 'Enter an %organisationNoun%',
    'comment'               => 'Comment',
    'comment_error'         => 'Enter a comment',
    'cancel'                => 'Cancel',
    'send'                  => 'Send',
    'close'                 => 'Close',
    'required'              => 'Required',
    'send_confirm'          => 'Your request has been sent',
    'send_confirm_desc'     => 'Your request has been forwarded to your %organisationNoun%. Further settlement and decisions on the availability of this service will be taken by the ICT staff of your %organisationNoun%.',

    // Consent
    'consent_attributes_screenreader'         => 'about %orgName%',
    'consent_attributes_show_more'            => 'Show more information',
    'consent_attributes_show_less'            => 'Show less information',
    'consent_no_attributes_text'              => 'This service requires no information from your %organisationNoun%.',
    'consent_buttons_ok_informational'        => 'Proceed to %sp%',
    'consent_buttons_nok'                     => 'No, I do not agree',
    'consent_buttons_nok_informational'       => 'Cancel',
    'consent_explanation_title'               => 'Pay attention when using this service',
    'consent_name_id_label'                   => 'Identifier',
    'consent_name_id_support_link'            => 'Explanation',
    'consent_name_id_value_tooltip'           => 'The identifier for this service is generated by %arg1% en differs amongst each service you use through %arg1%. The service can therefore recognise you as the same user when you return, but services cannot recognise you amongst each other as the same user.',
    'consent_slidein_details_email'           => 'Email',
    'consent_slidein_details_phone'           => 'Phone',
    'consent_slidein_text_contact'            => 'If you have any questions about this page, please contact the service desk of your %organisationNoun%. %suiteName% has the following contact information:',
    'consent_slidein_text_no_support'         => 'No contact data available.',

    // Consent slidein: Is the data shown incorrect?
    'consent_slidein_correction_title' => 'Is the data shown incorrect?',
    'consent_slidein_correction_text_idp'  => '%suiteName% receives the information directly from your %organisationNoun% and does not store the information itself. If your information is incorrect, please contact the service desk of your %organisationNoun% to change it.',
    'consent_slidein_correction_text_aa'  => '%suiteName% receives the information directly from the attribute provider and does not store the information itself. If your information is incorrect, please contact the attribute provider directly to correct it. You can ask the service desk of your %organisationNoun% for assistance with this.',

    // Generic slide-in
    'slidein_read_more' => 'Read more',

    // Error screens
    'error_feedback_info_intro' => 'Does this error message recur? Use the resources below to get help. When contacting the service desk, please include the following codes:',
    'error_wiki-href' => 'https://nl.wikipedia.org/wiki/SURFnet',
    'error_wiki-link-text' => '%suiteName% Wiki',
    'error_wiki-link-text-short' => 'Wiki',
    'error_help-desk-href' => 'https://www.surf.nl/over-surf/dienstverlening-support-werkmaatschappijen',
    'error_help-desk-link-text' => 'Service desk',
    'error_help-desk-link-text-short' => 'Service desk',
    'error_return-sp-link-text' => 'Return to %spName%',
    'error_return-sp-link-text-short' => 'Return to service',

    'error_404'                         => '404 - Page not found',
    'error_404_desc'                    => 'This page has not been found.',
    'error_405'                         => 'HTTP Method not allowed',
    'error_405_desc'                    => 'The HTTP method "%requestMethod%" is not allowed for location "%uri%". Supported methods are: %allowedMethods%.',
    'error_help_desc'               => '',
    'error_no_idps'                 => 'Error - No %organisationNounPlural% found',
    'error_no_idps_desc'            => 'Logging into %spName% is not possible via %suiteName%. %spName% is not connected to any %organisationNounPlural%.',
    'error_no_idps_desc_no_sp_name'            => 'Logging into this service is not possible via %suiteName%. The service is not connected to any %organisationNounPlural%.',
    'error_session_lost'            => 'Error - your session was lost',
    'error_session_lost_desc'       => 'To continue to the service an active session is required. However, your session expired. Perhaps you waited too long with logging in? Please go back to the service and try again. If that doesn\'t work, close your browser first and then try again.',
    'error_session_not_started'            => 'Error - No session found',
    'error_session_not_started_desc'       => 'To continue to the service an active session is required. However, no session was found. Your browser must accept cookies. Alternatively, the link you used to get to the service might be wrong. Please go back to the service and try again. If that doesn\'t work, try a different browser.',
    'error_authorization_policy_violation'            => 'Error - Access denied',
    'error_authorization_policy_violation_desc'       => 'You cannot use %spName% because %idpName% limits access to it (the "Service Provider") with an authorization policy. Please contact the service desk of %idpName% if you think you should be allowed access to %spName%.',
    'error_authorization_policy_violation_desc_no_idp_name'       => 'You cannot use %spName% because your %organisationNoun% limits access to it (the "Service Provider") with an authorization policy. Please contact the service desk of your %organisationNoun% if you think you should be allowed access to %spName%.',
    'error_authorization_policy_violation_desc_no_sp_name'       => 'You cannot use this service because %idpName% limits access to it (the "Service Provider") with an authorization policy. Please contact the service desk of %idpName% if you think you should be allowed access to this service.',
    'error_authorization_policy_violation_desc_no_name' => 'You cannot use this service because your %organisationNoun% limits access to this service (the "Service Provider") with an authorization policy. Please contact the helpdesk of your %organisationNoun% if you think you should be allowed access to this service.',
    'error_authorization_policy_violation_info'       => 'Message from %idpName%: ',
    'error_authorization_policy_violation_info_no_idp_name'       => 'Message from your %organisationNoun%: ',
    'error_no_message'              => 'Error - No message received',
    'error_no_message_desc'         => 'We were expecting a SAML message, but did not get one. Something went wrong. Please try again.',
    'error_invalid_acs_location'    => 'The given "Assertion Consumer Service" is unknown or invalid.',
    'error_invalid_acs_binding'     => 'Error - Invalid ACS binding type',
    'error_invalid_acs_binding_desc'     => 'The provided or configured "Assertion Consumer Service" Binding Type is unknown or invalid.',
    'error_unsupported_signature_method' => 'Error - Signature method is not supported',
    'error_unsupported_signature_method_desc' => 'The signature method %arg1% is not supported, please upgrade to RSA-SHA256 (http://www.w3.org/2001/04/xmldsig-more#rsa-sha256).',
    'error_unknown_keyid' => 'Error - unknown key id',
    'error_unknown_keyid_desc' => 'The requested key-ID is not known to %suiteName%. Perhaps the service provider is using outdated metadata or has a configuration error.',
    'error_unknown_preselected_idp' => 'Error - %spName% not accessible through your %organisationNoun%',
    'error_unknown_preselected_idp_no_sp_name' => 'Error - Service not accessible through your %organisationNoun%',
    'error_unknown_preselected_idp_desc' => 'The %organisationNoun% that you want to use to login to %spName% did not activate access to it. This means you are unable to use %spName% through %suiteName%. Please contact the service desk of your %organisationNoun% to request access. State it is about %spName% and why you need access.',
    'error_unknown_preselected_idp_desc_no_sp_name' => 'The %organisationNoun% that you want to use to login to this service did not activate access to this service. This means you are unable to use this service through %suiteName%. Please contact the helpdesk of your %organisationNoun% to request access to this service. State what service it is about (the "SP") and why you need access.',
    'error_unknown_service_provider'            => 'Error - %spName% unknown',
    'error_unknown_service_provider_no_sp_name' => 'Error - Unknown service',
    'error_unknown_service_provider_desc'     => 'You are trying to log in to %spName%, but this is unknown to %suiteName%. Possibly %idpName% has never enabled access to %spName%. If you would like to use it, please contact the service desk of %idpName%.',
    'error_unknown_service_provider_desc_no_sp_name' => 'The service you are trying to log in to is unknown to %suiteName%. Possibly %idpName% has never enabled access to this service. If you would like to use this service, please contact the helpdesk of %idpName%.',
    'error_unknown_service_provider_desc_no_idp_name' => 'You are trying to log in to %spName%. Possibly your %organisationNoun% has never enabled access to %spName%. If you would like to use it, please contact the helpdesk of your %organisationNoun%.',
    'error_unknown_service_provider_desc_no_names' => 'The service you are trying to log in to is unknown to %suiteName%. Possibly your %organisationNoun% has never enabled access to this service. If you would like to use this service, please contact the helpdesk of your %organisationNoun%.',

    'error_unsupported_acs_location_scheme' => 'Error - Unsupported URI scheme in ACS location',

    'error_unknown_identity_provider'               => 'Error - %idpName% unknown',
    'error_unknown_identity_provider_no_idp_name'   => 'Error - Unknown %organisationNoun%',
    'error_unknown_identity_provider_desc'     => '%idpName%, which you are trying to log in with, is unknown to %suiteName%.',
    'error_unknown_identity_provider_desc_no_idp_name'     => 'The %organisationNoun% you are trying to log in with is unknown to %suiteName%.',
    'error_unknown_signing_key' => 'Error - unknown signing key',
    'error_unknown_signing_key_desc' => 'The signing key used is not known to %suiteName%. This is possibly a configuration error.',
    'error_generic'                     => 'Error - An error occurred',
    'error_generic_desc'                => 'Logging in has failed and we don\'t know exactly why. Please try again first by going back to %spName% and logging in again. If this doesn\'t work, please contact the service desk of %idpName%.',
    'error_generic_desc_no_sp_name' => 'Logging in has failed and we don\'t know exactly why. Please try again first by going back to the service and logging in again. If this doesn\'t work, please contact the service desk of %idpName%.',
    'error_generic_desc_no_idp_name' => 'Logging in has failed and we don\'t know exactly why. Please try again first by going back to %spName% and logging in again. If this doesn\'t work, please contact the service desk of your %organisationNoun%.',
    'error_generic_desc_no_names' => 'Logging in has failed and we don\'t know exactly why. Please try again first by going back to the service and logging in again. If this doesn\'t work, please contact the service desk of your %organisationNoun%.',
    'error_missing_required_fields'     => 'Error - Missing required fields',
    'error_missing_required_fields_desc'=> '%idpName% does not provide the mandatory information or it has an invalid format. Therefore, you can not use %spName%. Please contact the service desk of %idpName% and tell them one or more of the the following required attributes are not being set correctly for %suiteName%:',
    'error_missing_required_fields_desc_no_idp_name'=> 'Your %organisationNoun% does not provide the mandatory information. Therefore, you can not use %spName%. Please contact your %organisationNoun% and tell them one or more of the the following required attribute(s) are missing within %suiteName%:',
    'error_missing_required_fields_desc_no_sp_name'=> '%idpName% does not provide the mandatory information. Therefore, you can not use this service. Please contact the service desk of %idpName% and tell them one or more of the the following required attribute(s) are missing within %suiteName%:',
    'error_missing_required_fields_desc_no_name'=> '
Your %organisationNoun% does not provide the mandatory information. Therefore, you can not use this service. Please contact your %organisationNoun% and tell them one or more of the the following required attribute(s) are missing within %suiteName%:',
    'error_invalid_attribute_value' => 'Error - Attribute value not allowed',
    'error_invalid_attribute_value_desc' => '%idpName% sends a value for attribute %attributeName% ("%attributeValue%") which is not allowed for this %organisationNoun%. Therefore you cannot log in. Only %idpName% can resolve this. Please contact the service desk of %idpName% to fix this problem.',
    'error_invalid_attribute_value_desc_no_idp_name' => 'Your %organisationNoun% sends a value for attribute %attributeName% ("%attributeValue%") which is not allowed for this %organisationNoun%. Therefore you cannot log in. Only your %organisationNoun% can resolve this. Please contact the service desk of your own %organisationNoun% to fix this problem.',
    'error_received_error_status_code'     => 'Error - Identity Provider error',
    'error_received_error_status_code_desc'=> 'Your %organisationNoun% has denied you access to this service. You will have to contact your own (IT-)service desk to see if this can be fixed.',
    'error_received_invalid_response'       => 'Error - Invalid %idpName% SAML response',
    'error_received_invalid_response_no_idp_name'       => 'Error - Invalid %organisationNoun% SAML response',
    'error_received_invalid_signed_response'=> 'Error - Invalid signature on %idpName% response',
    'error_received_invalid_signed_response_no_idp_name'=> 'Error - Invalid signature on %organisationNoun% response',
    'error_stuck_in_authentication_loop' => 'Error - You got stuck in a black hole',
    'error_stuck_in_authentication_loop_desc' => 'You\'ve successfully authenticated at %idpName% but %spName% sends you back again to %suiteName%. Because you are already logged in, %suiteName% then sends you back to %spName%, which results in an infinite black hole. Likely, this is caused by an error at %spName%.',
    'error_stuck_in_authentication_loop_desc_no_idp_name' => 'You\'ve successfully authenticated at your %organisationNoun% but %spName% sends you back again to %suiteName%. Because you are already logged in, %suiteName% then sends you back to %spName%, which results in an infinite black hole. Likely, this is caused by an error at %spName%.',
    'error_stuck_in_authentication_loop_desc_no_sp_name' => 'You\'ve successfully authenticated at %idpName% but the service you are trying to access sends you back again to %suiteName%. Because you are already logged in, %suiteName% then sends you back to the service, which results in an infinite black hole. Likely, this is caused by an error at the Service Provider.',
    'error_stuck_in_authentication_loop_desc_no_name' => 'You\'ve successfully authenticated at your %organisationNoun% but the service you are trying to access sends you back again to %suiteName%. Because you are already logged in, %suiteName% then sends you back to the service, which results in an infinite black hole. Likely, this is caused by an error at the Service Provider.',
    'error_authentication_limit_exceeded' => 'Error - too many authentications in progress',
    'error_authentication_limit_exceeded_desc' => 'Too many authentications in progress',
    'error_no_authentication_request_received' => 'Error - No authentication request received.',
    'error_authn_context_class_ref_blacklisted'                     => 'Error - AuthnContextClassRef value is not allowed',
    'error_authn_context_class_ref_blacklisted_desc'                => 'You cannot login because %idpName% sent a value for AuthnContextClassRef that is not allowed. Please contact the service desk of %idpName% to solve this.',
    'error_authn_context_class_ref_blacklisted_desc_no_idp_name'                => 'You cannot login because your %organisationNoun% sent a value for AuthnContextClassRef that is not allowed. Please contact the service desk of your %organisationNoun% to solve this.',
    'error_invalid_mfa_authn_context_class_ref' => 'Error - Multi factor authentication failed',
    'error_invalid_mfa_authn_context_class_ref_desc' => '%idpName% requires multi-factor authentication for this service. However, your second factor could not be validated. Please contact the service desk of %idpName% to solve this.',
    'error_invalid_mfa_authn_context_class_ref_desc_no_idp_name' => 'Your %organisationNoun% requires multi-factor authentication for this service. However, your second factor could not be validated. Please contact the service desk of your %organisationNoun% to solve this.',
    /**
     * %1 AttributeName
     * %2 Options
     * %3 (optional) Value
     * @url http://nl3.php.net/sprintf
     */
    'error_attribute_validator_type_uri'            => '\'%arg3%\' is not a valid URI',
    'error_attribute_validator_type_urn'            => '\'%arg3%\' is not a valid URN',
    'error_attribute_validator_type_url'            => '\'%arg3%\' is not a valid URL',
    'error_attribute_validator_type_hostname'       => '\'%arg3%\' is not a valid hostname',
    'error_attribute_validator_type_emailaddress'   => '\'%arg3%\' is not a valid email address',
    'error_attribute_validator_minlength'           => '\'%arg3%\' is not long enough (minimum is %arg2% characters)',
    'error_attribute_validator_maxlength'           => '\'%arg3%\' is too long (maximum is %arg2% characters)',
    'error_attribute_validator_min'                 => '%arg1% should have at least %arg2% values (%arg3% given)',
    'error_attribute_validator_max'                 => '%arg1% may have no more than %arg2% values (%arg3% given)',
    'error_attribute_validator_regex'               => '\'%arg3%\' does not match the expected format of this attribute (%arg2%)',
    'error_attribute_validator_not_in_definitions'  => '%arg1% is not known in the schema',
    'error_attribute_validator_allowed'             => '\'%arg3%\' is not an allowed value for this attribute',
    'error_attribute_validator_availability'        => '\'%arg3%\' is a reserved schacHomeOrganization for another Identity Provider',
    'allowed_scopes'                                => 'Allowed scopes',

    'error_unknown_requesterid_in_authnrequest'         => 'Error - Unknown service',
    'error_unknown_requesterid_in_authnrequest_desc'    => 'Your requested service couldn\'t be found.',
    'error_clock_issue_title' => 'Error - The Assertion is not yet valid or has expired',
    'error_clock_issue_desc' => 'This is likely because the difference in time between %idpName% and %suiteName% it too large. Please verify that the time on the %organisationNoun% is correct.',
    'error_clock_issue_desc_no_idp_name' => 'This is likely because the difference in time between %organisationNoun% and %suiteName% it too large. Please verify that the time on the IdP is correct.',
    'error_stepup_callout_unknown_title' => 'Error - Unknown strong authentication failure',
    'error_stepup_callout_unknown_desc' => 'Logging in with strong authentication has failed and we don\'t know exactly why . Please try again first by going back to the service and logging in again . If this doesn\'t work, please contact the service desk of your %organisationNoun%.',
    'error_stepup_callout_unmet_loa_title' => 'Error - No suitable token found',
    'error_stepup_callout_unmet_loa_desc' => 'To continue to this service, a registered token with a certain level of assurance is required. Currently, you either haven\'t registered a token at all, or the level of assurance of the token you did register is too low. See the link below for more information about the registration process.',
    'error_stepup_callout_unmet_loa_link_text' => 'Read more about the registration process.',
    'error_stepup_callout_unmet_loa_link_target' => 'https://support.surfconext.nl/stepup-noauthncontext-en',
    'error_stepup_callout_user_cancelled_title' => 'Error - Logging in cancelled',
    'error_stepup_callout_user_cancelled_desc' => 'You have aborted the login process. Go back to the service if you want to try again.',
    'error_metadata_entity_id_not_found' => 'Metadata can not be generated',
    'error_metadata_entity_id_not_found_desc' => 'The following error occurred: %message%',
    'attributes_validation_succeeded' => 'Authentication success',
    'attributes_validation_failed'    => 'Some attributes failed validation',
    'attributes_data_mailed'          => 'Attribute data have been mailed',
    'idp_debugging_title'             => 'Show response from Identity Provider',
    'retry'                           => 'Retry',

    'attributes' => 'Attributes',
    'validation' => 'Validation',
    'idp_debugging_mail_explain' => 'When requested by %suiteName%,
                                    use the "Mail to %suiteName%" button below
                                    to mail the information in this screen.',
    'idp_debugging_mail_button' => 'Mail to %suiteName%',

    // Logout
    'logout' => 'logout',
    'logout_description' => 'This application uses centralized log in, which provides single sign on for several applications. To be sure your log out is 100% secure you should close your browser completely.',
    'logout_information_link_text' => '',
    'logout_information_link_target' => '',

    // Error page wiki link in footer, keep empty to hide block in footer
    'error_feedback_wiki_links_feedback_unknown_error' => 'https://support.surfconext.nl/help-error-error-en',
    'error_feedback_wiki_links_authentication_feedback_unable_to_receive_message' => '',
    'error_feedback_wiki_links_authentication_feedback_session_lost' => 'https://support.surfconext.nl/help-session-lost-en',
    'error_feedback_wiki_links_authentication_feedback_session_not_started' => '',
    'error_feedback_wiki_links_authentication_feedback_unknown_identity_provider' => '',
    'error_feedback_wiki_links_authentication_feedback_no_idps' => '',
    'error_feedback_wiki_links_authentication_feedback_invalid_acs_location' => '',
    'error_feedback_wiki_links_authentication_feedback_unsupported_signature_method' => '',
    'error_feedback_wiki_links_authentication_feedback_unsupported_acs_location_uri_scheme' => '',
    'error_feedback_wiki_links_authentication_feedback_unknown_service_provider' => 'https://support.surfconext.nl/help-unknown-sp-en',
    'error_feedback_wiki_links_authentication_feedback_missing_required_fields' => 'https://support.surfconext.nl/help-missing-fields-en',
    'error_feedback_wiki_links_authentication_authn_context_class_ref_blacklisted' => '',
    'error_feedback_wiki_links_authentication_feedback_invalid_attribute_value' => 'https://support.surfconext.nl/help-scope-en',
    'error_feedback_wiki_links_authentication_feedback_custom' => '',
    'error_feedback_wiki_links_authentication_feedback_invalid_acs_binding' => 'https://support.surfconext.nl/help-bindings',
    'error_feedback_wiki_links_authentication_feedback_received_error_status_code' => '',
    'error_feedback_wiki_links_authentication_feedback_signature_verification_failed' => 'https://support.surfconext.nl/help-bindings',
    'error_feedback_wiki_links_authentication_feedback_verification_failed' => 'https://support.surfconext.nl/help-prepare-idp',
    'error_feedback_wiki_links_authentication_feedback_unknown_requesterid_in_authnrequest' => '',
    'error_feedback_wiki_links_authentication_feedback_pep_violation' => 'https://support.surfconext.nl/help-pep-en',
    'error_feedback_wiki_links_authentication_feedback_unknown_preselected_idp' => 'https://support.surfconext.nl/help-no-connection-en',
    'error_feedback_wiki_links_authentication_feedback_stuck_in_authentication_loop' => 'https://support.surfconext.nl/help-loop-en',
    'error_feedback_wiki_links_authentication_feedback_no_authentication_request_received' => '',
    'error_feedback_wiki_links_authentication_feedback_response_clock_issue' => 'https://support.surfconext.nl/help-ntp-en',
    'error_feedback_wiki_links_authentication_feedback_stepup_callout_user_cancelled' => '',
    'error_feedback_wiki_links_authentication_feedback_stepup_callout_unmet_loa' => '',
    'error_feedback_wiki_links_authentication_feedback_stepup_callout_unknown' => '',
    'error_feedback_wiki_links_authentication_feedback_metadata_entity_not_found' => '',

    // Error page idp contact link in footer, keep empty to hide block in footer
    'error_feedback_idp_contact_label_small_feedback_unknown_error' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_unable_to_receive_message' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_feedback_session_lost' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_session_not_started' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_feedback_unknown_identity_provider' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_no_idps' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_feedback_invalid_acs_location' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_unsupported_signature_method' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_feedback_unsupported_acs_location_uri_scheme' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_unknown_service_provider' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_feedback_missing_required_fields' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_authn_context_class_ref_blacklisted' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_invalid_attribute_value' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_feedback_custom' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_invalid_acs_binding' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_received_error_status_code' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_signature_verification_failed' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_feedback_verification_failed' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_feedback_unknown_requesterid_in_authnrequest' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_pep_violation' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_feedback_unknown_preselected_idp' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_feedback_stuck_in_authentication_loop' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_feedback_no_authentication_request_received' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_response_clock_issue' => 'Mail',
    'error_feedback_idp_contact_label_small_authentication_feedback_stepup_callout_user_cancelled' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_stepup_callout_unmet_loa' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_stepup_callout_unknown' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_metadata_entity_not_found' => '',

    // Metadata
    'metadata_organization_name' => '%suiteName%',
    'metadata_organization_displayname' => '%suiteName%',
    'metadata_organization_url' => '%supportUrl%',
];
