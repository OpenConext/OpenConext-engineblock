<?php

return array(
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
    'userAgent'             => 'User Agent',
    'ipAddress'             => 'IP Address',
    'statusCode'            => 'Status Code',
    'statusMessage'         => 'Status Message',

    // WAYF
    'search'                    => 'Search for an institution...',
    'idps_with_access'          => 'Identity Providers with access',
    'idps_without_access'       => 'Identity Providers without access',
    'log_in_to'                 => 'Select an institution to login to the service:',
    'press_enter_to_select'     => 'Press enter to select',
    'loading_idps'              => 'Loading Identity Providers...',
    'request_access'            => 'Request access',
    'no_idp_results'            => 'Your search did not return any results.',
    'no_idp_results_request_access' => 'Can\'t find your institution? &nbsp;<a href="#no-access" class="noaccess">Request access</a>&nbsp;or try tweaking your search.',
    'more_idp_results'          => '%arg1% results not shown. Refine your search to show more specific results.',
    'return_to_sp'              => 'Return to Service Provider',

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
    'serviceprovider_link'  => '<a href="https://www.surfconext.nl/" target="_blank">SURFconext</a>',
    'terms_of_service_link' => '<a href="https://wiki.surfnet.nl/display/conextsupport/Terms+of+Service+%28EN%29" target="_blank">Terms of Service</a>',

    // Help
    'help'                  => 'Help',
    'help_header'           => 'Help',
    'help_description'      => '<p>Check the FAQ below if you have any questions about this screen or about SURFconext.</p>

    <p>For more detailed information, please visit <a href="https://support.surfconext.nl/">the SURFconext support page</a>
        or contact the SURFconext helpdesk at <a href="mailto:help@surfconext.nl">help@surfconext.nl</a></p>',

    // Help questions
    // general help questions
    'question_surfconext'               =>      'What is SURFconext?',
    'answer_surfconext'                 =>      '<p>SURFconext is a next generation collaboration infrastructure that creates new opportunities to collaborate online based on a combination of federation technology, group management, concepts from social networking and applications from different providers. SURFconext allows you to access online services with the username and password issued to you by your own institution.</p>',
    'question_log_in'                   =>      'How do you login via SURFconext?',
    'answer_log_in'                     =>      '<ul>
                            <li>Click on your institution in this screen.</li>
                            <li>You will then be redirected to the log-in page of your institution where you can log in.</li>
                            <li>Your institution will notify SURFconext that you have logged in successfully.</li>
                            <li>You will be taken to the service for which you have logged in. You can then start using that service.</li>
                        </ul>',
    'question_security'                 =>      'Is SURFconext secure?',
    'answer_security'                   =>      '<p>Your institution and SURFnet believe that user privacy is extremely important.<br />
<br />
Personal details are only provided to a service provider if these details are needed to use the service. Contractual agreements between your institution, SURFconext and the service provider guarantee that your personal details will be handled and processed with care.<br />
<br />
If you have any questions about your privacy and the policy applied, please visit <a href="https://wiki.surfnet.nl/display/conextsupport/">the SURFconext support page</a> for more information or contact the SURFconext helpdesk at <a href="mailto:help@surfconext.nl">help@surfconext.nl</a>.
</p>',

    // WAYF help questions
    'question_screen'                   =>      'Why this screen?',
    'answer_screen'                     =>      '<p>You can log in to this service with your institutional account. In this screen, you select the institution you are affiliated with.</p>',
    'question_institution_not_listed'   =>      'My institution is not listed. What should I do?',
    'answer_institution_not_listed'     =>      '<p>If your institution is not listed, then it is either not linked to SURFconext or your institution may not allow access to this particular service. Go back to the page for the service. In some cases, there will be alternative ways of logging in.</p>',
    'question_institution_no_access'    =>      'My institution does not allow access to the service. What should I do?',
    'answer_institution_no_access'      =>      '<p>It is possible that your institution is connected to SURFconext but has not (or not yet) made any arrangements with the service provider about the use of the service. We will forward your request to your institution. Based on your request your institution may consider to add this service to its service portfolio.</p>',
    'question_asked_institution_access'  =>      'I have asked my institution for access but I still cannot get access. Why not?',
    'answer_asked_institution_access'    =>      '<p>Apparently, your institution has not arranged a license yet or, access to this service is not on the roadmap of your institution. Such decisions are beyond the scope and control of SURFnet.</p>',
    'question_cannot_select'            =>      'In my browser I cannot select my institution. What should I do?',
    'answer_cannot_select'              =>      '<p>The dialog box to select your institution can be used in most popular browsers, including Internet Explorer, Firefox, Chrome, and Safari. Other browsers may not be supported. Your browser must support the use of cookies and JavaScript.</p>',

    //Form
    'request_access_instructions' => '<h2>Unfortunately, you do not have access to the service you are looking for.
                                   What can you do?</h2>
                                <p>If you want to access this service, please fill out the form below.
                                   We will then forward your request to the person responsible for the services
                                   portfolio management at your institution.</p>',
    'name'                  => 'Name',
    'name_error'            => 'Enter your name',
    'email'                 => 'E-mail',
    'email_error'           => 'Enter your (correct) e-mail address',
    'institution'           => 'Institution',
    'institution_error'     => 'Enter an institution',
    'comment'               => 'Comment',
    'comment_error'         => 'Enter a comment',
    'cancel'                => 'Cancel',
    'send'                  => 'Send',
    'close'                 => 'Close',

    'send_confirm'          => 'Your request has been sent',
    'send_confirm_desc'     => '<p>Your request has been forwarded to your institution. Further settlement and decisions on the availability of this service will be taken by the ICT staff of your institution.</p>

    <p>SURFnet has forwarded your request, but the decision and planning to make this service available depends on the ICT policy of your institution.</p>

    <p>If you have any questions about your request, please contact <a href="mailto:help@surfconext.nl">help@surfconext.nl</a></p>',

    // Consent theme EB 5.5.0 and later
    'consent_header_title'                    => '%arg1% needs your information before logging in',
    'consent_header_text'                     => 'The service needs the following information to function properly. These data will be sent securely from your institution towards %arg1% via <a class="help" href="#" data-slidein="about">SURFconext</a>.',
    'consent_privacy_title'                   => 'The following information will be shared with %arg1%:',
    'consent_privacy_link'                    => 'Read the privacy policy of this service',
    'consent_attributes_correction_link'      => 'Are your details incorrect?',
    'consent_attributes_show_more'            => 'Show more information',
    'consent_attributes_show_less'            => 'Show less information',
    'consent_attribute_source_voot'           => 'Group membership',
    'consent_attribute_source_sab'            => 'SURFnet Autorisatie Beheer',
    'consent_attribute_source_orcid'          => 'ORCID iD',
    'consent_attribute_source_surfmarket_entitlements' => 'SURFmarket entitlements',
    'consent_attribute_source_logo_url_voot'  => 'https://static.surfconext.nl/media/aa/voot.png',
    'consent_attribute_source_logo_url_sab'   => 'https://static.surfconext.nl/media/aa/sab.png',
    'consent_attribute_source_logo_url_orcid' => 'https://static.surfconext.nl/media/aa/orcid.png',
    'consent_attribute_source_logo_url_surfmarket_entitlements' => 'https://static.surfconext.nl/media/aa/surfmarket_entitlements.png',
    'consent_buttons_title'                   => 'Do you agree with sharing this data?',
    'consent_buttons_ok'                      => 'Yes, proceed to %arg1%',
    'consent_buttons_nok'                     => 'No, I do not agree',
    'consent_footer_text_singular'            => 'You are using one other service via SURFconext. <a href="%arg1%" target="_blank">View the list of services and your profile information.</a>',
    'consent_footer_text_plural'              => 'You are using %arg1% services via SURFconext. <a href="%arg2%" target="_blank">View the list of services and your profile information.</a>',
    'consent_footer_text_first_consent'       => 'You are not using any services via SURFconext. <a href="%arg1%" target="_blank">View your profile information.</a>',
    'consent_slidein_details_email'           => 'Email',
    'consent_slidein_details_phone'           => 'Phone',
    'consent_slidein_text_contact'            => 'If you have any questions about this page, please contact the help desk of your institution. SURFconext has the following contact information:',
    'consent_slidein_text_no_support'         => 'No contact data available.',

    // Consent slidein: Is the data shown incorrect?
    'consent_slidein_correction_title' => 'Is the data shown incorrect?',
    'consent_slidein_correction_text_idp'  => 'SURFconext receives the information directly from your institution and does not store the information itself. If your information is incorrect, please contact the help desk of your institution to change it.',
    'consent_slidein_correction_text_aa'  => 'SURFconext receives the information directly from the attribute provider and does not store the information itself. If your information is incorrect, please contact the attribute provider directly to correct it. You can ask the help desk of your institution for assistance with this.',

    // Consent slidein: About SURFconext
    'consent_slidein_about_text'  => <<<'TXT'
<h1>Logging in through SURFconext</h1>
<img src="/images/about-surfconext.png" alt="SURFconext diagram"/>
<p>Via SURFconext, researchers, staff and students can easily and securely log in into various cloud services using their own institutional account. SURFconext offers extra privacy protection by sending a minimum set of personal data to these cloud services.</p>
<p>Curious about which services already received your information before through SURFconext? Visit your <a href="%arg1%">SURFconext profile page</a>.</p>

<h1>SURFconext is part of SURF</h1>
<p>SURF is the collaborative ICT organisation for Dutch education and research.</p>
<p>SURF provides access to the best possible internet and IT facilities to students, teachers and researchers in the Netherlands. Want to know more about SURF? Have a look at the <a href="https://www.surf.nl/" target="_blank">website from SURF</a>.</p>
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

    //Error screens
    'error_404'                         => '404 - Page not found',
    'error_404_desc'                    => 'This page has not been found.',
    'error_help_desc'               => '<p>
        Please visit <a href="https://support.surfconext.nl/" target="_blank">the SURFconext support pages</a> for help solving this problem. These pages also contain contact information for the support team if the problem persists.
    </p>',
    'error_no_consent'              => 'Unable to continue to service',
    'error_no_consent_desc'         => 'This application can only be used when you share the mentioned information.<br /><br />

If you want to use this application you have to:<br />
<ul><li>restart your browser</li>
<li>login again</li>
<li>share your information</li></ul>',
    'error_no_idps'                 => 'Error - No Identity Providers found',
    'error_no_idps_desc'            => '<p>
The service you\'re trying to reach (&lsquo;Service Provider&rsquo;) is not accessible through SURFconext.<br /><br />

Visit <a href="https://wiki.surfnet.nl/x/m69WAw" target="_blank">the SURFconext support pages</a> for more support, if you think you should have access to this service.
        <br /><br />
    </p>',
    'error_session_lost'            => 'Error - your session was lost',
    'error_session_lost_desc'       => '<p>
We somehow lost where you wanted to go. Did you wait too long? If so, try again first. Does your browser accept cookies? Are you using an outdated URL or bookmark?<br /><br />
Visit <a href="https://wiki.surfnet.nl/x/jq9WAw" target="_blank">the SURFconext supportpages</a> for more extensive support on this error.
        <br /><br />
    </p>',

    'error_authorization_policy_violation'            => 'Error - No access',
    'error_authorization_policy_violation_desc'       => '<p>
        You have successfully logged in at your institution, but unfortunately you cannot use this service (the &lsquo;Service Provider&rsquo;) because you have no access. Your institution limits access to this service with an <i>authorization policy</i>. Please contact your institution\'s helpdesk if you think you should be allowed access to this service.
    </p>',
    'error_authorization_policy_violation_info'       => 'Message from your institution: ',
    'error_no_message'              => 'Error - No message received',
    'error_no_message_desc'         => 'We were expecting a message, but did not get one? Something went wrong. Please try again.',
    'error_invalid_acs_location'    => 'The given "Assertion Consumer Service" is unknown or invalid.',
    'error_invalid_acs_binding'     => 'Invalid ACS Binding Type',
    'error_invalid_acs_binding_desc'     => 'The provided or configured "Assertion Consumer Service" Binding Type is unknown or invalid.',
    'error_unsupported_signature_method' => 'Signature method is not supported',
    'error_unsupported_signature_method_desc' => 'The signature method %arg1% is not supported, please upgrade to RSA-SHA256 (http://www.w3.org/2001/04/xmldsig-more#rsa-sha256).',
    'error_unknown_preselected_idp' => 'Error - No connection between institution and service',
    'error_unknown_preselected_idp_desc' => '<p>
        The institution that you want to use to login to this service did not activate access to this service. This means you are unable to use this service through SURFconext. Please contact your institution\'s helpdesk to request access to this service. State what service it is about (the &lsquo;Service Provider&rsquo;) and why you need access.
    </p>',
    'error_unknown_service_provider'          => 'Error - Cannot provide metadata for EntityID \'%arg1%\'',
    'error_unknown_service_provider_desc'     => '<p>
Your requested service couldn\'t be found. Visit <a href="https://wiki.surfnet.nl/x/k69WAw" target="_blank">the SURFconext supportpages</a> for more extensive support on this error.
    </p>',

    'error_unknown_issuer'          => 'Error - Unknown service',
    'error_unknown_issuer_desc'     => '<p>
        The service you are trying to log in to is unknown to SURFconext. Possibly your institution has never enabled access to this service. Please contact the helpdesk of your institution and provide them with the following information:
    </p>',
    'error_generic'                     => 'Error - An error occurred',
    'error_generic_desc'                => '<p>
Your log-in has failed and we don\'t know exactly why. Try again first, and if that doesn\'t work visit <a href="https://wiki.surfnet.nl/x/iq9WAw" target="_blank">the SURFconext supportpages</a> for more support with this error. On that page you can also find how to contact us if the error persists.
    </p>',
    'error_missing_required_fields'     => 'Error - Missing required fields',
    'error_missing_required_fields_desc'=> '<p>
        You can not use this application because your institution is not providing the needed information.
    </p>
    <p>
        Please contact your institution with the information stated below.
    </p>
    <p>
        Login failed because the institution\'s identity provider did not provide SURFconext with one or more of the following required attribute(s):
        <ul>
            <li>UID</li>
            <li>schacHomeOrganization</li>
        </ul>
    </p>',
    'error_received_error_status_code'     => 'Error - Identity Provider error',
    'error_received_error_status_code_desc'=> '<p>
Your institution has denied you access to this service. You will have to contact your own (IT-)servicedesk to see if this can be fixed.
    </p>',
    'error_received_invalid_response'       => 'Error - Invalid Identity Provider response',
    'error_received_invalid_signed_response'=> 'Error - Invalid signature on Identity Provider response',
    'error_stuck_in_authentication_loop' => 'Error - You got stuck in a black hole',
    'error_stuck_in_authentication_loop_desc' => '<p>
        You\'ve successfully authenticated at your Identity Provider but the service you are trying to access sends you back again to SURFconext. Because you are already logged in, SURFconext then forwards you back to the service, which results in an infinite black hole. Likely, this is caused by an error at the Service Provider. Visit <a href="https://support.surfconext.nl" target="_blank">the SURFconext support pages</a> for more extensive support on this error.
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
    'error_attribute_validator_not_in_definitions'  => '%arg1% is not known in the SURFconext schema',
    'error_attribute_validator_allowed'             => '\'%arg3%\' is not an allowed value for this attribute',
    'error_attribute_validator_availability'        => '\'%arg3%\' is a reserved schacHomeOrganization for another Identity Provider',

    'error_unknown_service'         => 'Error - Unknown service',
    'error_unknown_service_desc'    => '<p>Your requested service couldn\'t be found.Please contact the SURFconext helpdesk at <a href="mailto:help@surfconext.nl">help@surfconext.nl</a></p>',

    'attributes_validation_succeeded' => 'Authentication success',
    'attributes_validation_failed'    => 'Some attributes failed validation',
    'attributes_data_mailed'          => 'Attribute data have been mailed',
    'idp_debugging_title'             => 'Show response from Identity Provider',
    'retry'                           => 'Retry',

    'attributes' => 'Attributes',
    'validation' => 'Validation',
    'idp_debugging_mail_explain' => 'When requested by SURFconext,
                                        use the "Mail to SURFconext" button below
                                        to mail the information in this screen.',
    'idp_debugging_mail_button' => 'Mail to SURFconext',

    // Logout
    'logout' => 'logout',
    'logout_description' => 'This application uses centralized log in, which provides single sign on for several applications. To be sure your log out is 100% secure you should close your browser completely.',
    'logout_information_link' => '<a href="https://wiki.surfnet.nl/display/conextsupport/Log+out+SURFconext">More information about secure log out</a>',
);
