<?php

$overrides = [];
$overridesFile = __DIR__ . '/overrides.en.php';
if (file_exists($overridesFile)) {
    $overrides = require $overridesFile;
}

return $overrides + [
    // General
    'search'                    => 'Search for an %organisationNoun%...',
    'search_screenreader'       => 'Search',
    'log_in_to'                 => 'Select an %organisationNoun% to log in to the application',
    'hamburger_screenreader'     => 'skip to footer',

    // Consent page
    'consent_header_title'                    => '%arg1% needs your information before logging in',
    'consent_header_text'                     => 'The application needs the following information to function properly. These data will be sent securely from your %organisationNoun% towards %arg1% via <a class="help" href="#" data-slidein="about"><span>%suiteName%</span></a>.',
    'consent_privacy_title'                   => 'The following information will be shared with %arg1%:',
    'consent_privacy_link'                    => 'Read the privacy policy of this application',
    'consent_attributes_correction_link'      => 'Are the details below incorrect?',
    'consent_buttons_title'                   => 'Do you agree with sharing this data?',
    'consent_buttons_ok'                      => 'Yes, proceed to %arg1%',
    'consent_footer_text_singular'            => 'You are using one other application via %suiteName%. <a href="%arg1%" target="_blank"><span>View the list of applications and your profile information.</span></a>',
    'consent_footer_text_plural'              => 'You are using %arg1% applications via %suiteName%. <a href="%arg2%" target="_blank"><span>View the list of applications and your profile information.</span></a>',
    'consent_footer_text_first_consent'       => 'You are not using any applications via %suiteName%. <a href="%arg1%" target="_blank"><span>View your profile information.</span></a>',
    // Consent slidein: About %suiteName%
    'consent_slidein_about_head'  => 'Logging in through %suiteName%',
    'consent_slidein_about_text'  => '%suiteName% allows people to easily and securely log in into various cloud applications using their own %accountNoun%. %suiteName% offers extra privacy protection by sending a minimum set of personal data to these cloud applications.',

    // Consent slidein: Reject
    'consent_slidein_reject_head'  => "You don't want to share your data with the application",
    'consent_slidein_reject_text'  => "The application you're logging in to requires your data to function properly. If you prefer not to share your data, you cannot use this application. By closing your browser or just this tab you prevent your information from being shared with the application. If you change your mind later, please log in to the application again and this screen will reappear.",

    // Generic slide-in
    'slidein_close' => 'Close',
];
