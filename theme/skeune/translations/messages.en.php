<?php

$overrides = [];
$overridesFile = __DIR__ . '/overrides.en.php';
if (file_exists($overridesFile)) {
    $overrides = require $overridesFile;
}

return $overrides + [
    // General
    'button_screenreader'        =>  ', button',
    'button_expandable_screenreader' => ', button, expandable',
    'button_expanded_screenreader' => ', button, expanded',
    'required_screenreader'      =>  'Required',
    'search_screenreader'        =>  'Search',
    'send_request'  =>  'Send request',
    'back'          =>  'Back',
    'noscript'      =>  'For this page to function optimally you need JavaScript turned on.',
    'language_switcher'     => 'Language switcher',

    // FOOTER
    'log_in_to'     => 'Select an account to login to %arg1%',
    'helpLink'       => 'https://support.surfconext.nl/wayf-en',
    'footer_navigation_screenreader'    => 'Footer navigation',

    // Forms
    'form_general_error'    =>  'Something went wrong when submitting the form.  This might be a faulty internet connection, or some other problem.  Please check your input and try again in a little while.  If the problem persists please contact your service desk.',
    'form_error_name'       =>  'Your name needs to be at least 2 characters long',
    'form_error_email'      =>  'This is an invalid email address',

    // REDIRECT
    'processing_waiting'    =>  'Waiting for a response',

    // WAYF
    'wayf_nothing_found'        => 'Nothing found',
    'wayf_apu'                  => 'Please try again with some different keywords',
    'wayf_noscript_warning_intro'     => 'Without JavaScript you will not be able to remember previously used accounts, nor be able to search.  If you want to use that functionality, please enable JavaScript.',
    'wayf_noscript_warning_end'     => 'You can, off course, still log in.',
    'wayf_delete_account_screenreader'       => 'Delete %idpTitle% from your accounts',
    'wayf_deleted_account_screenreader'      => ' was deleted from your accounts',
    'wayf_remaining_idps_title_screenreader' => 'Log in with an account from the list below',
    'wayf_select_account_screenreader'       => 'Select an account from the list below',
    'wayf_search_placeholder'   => 'Search...',
    'wayf_search_screenreader'          => 'Search for an %organisationNoun%',
    'wayf_search_reset_screenreader'    => 'Clear the search field',
    'wayf_search_results_screenreader' => '%orgNoun% found',
    'wayf_your_accounts'        => 'Your accounts',
    'wayf_add_account'          => 'Use another account',
    'wayf_no_access'            => 'Sorry, no access for this account',
    'wayf_no_access_account_screenreader'    => 'No access with this account',
    'wayf_no_access_helpdesk'   => 'If you want, you can request access to this application. We will send the request to the helpdesk of your %orgNoun%.',
    'wayf_no_access_helpdesk_not_connected'     =>  "Go back to the previous page and click '%buttonText%'.",
    'wayf_noaccess_title_screenreader'       => 'Request access for this account',
    'wayf_noaccess_name'        => 'Your name',
    'wayf_noaccess_email'       => 'Your email address',
    'wayf_noaccess_motivation'  => 'Motivation',
    'wayf_noaccess_success'     => 'Your request for access has been sent.',
    'wayf_noaccess_request_access_screenreader'  => 'Open the request access form',
    'wayf_noaccess_form_announcement_screenreader' => 'Some required fields are not filled in, or not correctly filled in.',
    'wayf_defaultIdp_start'     => 'If your %organisation_noun% is not listed,',
    'wayf_defaultIdp_linkText'  => '%defaultIdpName% is available as an alternative.',
    'wayf_idp_title_screenreader' => 'Log in with ',
    'wayf_idp_title_noaccess_screenreader'  => 'No access with',

    // Consent
    'consent_h1_text'   => 'Do you consent to sharing your information?',
    'consent_h1_text_informational'   => 'Review your information that will be shared.',
    'consent_privacy_header'    => '%target% will receive',
    'consent_attributes_correction_text'    => 'Something incorrect?',
    'consent_ok'    =>  'Yes, I agree',
    'consent_identifier_explanation'    => 'The identifier for this application is generated by %suite_name% en differs amongst each application you use through %suite_name%. The application can therefore recognise you as the same user when you return, but applications cannot recognise you amongst each other as the same user.',
    'consent_provided_by'   => 'provided by',
    'consent_tooltip_screenreader'  => 'Why do we need your %attr_name%?',
    'consent_nojs' => 'Some features on this page require JavaScript to work with the keyboard. If you wish to use a keyboard, please enable JavaScript in your browser.',
    'consent_disclaimer_privacy_statement' => '(offered by %org%) needs this information to function properly',
    'consent_disclaimer_privacy_read'    => 'read their',
    'consent_disclaimer_privacy_policy'  => 'privacy policy',
    'consent_disclaimer_secure' => 'is being used by your %orgNoun% to securely send your information to %spName% (read more about',
    'consent_reject_text_skeune_header'    => "You don't want to share your data with the application",
    'consent_reject_text_skeune_body'    => "The application you're logging into requires your data to function properly. If you prefer not to share your data, you cannot use this application. By closing your browser or just this tab you prevent your information from being shared with the application. If you change your mind later, please log in to the application again and this screen will reappear.",
    'consent_nok_title'     => "You don't want to share your data with the application",
    'consent_nok_text'      => "The application you're logging into requires your data to function properly. If you prefer not to share your data, you cannot use this application. By closing your browser or just this tab you prevent your information from being shared with the application. If you change your mind later, please log in to the application again and this screen will reappear.",
    'consent_groupmembership_show_more'     => 'Show more',
    'consent_groupmembership_show_less'     => 'Show less',
    'consent_warning_allowed_html'      => '<a><u><i><br><wbr><strong><em><blink><marquee>',
];
