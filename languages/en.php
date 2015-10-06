<?php

return array(
    'english'               => 'English',

    // General
    'back'                  => 'back',
    'attribute'             => 'Attribute',
    'value'                 => 'Value',
    'post_data'             => 'POST Data',
    'processing'            => 'Connecting to the service',
    'processing_waiting'    => 'Waiting for a response from the service.',
    'processing_long'       => 'Taking too long?',
    'note'                  => 'Note',
    'note_no_script'        => 'Since your browser does not support JavaScript, you must press the button below to proceed.',
    'go_back'               => '&lt;&lt; Go back',
    'authentication_urls'   => 'Authentication URLs',
    'timestamp'             => 'Timestamp',

    // Feedback
    'requestId'             => 'Unique Request ID',
    'identityProvider'      => 'Identity Provider',
    'serviceProvider'       => 'Service Provider',
    'userAgent'             => 'User Agent',
    'ipAddress'             => 'IP Address',
    'statusCode'            => 'Status Code',
    'statusMessage'         => 'Status Message',

    // WAYF
    'idp_selection_title'       => 'Identity Provider Selection - %s',
    'idp_selection_subheader'   => 'Login via your institution',
    'search'                    => 'Search for an institution...',
    'idp_selection_desc'        => 'Select an institution to login to <i>%s</i>',
    'our_suggestion'            => 'Previously chosen:',
    'idps_with_access'          => 'Identity Providers with access',
    'no_access'                 => 'No access',
    'no_access_more_info'       => 'No access. &raquo;',
    'no_results'                => 'Your search did not return any results.',
    'error_header'              => 'Error',
    'log_in_to'                 => 'Select an institution to login to the service:',
    'press_enter_to_select'     => 'Press enter to select',
    'loading_idps'              => 'Loading Identity Providers...',
    'edit'                      => 'Edit List',
    'done'                      => 'Done',
    'remove'                    => 'Remove',
    'request_access'            => 'Request access',
    'no_idp_results'            => 'Your search did not return any results.',
    'no_idp_results_request_access' => 'Can\'t find your institution? &nbsp;<a href="#no-access" class="noaccess">Request access</a>&nbsp;or try tweaking your search.',
    'return_to_sp'              => 'Return to Service Provider',

    // Footer
    'service_by'            => 'This is a service connected through',
    'serviceprovider_link'  => '<a href="https://www.surfconext.nl/" target="_blank">SURFconext</a>',
    'terms_of_service_link' => '<a href="https://wiki.surfnet.nl/display/conextsupport/Terms+of+Service+%28EN%29" target="_blank">Terms of Service</a>',
    'footer'                => '<a href="https://www.surfconext.nl/" target="_blank">SURFconext</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="https://wiki.surfnet.nl/display/conextsupport/Terms+of+Service+%28EN%29">Terms of Service</a>',

    // Help
    'help'                  => 'Help',
    'help_header'           => 'Help',
    'help_description'      => '<p>Check the FAQ below if you have any questions about this screen or about SURFconext.</p>

    <p>For more detailed information, please visit <a href="https://support.surfconext.nl/">the SURFconext support page</a>
        or contact the SURFconext helpdesk at <a href="mailto:help@surfconext.nl">help@surfconext.nl</a></p>',

    'close_question'        =>      'Close',

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

    // consent help questions
    'question_consentscreen'            =>      'Why this screen?',
    'answer_consentscreen'              =>      '<p>To be able to use this service it is necessary to share some of your personal information with this service.</p>',
    'question_consentinfo'              =>      'What happens with my information?',
    'answer_consentinfo'                =>      '<p>When you agree to share your information with the service the information shown will be provided to the service. The service provider will use and possibly store this information in order to ensure a proper functioning service. On this screen there is a link to the "Terms of Service" of the service and of SURFconext which will give you more information on how the personal data is handled.</p>',
    'question_consentno'                =>      'What happens when i don\'t want to share my information?',
    'answer_consentno'                  =>      '<p>When you don\'t agree to share your information with the service you cannot use the service. In this case, the information shown will not be shared with the service.</p>',
    'question_consentagain'             =>      'I\'ve previously shared my information with the service, but why do i get the same question again?',
    'answer_consentagain'               =>      '<p>When your information previously provided to the service has changed, you will be asked again if you allow the sharing of your information.</p>',

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
    'sorry'                 => 'Unfortunately,',
    'form_description'      => 'does not have access to this service. What can you do?</h2>
            <p>If you want to access this service, please fill out the form below. We will then forward your request to the person responsible for the services portfolio management at your institution.</p>',
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

    //Profile
    'profile_header'                    => 'SURFconext',
    'profile_subheader'                 => 'Overview of your SURFconext profile.',
    'profile_header_my_profile'         => 'My Profile',
    'profile_header_my_apps'            => 'My Services',
    'profile_header_my_groups'          => 'My Groups',
    'profile_header_surfteams'          => 'SURFconext Teams',
    'profile_header_exit'               => 'Exit',
    'profile_header_auth_needed'        => 'Authentication required',
    'profile_header_leave_surfconext'   => 'Leave SURFconext',
    'profile_store_info'                => 'Your institution provided the following profile information. This profile will be stored in (and used by) SURFconext. A subset of this information is provided to services that you access through SURFconext.',
    'profile_group_membership_desc'     => 'You are a member of the following groups.',
    'profile_no_groups'                 => 'No groups',
    'profile_extra_groups_desc'         => 'In order to get extra groups you must authorize their use.',
    'profile_leave_surfconext_desc'     => 'You currently use SURFconext to access one or more services with your institutional account. It is possible to delete your SURFconext profile by pressing the button below.',
    'profile_leave_surfconext_link'     => 'Delete my SURFconext account!',
    'profile_leave_surfconext_disclaim' => 'Note:
                                            <ul>
                                                <li>Only information stored in the SURFconext service will be deleted.</li>
                                                <li>Applications accessed through SURFconext will not be notified. It is possible your personal data is still stored in these applications.</li>
                                                <li>After a new SURFconext login a new profile will be created automatically.</li>
                                             </ul>
                                             <br>More information about what information is stored by SURFconext can be found on <a href="https://wiki.surfnet.nl/display/conextsupport/Profile+page" target="_blank">the SURFconext support pages</a>',
    'profile_leave_surfconext_link_add' => '(Close your browser after this action to finalize the removal procedure)',
    'profile_revoke_access'             => 'Revoke access',
    'profile_leave_surfconext_conf'     => 'Are you sure you want to delete your profile? You must restart your browser to finalize this action',
    'profile_eula_link'                 => 'Terms of use',
    'profile_support_link'              => 'Support pages',
    'profile_mail_text'                 => 'SURFconext support may ask you to share the abovementioned data. This information can help them to answer your support question.',
    'profile_mail_attributes'           => 'Mail data to help@surfconext.nl',
    'profile_mail_send_success'         => 'The mail with your information has been successfully sent.',
    'profile_helplink'                  => 'https://wiki.surfnet.nl/display/conextsupport/Profile+page',

    //Profile MyApps
    'profile_apps_connected_aps'        => 'My services accessed through SURFconext',
    'profile_apps_share'                => 'You have given permission to share your information with the following services:',
    'profile_apps_service_th'           => 'Service/Application',
    'profile_apps_eula_th'              => 'EULA',
    'profile_apps_support_name_th'      => 'Support person name',
    'profile_apps_support_url_th'       => 'Support URL',
    'profile_apps_support_email_th'     => 'Support email',
    'profile_apps_support_phone_th'     => 'Support person phone',
    'profile_apps_consent_th'           => 'Consent group information',
    'profile_revoke_consent'            => 'Revoke',
    'profile_no_consent'                => 'Not granted yet',
    'profile_consent'                   => 'Granted consent',
    'profile_attribute_release'         => 'The following attributes are released to this Service Provider:',
    'profile_attribute_release_all'     => 'This service receives all attributes provided by your institution.',

    //Delete User
    'deleteuser_success_header'         => 'SURFconext exit procedure',
    'deleteuser_success_subheader'      => 'You are almost done...',
    'deleteuser_success_desc'           => '<strong>Important!</strong> To finalize the exit procedure you must close your browser.',

    //Consent
    'external_link'                     => 'opens in a new window',
    'consent_header'                    => '%s requests your information',
    'consent_subheader'                 => '%s requests your information',
    'consent_intro'                     => '%s requests this information that %s has stored for you:',
    'consent_idp_provides'              => 'wants to provide the following information:',
    'consent_sp_is_provided'            => 'to',
    'consent_terms_of_service'          => 'This information will be passed on to %s. Terms of service of %s and %s apply.',

    'consent_accept'                    => 'Yes, share this data',
    'consent_decline'                   => 'No, I don\'t want to use this service',
    'consent_notice'                    => '(We will ask you again when the information changes)',

    //New Consent
    'consent_header_info'               => 'Request for release of your information',
    'consent_sp_idp_info'               => 'In order to log in to <strong class="service-provider">%1$s</strong> using your institutional account, <strong class="identity-provider">%2$s</strong> uses SURFconext. This service is only accessible through SURFconext if <strong class="identity-provider">%2$s</strong> shares certain information with this service. For this, your permission is required. The service needs the following information:',
    'sp_terms_of_service'               => 'View %s\'s <a href="%s" target="_blank">Terms of Service</a>',
    'name_id'                           => 'SURFconext user ID',

    //Error screens
    'error_404'                         => '404 - Page not found',
    'error_404_desc'                    => 'This page has not been found.',
    'error_help_desc'               => 'If this does not solve your problem, please visit
        <a href="https://wiki.surfnet.nl/display/conextsupport/">the SURFconext support page</a>
        or contact the SURFconext helpdesk at <a href="mailto:help@surfconext.nl">help@surfconext.nl</a>.',
    'error_no_consent'              => 'Unable to continue to service',
    'error_no_consent_desc'         => 'This application can only be used when you share the mentioned information.<br /><br />

If you want to use this application you have to:<br />
<ul><li>restart your browser</li>
<li>login again</li>
<li>share your information</li></ul>',
    'error_no_idps'                 => 'Error - No Identity Providers found',
    'error_no_idps_desc'            => '<p>
        The application you came from (your &lsquo;Service Provider&rsquo;) is not allowed to connect with any Identity Provider.
        Please <a href="javascript:history.back();">go back</a> and contact the administrator(s) of
        this service.
        <br /><br />
    </p>',
    'error_session_lost'            => 'Error - your session was lost',
    'error_session_lost_desc'       => '<p>
        Somewhere along the way, your session with us was lost. <br />
        Most likely your browser privacy or security settings prevented the cookie to be set? <br />
        Please go back and try again.
        <br /><br />
    </p>',
    'error_no_message'              => 'Error - No message received',
    'error_no_message_desc'         => 'We were expecting a message, but did not get one? Something went wrong. Please try again.',
    'error_invalid_acs_location'    => 'The given "Assertion Consumer Service" is unknown or invalid.',
    'error_invalid_acs_binding'     => 'Invalid ACS Binding Type',
    'error_invalid_acs_binding_desc'     => 'The provided or configured "Assertion Consumer Service" Binding Type is unknown or invalid.',
    'error_unknown_service_provider'          => 'Error - Cannot provide metadata for EntityID \'%s\'',
    'error_unknown_service_provider_desc'     => '<p>
        A Service Provider with the EntityID you have provided could not be found. If you feel this is an error please contact the SURFconext helpdesk at <a href="mailto:help@surfconext.nl">help@surfconext.nl</a>.
    </p>',

    'error_unknown_issuer'          => 'Error - Unknown service',
    'error_unknown_issuer_desc'     => '<p>
        The service you are trying to log in to is unknown to SURFconext. Possibly your institution has never enabled access to this service. Please contact the helpdesk of your institution and provide them with the following information:
    </p>',
    'error_vo_membership_required'      => 'Membership of a Virtual Organisation required',
    'error_vo_membership_required_desc' => 'You have successfully authenticated at your Identity Provider, however in order to use this service you have to be a member of a Virtual Organisation.',
    'error_generic'                     => 'Error - An error occurred',
    'error_generic_desc'                => '<p>
        It is not possible to sign in. Please try again.
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
    'error_group_oauth'            =>  'Error - Group authorization failed',
    'error_group_oauth_desc'       => '<p>
        The external group provider <b>%s</b> reported an error. </p>
        <p>Please contact the SURFconext team at <a href="mailto:help@surfconext.nl">help@surfconext.nl</a>.
       </p>',
    'error_received_error_status_code'     => 'Error - Identity Provider error',
    'error_received_error_status_code_desc'=> '<p>
        Your Identity Provider sent an authentication response with an error status code.
    </p>',
    'error_received_invalid_response'       => 'Error - Invalid Identity Provider response',
    'error_received_invalid_signed_response'=> 'Error - Invalid signature on Identity Provider response',
    'error_received_status_code_desc'=> '<p>
        Your Identity Provider sent an authentication response that was invalid.
    </p>',

    /**
     * %1 AttributeName
     * %2 Options
     * %3 (optional) Value
     * @url http://nl3.php.net/sprintf
     */
    'error_attribute_validator_type_uri'            => '\'%3$s\' is not a valid URI',
    'error_attribute_validator_type_urn'            => '\'%3$s\' is not a valid URN',
    'error_attribute_validator_type_url'            => '\'%3$s\' is not a valid URL',
    'error_attribute_validator_type_hostname'       => '\'%3$s\' is not a valid hostname',
    'error_attribute_validator_type_emailaddress'   => '\'%3$s\' is not a valid email address',
    'error_attribute_validator_minlength'           => '\'%3$s\' is not long enough (minimum is %2$d characters)',
    'error_attribute_validator_maxlength'           => '\'%3$s\' is too long (maximum is %2$d characters)',
    'error_attribute_validator_min'                 => '%1$s requires at least %2$d values (%3$d given)',
    'error_attribute_validator_max'                 => '%1$s may have no more than %2$d values (%3$d given)',
    'error_attribute_validator_regex'               => '\'%3$s\' does not match the expected format of this attribute (%2$s)',
    'error_attribute_validator_not_in_definitions'  => '%1$s is not known in the SURFconext schema',
    'error_attribute_validator_allowed'             => '\'%3$s\' is not an allowed value for this attribute',
    'error_attribute_validator_availability'        => '\'%3$s\' is a reserved schacHomeOrganization for another Identity Provider',

    'attributes_validation_succeeded' => 'Authentication success',
    'attributes_validation_failed'    => 'Some attributes failed validation',
    'attributes_data_mailed'          => 'Attribute data has been mailed',
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

    // Internal
    'info_mail_link' => '<a href="support@surfconext.nl">support@surfconext.nl</a>',
);
