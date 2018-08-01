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

    // General
    'value'                 => 'Value',
    'post_data'             => 'POST Data',
    'processing'            => 'Connecting to the service',
    'processing_waiting'    => 'Waiting for a response from the service.',
    'processing_long'       => 'Please be patient, it may take a while...',
    'go_back'               => '&lt;&lt; Go back',
    'note'                  => 'Note',
    'note_no_script'        => 'Since your browser does not support JavaScript, you must press the button below to proceed.',

    // Feedback
    'timestamp'             => 'Timestamp',
    'requestId'             => 'Unique Request ID',
    'identityProvider'      => 'Identity Provider',
    'serviceProvider'       => 'Service Provider',
    'serviceProviderName'   => 'Service Provider Name',
    'userAgent'             => 'User Agent',
    'ipAddress'             => 'IP Address',
    'statusCode'            => 'Status Code',
    'statusMessage'         => 'Status Message',

    // WAYF
    'search'                    => 'Search for an %organisationNoun%...',
    'our_suggestion'            => 'Previously chosen:',
    'idps_with_access'          => 'Identity Providers with access',
    'idps_without_access'       => 'Identity Providers without access',
    'log_in_to'                 => 'Select an %organisationNoun% to login to the service:',
    'press_enter_to_select'     => 'Press enter to select',
    'loading_idps'              => 'Loading Identity Providers...',
    'request_access'            => 'Request access',
    'no_idp_results'            => 'Your search did not return any results.',
    'no_idp_results_request_access' => 'Can\'t find your %organisationNoun%? &nbsp;<a href="#no-access" class="noaccess">Request access</a>&nbsp;or try tweaking your search.',
    'more_idp_results'          => '%arg1% results not shown. Refine your search to show more specific results.',
    'return_to_sp'              => 'Return to Service Provider',

    // Help page
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
    'cookie_removal_description' => '<p>Below you will find an overview of your cookies and the possibility to remove them individually or all at once.</p>',
    'cookie_removal_confirm'     => 'Your cookie has been removed.',
    'cookies_removal_confirm'    => 'Your cookies have been removed.',

    // Footer
    'service_by'            => 'This is a service connected through',
    'serviceprovider_link'  => '<a href="https://openconext.org/" target="_blank">%suiteName%</a>',
    'terms_of_service_link' => '<a href="#" target="_blank">Terms of Service</a>',

    // Form
    'request_access_instructions' => '<h2>Unfortunately, you do not have access to the service you are looking for.
                                   What can you do?</h2>
                                <p>If you want to access this service, please fill out the form below.
                                   We will then forward your request to the person responsible for the services
                                   portfolio management at your %organisationNoun%.</p>',
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

    'send_confirm'          => 'Your request has been sent',
    'send_confirm_desc'     => '<p>Your request has been forwarded to your %organisationNoun%. Further settlement and decisions on the availability of this service will be taken by the ICT staff of your %organisationNoun%.</p>',

    // Consent page
    'consent_header_title'                    => '%arg1% needs your information before logging in',
    'consent_header_text'                     => 'The service needs the following information to function properly. These data will be sent securely from your %organisationNoun% towards %arg1% via <a class="help" href="#" data-slidein="about">%suiteName%</a>.',
    'consent_privacy_title'                   => 'The following information will be shared with %arg1%:',
    'consent_privacy_link'                    => 'Read the privacy policy of this service',
    'consent_attributes_correction_link'      => 'Are your details incorrect?',
    'consent_attributes_show_more'            => 'Show more information',
    'consent_attributes_show_less'            => 'Show less information',
    'consent_buttons_title'                   => 'Do you agree with sharing this data?',
    'consent_buttons_ok'                      => 'Yes, proceed to %arg1%',
    'consent_buttons_ok_minimal'              => 'Proceed to %arg1%',
    'consent_buttons_nok'                     => 'No, I do not agree',
    'consent_buttons_nok_minimal'             => 'Cancel',
    'consent_explanation_title'               => 'Pay attention when using this service',
    'consent_footer_text_singular'            => 'You are using one other service via %suiteName%. <a href="%arg1%" target="_blank">View the list of services and your profile information.</a>',
    'consent_footer_text_plural'              => 'You are using %arg1% services via %suiteName%. <a href="%arg2%" target="_blank">View the list of services and your profile information.</a>',
    'consent_footer_text_first_consent'       => 'You are not using any services via %suiteName%. <a href="%arg1%" target="_blank">View your profile information.</a>',
    'consent_name_id_label'                   => 'Identifier',
    'consent_name_id_support_link'            => 'Explanation',
    'consent_name_id_value_tooltip'           => 'The identifier for this service is generated by %arg1% en differs amongst each service you use through %arg1%. The service can therefore recognise you as the same user when you return, but services cannot recognise you amongst each other as the same user.',
    'consent_slidein_details_email'           => 'Email',
    'consent_slidein_details_phone'           => 'Phone',
    'consent_slidein_text_contact'            => 'If you have any questions about this page, please contact the help desk of your %organisationNoun%. %suiteName% has the following contact information:',
    'consent_slidein_text_no_support'         => 'No contact data available.',

    // Consent slidein: Is the data shown incorrect?
    'consent_slidein_correction_title' => 'Is the data shown incorrect?',
    'consent_slidein_correction_text_idp'  => '%suiteName% receives the information directly from your %organisationNoun% and does not store the information itself. If your information is incorrect, please contact the help desk of your %organisationNoun% to change it.',
    'consent_slidein_correction_text_aa'  => '%suiteName% receives the information directly from the attribute provider and does not store the information itself. If your information is incorrect, please contact the attribute provider directly to correct it. You can ask the help desk of your %organisationNoun% for assistance with this.',

    // Consent slidein: About %suiteName%
    'consent_slidein_about_text'  => <<<'TXT'
