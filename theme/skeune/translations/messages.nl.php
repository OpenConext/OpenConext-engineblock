<?php
$overrides = [];
$overridesFile = __DIR__ . '/overrides.nl.php';
if (file_exists($overridesFile)) {
    $overrides = require $overridesFile;
}

return $overrides + [
    // General
    'button_screenreader'        =>  ', knop',
    'button_expandable_screenreader' => ' ,knop, uitklapbaar',
    'button_expanded_screenreader' => ' ,knop, uitgeklapt',
    'required_screenreader'      =>  'Verplicht',
    'search_screenreader'        =>  'Zoeken',
    'send_request'  =>  'Verzoek sturen',
    'back'          =>  'Terug',
    'noscript'      => 'Om deze pagina optimaal te laten functioneren moet JavaScript aan staan.',
    'language_switcher'     => 'Wissel van taal',

    // FOOTER
    'log_in_to'     => 'Selecteer een account om in te loggen bij %arg1%',
    'helpLink'       => 'https://support.surfconext.nl/wayf-nl',
    'footer_navigation_screenreader'    => 'Footer navigatie',

    // Forms
    'form_general_error'    =>  'Er ging iets fout tijdens het insturen van het formulier. Dit kan een probleem zijn met je internetverbinding of iets anders.  Controleer je invoer en probeer het later opnieuw.  Mocht het probleem zich blijven voordoen, neem dan contact op met je servicedesk.',
    'form_error_name'       =>  'Je naam moet minstens twee tekens lang zijn',
    'form_error_email'      =>  'Dit is geen geldig e-mailadres',

    // REDIRECT
    'processing_waiting'    =>  'Wachten op een reactie',

    // WAYF
    'wayf_nothing_found'        => 'Niets gevonden',
    'wayf_apu'                  => 'Probeer het opnieuw met andere zoektermen',
    'wayf_noscript_warning_intro'     => 'Je kunt geen gebruikte accounts onthouden en niet zoeken zonder JavaScript. Zet JavaScript aan in je browser als je deze functionaliteiten toch wenst te gebruiken.',
    'wayf_noscript_warning_end'     => 'Vanzelfsprekend kun je wel gewoon inloggen.',
    'wayf_delete_account_screenreader'       => 'Verwijder %idpTitle% uit je accounts',
    'wayf_deleted_account_screenreader'      => ' werd verwijderd uit uw accounts',
    'wayf_remaining_idps_title_screenreader' => 'Login met een account uit de onderstaande lijst',
    'wayf_select_account_screenreader'       => 'Selecteer een account uit de onderstaande lijst',
    'wayf_search_placeholder'   => 'Zoeken...',
    'wayf_search_screenreader'          => 'Zoek naar een %organisationNoun%',
    'wayf_search_reset_screenreader'    => 'Wis de tekst in het zoekveld',
    'wayf_search_results_screenreader' => '%orgNoun% gevonden',
    'wayf_your_accounts'        => 'Je accounts',
    'wayf_add_account'          => 'Gebruik een ander account',
    'wayf_no_access'            => 'Sorry, geen toegang met dit account',
    'wayf_no_access_account_screenreader'    => 'Geen toegang met dit account',
    'wayf_no_access_helpdesk'   => 'Je kunt toegang vragen tot deze dienst.  We sturen deze aanvraag door naar de helpdesk van je %orgNoun%.',
    'wayf_no_access_helpdesk_not_connected'     =>  "Ga terug naar de vorige pagina en klik op '%buttonText%'.",
    'wayf_noaccess_title_screenreader'       => 'Vraag toegang aan voor dit account',
    'wayf_noaccess_name'        => 'Je naam',
    'wayf_noaccess_email'       => 'Je e-mailadres',
    'wayf_noaccess_motivation'  => 'Motivatie',
    'wayf_noaccess_success'     => 'Je aanvraag voor toegang is verstuurd.',
    'wayf_noaccess_request_access_screenreader'  => 'Open het formulier om toegang aan te vragen',
    'wayf_noaccess_form_announcement_screenreader' => 'Er zijn verplichte velden niet, of niet goed ingevuld.',
    'wayf_defaultIdp_start'     => 'Als je %organisation_noun% niet in de lijst staat,',
    'wayf_defaultIdp_linkText'  => 'is %defaultIdpName% beschikbaar als alternatief.',
    'wayf_idp_title_screenreader' => 'Inloggen met ',
    'wayf_idp_title_noaccess_screenreader'  => 'Geen toegang met',

    // Consent
    'consent_h1_text'   => 'Ga je akkoord met het delen van je informatie?',
    'consent_h1_text_informational'   => 'Bekijk je informatie die zal worden gedeeld.',
    'consent_privacy_header'    => '%target% ontvangt',
    'consent_attributes_correction_text'    => 'Foutieve informatie?',
    'consent_ok'    =>  'Ja, ik geef toestemming',
    'consent_identifier_explanation'    => 'De identifier voor deze dienst wordt gegenereerd door %suite_name% en is verschillend voor elke dienst waar je gebruik van maakt via %suite_name%. De dienst kan je aan de hand van deze identifier herkennen als dezelfde gebruiker zodra je later terugkeert bij de dienst. Diensten onderling kunnen jou echter niet herkennen als dezelfde gebruiker wanneer zij gegevens uitwisselen.',
    'consent_provided_by'   => 'geleverd door',
    'consent_tooltip_screenreader'  => 'Waarom hebben we jouw %attr_name% nodig?',
    'consent_nojs'   => 'Sommige functionaliteiten op deze pagina vereisen JavaScript, zoals bedienen met je toetsenbord. Schakel JavaScript in in je browser indien je deze functionaliteiten wenst te gebruiken.',
    'consent_disclaimer_privacy_statement' => '(aangeboden door %org%) heeft deze informatie nodig om te kunnen werken',
    'consent_disclaimer_privacy_read'    => 'lees hun',
    'consent_disclaimer_privacy_policy'  => 'privacybeleid',
    'consent_disclaimer_secure' => 'wordt gebruikt door je %orgNoun% om informatie op een veilige manier te versturen naar %spName% (lees meer over',
    'consent_reject_text_skeune_header'    => 'Je wilt geen gegevens delen met deze dienst',
    'consent_reject_text_skeune_body'    => 'De dienst waar je bij wilt inloggen heeft deze gegevens nodig om te kunnen functioneren.  Indien je verkiest om je data niet te delen, kan je de dienst niet gebruiken.  Door je browser of door deze tab te sluiten verhinder je dat je informatie gedeeld wordt.  Mocht je later van gedachten veranderen, dan kun je opnieuw inloggen bij deze dienst en krijgt je dit scherm opnieuw te zien.',
    'consent_nok_title'     => "Je wilt geen gegevens delen met deze dienst",
    'consent_nok_text'      => "De dienst waarop je wilt inloggen heeft deze gegevens nodig om te kunnen functioneren.  Indien je verkiest om je gegevens niet te delen, kan je de dienst niet gebruiken.  Door je browser of door deze tab te sluiten verhinder je dat je informatie gedeeld wordt.  Mocht je later van gedachten veranderen, dan kan je opnieuw inloggen bij deze dienst en krijg je dit scherm opnieuw te zien.",
    'consent_groupmembership_show_more'     => 'Toon meer',
    'consent_groupmembership_show_less'     => 'Toon minder',
    'consent_warning_allowed_html'      => '<a><u><i><br><wbr><strong><em><blink><marquee>',
];
