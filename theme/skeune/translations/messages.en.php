<?php
return [
    // General
    'button'        =>  'Button: ',
    'logo'          =>  'logo',
    'required'      =>  'Required',
    'search'        =>  'Search',
    'send_request'  =>  'Send request',
    'noscript'      =>  'For this page to function optimally you need JavaScript turned on.',

    // Forms
    'form_general_error'    =>  'Something went wrong when submitting the form.  This might be a faulty internet connection, or some other problem.  Please check your input and try again in a little while.  If the problem persists please contact your administrator.',
    'form_error_name'       =>  'Your name needs to be at least 2 characters long',
    'form_error_email'      =>  'This is an invalid email address',

    // WAYF
    'wayf_nothing_found'        => 'Nothing found',
    'wayf_apu'                  => 'Please try again with some different keywords',
    'wayf_noscript_warning_intro'     => 'Without JavaScript you will not be able to remember previously used accounts.  If you want to use that functionality, please enable JavaScript.',
    'wayf_noscript_warning_end'     => 'You can, off course, still log in.',
    'wayf_no_access_account'    => 'No access with this account',
    'wayf_delete_account'       => 'Delete from your accounts',
    'wayf_remaining_idps_title' => 'Add an account from the list below',
    'wayf_select_account'       => 'Select an account from the list below',
    'wayf_search_placeholder'   => 'Search...',
    'wayf_search_aria'          => 'Search identity providers',
    'wayf_your_accounts'        => 'Your accounts',
    'wayf_add_account'          => 'Use another account',
    'wayf_no_access_helpdesk'   => 'If you want, you can request access to this service. We will send the request to the helpdesk of your institution.',
    'wayf_no_access'            => 'Sorry, no access for this account',
    'wayf_noaccess_name'        => 'Your name',
    'wayf_noaccess_email'       => 'Your emailaddress',
    'wayf_noaccess_motivation'  => 'Motivation',
    'wayf_noaccess_success'     => 'Your request for access has been sent.',
    'wayf_defaultIdp'                => 'If your %organisation_noun% is not listed, <a href="%defaultIdpLink%" class="wayf__defaultIdpLink">%defaultIdpName% is available as an alternative.</a>',
    'wayf_idp_title_screenreader' => 'Login with ',
    'log_in_to'                 => 'Select an %organisationNoun% to login to %arg1%',

    // Consent
    'consent_h1_text'   => 'Do you give consent to share your information?',
    'consent_h1_text_minimal'   => 'This information will be shared with %arg1%',
    'consent_privacy_header'    => '%target% will receive',
    'consent_attributes_correction_text'    => 'Something incorrect?',
    'consent_ok'    =>  'Yes proceed',
    'consent_identifier_explanation'    => 'The identifier for this service is generated by %suite_name% en differs amongst each service you use through %suite_name%. The service can therefore recognise you as the same user when you return, but services cannot recognise you amongst each other as the same user.',
    'consent_provided_by'   => 'provided by <strong>%organisationName%</strong>',
    'consent_tooltip_screenreader'  => 'Why do we need this info?',
    'consent_nojs'   => '<p>Tooltips / modals on this page need JS to work with the keyboard.  If you use a keyboard, please enable JS if you wish to use this functionality.</p>',
    'consent_disclaimer_privacy_nolink' => '<div><strong>%org%</strong> needs this information to function properly.</div>'
    ,
    'consent_disclaimer_privacy'    => <<<'TXT'
<div><strong>%org%</strong> needs this information to function properly (read their <a href="%privacy_url%" target="_blank">privacy policy</a>).</div>
TXT
    ,
    'consent_disclaimer_secure' => <<<'TXT'
These data will be sent securely from your institution via <input type="checkbox" tabindex="-1" role="button" aria-hidden="true" class="modal visually-hidden" id="consent_disclaimer_about" name="consent_disclaimer_about"><br /><label class="modal" tabindex="0" for="consent_disclaimer_about"><span class="visually-hidden">%buttonText%</span>
%suite_name%</label> %modal_about% using <input type="checkbox" tabindex="-1" role="button" aria-hidden="true" class="modal visually-hidden" id="consent_disclaimer_number" name="consent_disclaimer_number"><label class="modal" tabindex="0" for="consent_disclaimer_number"><span class="visually-hidden">%buttonText%</span>a number that uniquely identifies you for this service.</label>
TXT
    ,
    'consent_disclaimer_secure_onemodal'    => <<<'TXT'
These data will be sent securely from your institution via <input type="checkbox" tabindex="-1" role="button" aria-hidden="true" class="modal visually-hidden" id="consent_disclaimer_about" name="consent_disclaimer_about"><label class="modal" tabindex="0" for="consent_disclaimer_about"><span class="visually-hidden">%buttonText%</span>%suite_name%</label> %modal_about% using a number that uniquely identifies you for this service.
TXT
    ,
    'consent_modal_about'   => <<<'TXT'
<section class="modal__value" role="alert">
    <div class="modal__content">
        %about_text%
        <a href="%read_more_url%" target="_blank" class="link__readmore">%read_more_text%</a>
    </div>
</section>
TXT
    ,
    // Consent slidein: About %suiteName%
    'consent_slidein_about_text_new'    => <<<'TXT'
<h3>Logging in through %suiteName%</h3>
<p>%suiteName% allows people to easily and securely log in into various cloud services using their own %accountNoun%. %suiteName% offers extra privacy protection by sending a minimum set of personal data to these cloud services.</p>
TXT
    ,

    // Consent slidein: Reject_skeune
    'consent_slidein_reject_text_skeune'    => <<<'TXT'
<h3>You don't want to share your data with the service</h3>
<p>The service you're logging into requires your data to function properly. If you prefer not to share your data, you cannot use this service. By closing your browser or just this tab you prevent your information from being shared with the service. If you change your mind later, please login to the service again and this screen will reappear.</p>
TXT
    ,
    'consent_groupmembership_show_more'     => 'Show more',
    'consent_groupmembership_show_less'     => 'Show less',
];