<h1>Logging in through %suiteName%</h1>
<p>%suiteName% allows people to easily and securely log in into various cloud services using their own %accountNoun%. %suiteName% offers extra privacy protection by sending a minimum set of personal data to these cloud services.</p>
TXT
    ,

    // Consent slidein: Reject
    'consent_slidein_reject_text'  => <<<'TXT'
<h1>You declined to share your data</h1>
<p>The service you're logging into requires your data to function. If you do not agree with sharing your data, you cannot use this service. By closing your browser or this tab you fully decline to share the necessary information. If you change your mind after this, please return to the service and you will be asked for permission once again.</p>
TXT
    ,

    // Generic slide-in
    'slidein_close' => 'Close',
    'slidein_read_more' => 'Read more',

    // Error screens
    'error_404'                         => '404 - Page not found',
    'error_404_desc'                    => 'This page has not been found.',
    'error_help_desc'               => '<p></p>',
    'error_no_consent'              => 'Unable to continue to service',
    'error_no_consent_desc'         => 'This application can only be used when you share the mentioned information.<br /><br />

If you want to use this application you have to:<br />
<ul><li>restart your browser</li>
<li>login again</li>
<li>share your information</li></ul>',
    'error_no_idps'                 => 'Error - No Identity Providers found',
    'error_no_idps_desc'            => '<p>
The service you\'re trying to reach (&lsquo;Service Provider&rsquo;) is not accessible through %suiteName%.<br /><br />
    </p>',
    'error_session_lost'            => 'Error - your session was lost',
    'error_session_lost_desc'       => '<p>
