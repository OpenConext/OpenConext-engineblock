<?php

$overrides = [];
$overridesFile = __DIR__ . '/overrides.nl.php';
if (file_exists($overridesFile)) {
    $overrides = require $overridesFile;
}

return $overrides + [
    // General
    'search'                    => 'Zoek een %organisationNoun%...',
    'search_screenreader'       => 'Zoeken',
    'log_in_to'                 => 'Selecteer een %organisationNoun% en login bij',
    'hamburger_screenreader'     => 'naar de footer',

    // Consent page
    'consent_header_title'                    => 'Om in te loggen heeft %arg1% jouw gegevens nodig',
    'consent_header_text'                     => 'De dienst heeft deze gegevens nodig om goed te kunnen functioneren. De gegevens worden vanuit jouw %organisationNoun% veilig verstuurd naar %arg1% via <a class="help" data-slidein="about"><span>%suiteName%</span></a>.',
    'consent_privacy_title'                   => 'De volgende gegevens worden doorgestuurd naar %arg1%:',
    'consent_privacy_link'                    => 'Lees het privacybeleid van deze dienst',
    'consent_attributes_correction_link'      => 'Kloppen de onderstaande gegevens niet?',
    'consent_buttons_title'                   => 'Ga je akkoord met het doorsturen van deze gegevens?',
    'consent_buttons_ok'                      => 'Ja, ga door naar %arg1%',
    'consent_footer_text_singular'            => 'Je gebruikt al één andere dienst via %suiteName%. <a href="%arg1%" target="_blank"><span>Bekijk hier het overzicht en je profielinformatie.</span></a>',
    'consent_footer_text_plural'              => 'Je gebruikt al %arg1% diensten via %suiteName%. <a href="%arg2%" target="_blank"><span>Bekijk hier het overzicht en je profielinformatie.</span></a>',
    'consent_footer_text_first_consent'       => 'Je gebruikt nog geen diensten via %suiteName%. <a href="%arg1%" target="_blank"><span>Bekijk hier je profielinformatie.</span></a>',
    // Consent slidein: About OpenConext
    'consent_slidein_about_head'  => 'Inloggen met %suiteName%',
    'consent_slidein_about_text'  => 'Via %suiteName% loggen personen met hun eigen %accountNoun% veilig en gemakkelijk in bij clouddiensten van verschillende aanbieders. %suiteName% biedt extra privacy-bescherming doordat een minimaal aantal persoonlijke gegevens wordt doorgegeven aan deze clouddiensten.',

    // Consent slidein: Reject
    'consent_slidein_reject_head'  => 'Je wilt geen gegevens delen met de dienst',
    'consent_slidein_reject_text'  => 'De dienst waar je probeert in te loggen heeft jouw gegevens nodig om te kunnen functioneren. Als je deze gegevens niet wilt delen dan kun je geen gebruik maken van deze dienst. Door je browser of dit tabblad af te sluiten voorkom je het doorsturen van je gegevens. Als je je hierna bedenkt, log dan opnieuw in bij de dienst; je krijgt dit scherm dan opnieuw te zien.',
    // Generic slide-in
    'slidein_close' => 'Sluiten',
];