We somehow lost where you wanted to go. Did you wait too long? If so, try again first. Does your browser accept cookies? Are you using an outdated URL or bookmark?<br /><br />
        <br /><br />
    </p>',

    'error_authorization_policy_violation'            => 'Error - No access',
    'error_authorization_policy_violation_desc'       => '<p>
        You have successfully logged in at your %organisationNoun%, but unfortunately you cannot use this service (the &lsquo;Service Provider&rsquo;) because you have no access. Your %organisationNoun% limits access to this service with an <i>authorization policy</i>. Please contact the helpdesk of your %organisationNoun% if you think you should be allowed access to this service.
    </p>',
    'error_authorization_policy_violation_info'       => 'Message from your %organisationNoun%: ',
    'error_no_message'              => 'Error - No message received',
    'error_no_message_desc'         => 'We were expecting a message, but did not get one? Something went wrong. Please try again.',
    'error_invalid_acs_location'    => 'The given "Assertion Consumer Service" is unknown or invalid.',
    'error_invalid_acs_binding'     => 'Invalid ACS Binding Type',
    'error_invalid_acs_binding_desc'     => 'The provided or configured "Assertion Consumer Service" Binding Type is unknown or invalid.',
    'error_unsupported_signature_method' => 'Signature method is not supported',
    'error_unsupported_signature_method_desc' => 'The signature method %arg1% is not supported, please upgrade to RSA-SHA256 (http://www.w3.org/2001/04/xmldsig-more#rsa-sha256).',
    'error_unknown_preselected_idp' => 'Error - No connection between %organisationNoun% and service',
    'error_unknown_preselected_idp_desc' => '<p>
        The %organisationNoun% that you want to use to login to this service did not activate access to this service. This means you are unable to use this service through %suiteName%. Please contact the helpdesk of your %organisationNoun% to request access to this service. State what service it is about (the &lsquo;Service Provider&rsquo;) and why you need access.
    </p>',
    'error_unknown_service_provider'          => 'Error - Cannot provide metadata for EntityID \'%arg1%\'',
    'error_unknown_service_provider_desc'     => '<p>Your requested service couldn\'t be found.</p>',

    'error_unknown_issuer'          => 'Error - Unknown service',
    'error_unknown_issuer_desc'     => '<p>
        The service you are trying to log in to is unknown to %suiteName%. Possibly your %organisationNoun% has never enabled access to this service. Please contact the helpdesk of your %organisationNoun% and provide them with the following information:
    </p>',
    'error_generic'                     => 'Error - An error occurred',
    'error_generic_desc'                => '<p>
Your log-in has failed and we don\'t know exactly why. Try again first, and if that doesn\'t work contact your %organisationNoun% for help.
    </p>',
    'error_missing_required_fields'     => 'Error - Missing required fields',
    'error_missing_required_fields_desc'=> '<p>
        You can not use this application because your %organisationNoun% is not providing the needed information.
    </p>
    <p>
        Please contact your %organisationNoun% with the information stated below.
    </p>
    <p>
        Login failed because the identity provider of your %organisationNoun% did not provide %suiteName% with one or more of the following required attribute(s):
        <ul>
            <li>UID</li>
            <li>schacHomeOrganization</li>
        </ul>
    </p>',
    'error_received_error_status_code'     => 'Error - Identity Provider error',
    'error_received_error_status_code_desc'=> '<p>
Your %organisationNoun% has denied you access to this service. You will have to contact your own (IT-)servicedesk to see if this can be fixed.
    </p>',
    'error_received_invalid_response'       => 'Error - Invalid Identity Provider response',
    'error_received_invalid_signed_response'=> 'Error - Invalid signature on Identity Provider response',
    'error_stuck_in_authentication_loop' => 'Error - You got stuck in a black hole',
    'error_stuck_in_authentication_loop_desc' => '<p>
        You\'ve successfully authenticated at your Identity Provider but the service you are trying to access sends you back again to %suiteName%. Because you are already logged in, %suiteName% then forwards you back to the service, which results in an infinite black hole. Likely, this is caused by an error at the Service Provider.
    </p>',

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

    'error_unknown_service'         => 'Error - Unknown service',
    'error_unknown_service_desc'    => '<p>Your requested service couldn\'t be found.</p>',

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
    'logout_information_link' => '',
];
