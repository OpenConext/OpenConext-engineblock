<?php
$overrides = [];
$overridesFile = __DIR__ . '/overrides.nl.php';
if (file_exists($overridesFile)) {
    $overrides = require $overridesFile;
}

return $overrides + [
    // Values used in placeholders for other translations
    // %suiteName%: OpenConext, SURFconext, ACMEconext
    'suite_name' => 'OpenConext',

    // Example translation message:
    //     'Find an %organisationNoun%'
    //
    // Becomes:
    //     'Find an organisation' (default)
    // or: 'Find an institution' (when overridden)
    'organisation_noun' => 'organisatie',
    'organisation_noun_plural' => 'organisaties',

    // Example translation message:
    //     'Use your %accountNoun%'
    //
    // Becomes:
    //     'Use your organisation account' (default)
    // or: 'Use your institutional account' (when overridden)
    'account_noun' => 'organisatieaccount',

    // General
    'value'                 => 'Waarde',
    'post_data'             => 'POST Data',
    'processing'            => 'Verbinden met de dienst',
    'processing_waiting'    => 'Wachten op een reactie van de dienst.',
    'processing_long'       => 'Wees a.u.b. geduldig, het kan even duren...',
    'go_back'               => '&lt;&lt; Ga terug',
    'note'                  => 'Mededeling',
    'note_no_script'        => 'Jouw browser ondersteunt geen JavaScript. Je moet op de onderstaande knop drukken om door te gaan.',

     // Feedback
    'timestamp'             => 'Timestamp',
    'requestId'             => 'Uniek Request ID',
    'identityProvider'      => 'Identity Provider',
    'serviceProvider'       => 'Service Provider',
    'userAgent'             => 'User Agent',
    'ipAddress'             => 'IP-adres',
    'statusCode'            => 'Statuscode',
    'statusMessage'         => 'Statusbericht',

    // WAYF
    'search'                    => 'Zoek een %organisationNoun%...',
    'our_suggestion'            => 'Onze suggestie:',
    'idps_with_access'          => '%organisationNounPlural% met toegang',
    'idps_without_access'       => '%organisationNounPlural% zonder toegang',
    'log_in_to'                 => 'Selecteer een %organisationNoun% en login bij service',
    'press_enter_to_select'     => 'Druk op enter om te kiezen',
    'loading_idps'              => '%organisationNounPlural% worden geladen...',
    'request_access'            => 'Toegang aanvragen',
    'no_idp_results'            => 'Je zoekterm heeft geen resultaten opgeleverd.',
    'no_idp_results_request_access' => 'Kun je je %organisationNoun% niet vinden? &nbsp;<a href="#no-access" class="noaccess">Vraag toegang aan</a>&nbsp;of pas je zoekopdracht aan.',
    'more_idp_results'          => '%arg1% resultaten worden niet getoond. Verfijn je zoekopdracht voor specifiekere resultaten.',
    'return_to_sp'              => 'Keer terug naar Service Provider',

    // Help page
    'help_header'       => 'Help',
    'help_page_content' => <<<HTML
<p>No help content available.</p>
HTML
    ,

    // Remove cookies
    'remember_choice'           => 'Onthoud mijn keuze',
    'cookie_removal_header'     => 'Cookies verwijderen',
    'cookie_remove_button'      => 'Verwijderen',
    'cookie_remove_all_button'  => 'Alles verwijderen',
    'cookie_removal_description' => '<p>In onderstaande overzicht vindt u uw opgeslagen cookies en een de mogelijk om deze individueel of allemaal tegelijk te verwijderen.</p>',
    'cookie_removal_confirm'     => 'Uw cookie is verwijderd.',
    'cookies_removal_confirm'    => 'Uw cookies zijn verwijderd.',

    // Footer
    'service_by'            => 'Deze dienst is verbonden via',
    'serviceprovider_link'  => '%suiteName%',
    'terms_of_service_link' => '<a href="#" target="_blank">Gebruiksvoorwaarden</a>',

    // Request Access Form
    'request_access_instructions' => '<h2>Helaas, je hebt geen toegang tot de dienst die je zoekt. Wat nu?</h2>
                                <p>Wil je toch graag toegang tot deze dienst, vul dan het onderstaande formulier in.
                                   Wij sturen je verzoek door naar de juiste persoon binnen jouw %organisationNoun%.</p>',
    'name'                  => 'Naam',
    'name_error'            => 'Vul je naam in',
    'email'                 => 'E-mail',
    'email_error'           => 'Vul je (correcte) e-mailadres in',
    'institution'           => '%organisationNoun%',
    'institution_error'     => 'Vul jouw %organisationNoun% in',
    'comment'               => 'Toelichting',
    'comment_error'         => 'Vul een toelichting in',
    'cancel'                => 'Annuleren',
    'send'                  => 'Verstuur',
    'close'                 => 'Sluiten',

    'send_confirm'          => 'Je verzoek is verzonden',
    'send_confirm_desc'     => '<p>Je verzoek is doorgestuurd naar de juiste persoon binnen jouw %organisationNoun%. Het is aan deze persoon om actie te ondernemen op basis van jouw verzoek. Het kan zijn dat er nog afspraken gemaakt moeten worden tussen jouw %organisationNoun% en de dienstaanbieder.</p>',

    // Consent page
    'consent_header_title'                    => 'Om in te loggen heeft %arg1% jouw gegevens nodig',
    'consent_header_text'                     => 'De dienst heeft deze gegevens nodig om goed te kunnen functioneren. De gegevens worden vanuit jouw %organisationNoun% veilig verstuurd naar %arg1% via <a class="help" data-slidein="about">%suiteName%</a>.',
    'consent_privacy_title'                   => 'De volgende gegevens worden doorgestuurd naar %arg1%:',
    'consent_privacy_link'                    => 'Lees het privacybeleid van deze dienst',
    'consent_attributes_correction_link'      => 'Kloppen deze gegevens niet?',
    'consent_attributes_show_more'            => 'Toon alle gegevens',
    'consent_attributes_show_less'            => 'Toon minder gegevens',
    'consent_buttons_title'                   => 'Ga je akkoord met het doorsturen van deze gegevens?',
    'consent_buttons_ok'                      => 'Ja, ga door naar %arg1%',
    'consent_buttons_ok_minimal'              => 'Ga door naar %arg1%',
    'consent_buttons_nok'                     => 'Nee, ik ga niet akkoord',
    'consent_buttons_nok_minimal'             => 'Annuleren',
    'consent_explanation_title'               => 'Let op bij het gebruik van deze dienst',
    'consent_footer_text_singular'            => 'Je gebruikt al één andere dienst via %suiteName%. <a href="%arg1%" target="_blank">Bekijk hier het overzicht en je profielinformatie.</a>',
    'consent_footer_text_plural'              => 'Je gebruikt al %arg1% diensten via %suiteName%. <a href="%arg2%" target="_blank">Bekijk hier het overzicht en je profielinformatie.</a>',
    'consent_footer_text_first_consent'       => 'Je gebruikt nog geen diensten via %suiteName%. <a href="%arg1%" target="_blank">Bekijk hier je profielinformatie.</a>',
    'consent_name_id_label'                   => 'Identifier',
    'consent_name_id_support_link'            => 'Uitleg',
    'consent_name_id_value_tooltip'           => 'De identifier voor deze dienst wordt door %arg1% zelf gegenereerd en verschilt per dienst je via %arg1% gebruikt. De dienst kan jou dus wel herkennen als dezelfde gebruiker als je opnieuw inlogt, maar diensten kunnen onderling niet zien dat het om dezelfde gebruiker gaat.',
    'consent_slidein_details_email'           => 'Email',
    'consent_slidein_details_phone'           => 'Telefoon',
    'consent_slidein_text_contact'            => 'Neem voor vragen hierover contact op met de helpdesk van je %organisationNoun%. De volgende gegevens zijn bij %suiteName% bekend:',
    'consent_slidein_text_no_support'         => 'Er zijn geen contactgegevens beschikbaar.',

    // Consent slidein: Kloppen de getoonde gegevens niet?
    'consent_slidein_correction_title' => 'Kloppen de getoonde gegevens niet?',
    'consent_slidein_correction_text_idp'  => '%suiteName% ontvangt de gegevens rechtstreeks van jouw %organisationNoun% en slaat deze zelf niet op. Neem contact op met de helpdesk van je %organisationNoun% als je gegevens niet kloppen.',
    'consent_slidein_correction_text_aa'  => '%suiteName% ontvangt de gegevens rechtstreeks van de attribuutbron en slaat deze zelf niet op. Neem contact op met de getoonde attribuutbron als je gegevens niet kloppen. Als je daarbij hulp nodig hebt, kun je contact opnemen met de helpdesk van je eigen %organisationNoun%.',
    'consent_slidein_correction_details_title' => 'Contactgegevens %arg1%:',

    // Consent slidein: About OpenConext
    'consent_slidein_about_text'  => <<<'TXT'
<h1>Inloggen met %suiteName%</h1>
<p>Via %suiteName% loggen personen met hun eigen %accountNoun% veilig en gemakkelijk in bij clouddiensten van verschillende aanbieders. %suiteName% biedt extra privacy-bescherming doordat een minimaal aantal persoonlijke gegevens wordt doorgegeven aan deze clouddiensten.</p>
TXT
    ,

    // Consent slidein: Reject
    'consent_slidein_reject_text'  => <<<'TXT'
<h1>Je geeft geen toestemming om gegevens door te sturen</h1>
<p>De dienst waar je probeert in te loggen heeft jouw gegevens nodig om te kunnen functioneren. Als je hier geen toestemming voor geeft dan kun je geen gebruik maken van deze dienst. Door je browser of dit tabblad af te sluiten geef je geen toestemming voor het doorsturen van gegevens. Als je je hierna bedenkt, log dan opnieuw in bij de dienst; je wordt dan opnieuw gevraagd om toestemming te geven.</p>
TXT
    ,

    // Generic slide-in
    'slidein_close' => 'Sluiten',
    'slidein_read_more' => 'Lees meer',

    // Error screens
    'error_404'                         => '404 - Pagina niet gevonden',
    'error_404_desc'                    => 'De pagina is niet gevonden.',
    'error_help_desc'                   => '<p></p>',
    'error_no_consent'                  => 'Niet mogelijk om verder te gaan naar dienst',
    'error_no_consent_desc'             => 'Deze applicatie kan enkel worden gebruikt wanneer de vermelde informatie wordt gedeeld.<br /><br />

Als je deze applicatie wilt gebruiken moet je:<br />
<ul><li>de browser herstarten</li>
<li>opnieuw inloggen</li>
<li>jouw informatie delen</li></ul>',
    'error_no_idps'                     => 'Error - Geen %organisationNounPlural% gevonden',
    'error_no_idps_desc'                => '<p>
        De dienst die je probeert te benaderen (de &lsquo;Service Provider&rsquo;) is niet toegankelijk via de %suiteName%-infrastructuur.
    </p>',
    'error_session_lost'                => 'Error - Sessie is verloren gegaan',
    'error_session_lost_desc'           => '<p>
We weten helaas niet waar je heen wilt. Heb je te lang gewacht? Probeer het dan eerst opnieuw. Accepteert je browser wel cookies? Maak je gebruik van een te oude link of bookmark?<br /><br />
    </p>',

    'error_authorization_policy_violation'            => 'Error - Geen toegang',
    'error_authorization_policy_violation_desc'       => '<p>
        Je bent succesvol ingelogd bij jouw %organisationNoun%, maar je kunt geen gebruik maken van deze dienst omdat je geen toegang hebt. Voor deze dienst (de &lsquo;Service Provider&rsquo;) heeft jouw %organisationNoun% met <i>autorisatieregels</i> ingesteld dat alleen bepaalde gebruikers toegang krijgen. Neem contact op met de (IT-)servicedesk van je %organisationNoun% als je vindt dat je wel toegang moet hebben.
    </p>',
    'error_authorization_policy_violation_info'       => 'Bericht van je %organisationNoun%: ',
    'error_no_message'                  => 'Error - Geen bericht ontvangen',
    'error_no_message_desc'             => 'We verwachtten een bericht, maar we hebben er geen ontvangen. Er is iets fout gegaan. Probeer het alstublieft opnieuw.',
    'error_invalid_acs_location'        => 'De opgegeven "Assertion Consumer Service" is onjuist of bestaat niet.',
    'error_invalid_acs_binding'        => 'Onjuist ACS Binding Type',
    'error_invalid_acs_binding_desc'        => 'Het opgegeven of geconfigureerde "Assertion Consumer Service" Binding Type is onjuist of bestaat niet.',
    'error_unsupported_signature_method' => 'Ondertekeningsmethode wordt niet ondersteund',
    'error_unsupported_signature_method_desc' => 'De ondertekeningsmethode %arg1% wordt niet ondersteund, upgrade naar RSA-SHA256 (http://www.w3.org/2001/04/xmldsig-more#rsa-sha256).',
    'error_unknown_preselected_idp' => 'Error - %organisationNoun% is niet gekoppeld aan dienst',
    'error_unknown_preselected_idp_desc' => '<p>
        De %organisationNoun% waarmee je wilt inloggen heeft toegang tot deze dienst niet geactiveerd. Dat betekent dat jij geen gebruik kunt maken van deze dienst via %suiteName%. Neem contact op met de helpdesk van jouw %organisationNoun% als je toegang wilt krijgen tot deze dienst. Geef daarbij aan om welke dienst het gaat (de &lsquo;Service Provider&rsquo;) en waarom je toegang wilt.
    </p>',
    'error_unknown_service_provider'              => 'Error - Kan geen metadata ophalen voor EntityID \'%arg1%\'',
    'error_unknown_service_provider_desc'     => '<p>
        Er kon geen Service Provider worden gevonden met het opgegeven EntityID.
    </p>',

    'error_unknown_issuer'              => 'Error - Onbekende dienst',
    'error_unknown_issuer_desc'     => '<p>Je aangevraagde dienst kon niet worden gevonden.</p>',
    'error_generic'                     => 'Error - Foutmelding',
    'error_generic_desc'                => '<p>
Inloggen is niet gelukt en we kunnen je niet precies vertellen waarom. Probeer het eerst eens opnieuw, en neem anders contact op met het supportteam van uw %organisationNoun%.
    </p>',
    'error_missing_required_fields'     => 'Error - Verplichte velden ontbreken',
    'error_missing_required_fields_desc'=> '<p>
        Jouw %organisationNoun% geeft niet de benodigde informatie vrij. Daarom kun je deze applicatie niet gebruiken.
    </p>
    <p>
        Neem alstublieft contact op met jouw %organisationNoun%. Geef hierbij de onderstaande informatie door.
    </p>
    <p>
        Omdat je %organisationNoun% niet de juiste attributen aan %suiteName% doorgeeft is het inloggen mislukt. De volgende attributen zijn vereist om succesvol in te loggen op het %suiteName% platform:
        <ul>
            <li>UID</li>
            <li>schacHomeOrganization</li>
        </ul>
    </p>',

    'error_received_error_status_code'     => 'Error - Fout bij Identity Provider',
    'error_received_error_status_code_desc'=> '<p>
Je %organisationNoun% heeft je de toegang geweigerd tot deze dienst. Je zult dus contact moeten opnemen met de (IT-)servicedesk van je eigen %organisationNoun% om te kijken of dit verholpen kan worden.
    </p>',
    'error_received_invalid_response'        => 'Error - Ongeldig antwoord van Identity Provider',
    'error_received_invalid_signed_response' => 'Error - Ongeldige handtekening op antwoord Identity Provider',
    'error_stuck_in_authentication_loop' => 'Error - Je zat vast in een zwart gat',
    'error_stuck_in_authentication_loop_desc' => '<p>
        Je bent succesvol ingelogd bij je Identity Provider maar de dienst waar je naartoe wilt stuurt je weer terug naar %suiteName%. Omdat je succesvol bent ingelogd, stuurt %suiteName% je weer naar de dienst, wat resulteert in een oneindig zwart gat. Dit komt waarschijnlijk door een foutje aan de kant van de dienst.
    </p>',

    /**
     * %1 AttributeName
     * %2 Options
     * %3 (optional) Value
     * @url http://nl3.php.net/sprintf
     */
    'error_attribute_validator_type_uri'            => '\'%arg3%\' is geen geldige URI',
    'error_attribute_validator_type_urn'            => '\'%arg3%\' is geen geldige URN',
    'error_attribute_validator_type_url'            => '\'%arg3%\' is geen geldige URL',
    'error_attribute_validator_type_hostname'       => '\'%arg3%\' is geen geldige hostname',
    'error_attribute_validator_type_emailaddress'   => '\'%arg3%\' is geen geldig emailadres',
    'error_attribute_validator_minlength'           => '\'%arg3%\' is niet lang genoeg (minimaal %arg2% karakters)',
    'error_attribute_validator_maxlength'           => '\'%arg3%\' is te lang (maximaal %arg2% karakters)',
    'error_attribute_validator_min'                 => '%arg1% heeft minimaal %arg2% waardes nodig (%arg3% gegeven)',
    'error_attribute_validator_max'                 => '%arg1% heeft maximaal %arg2% waardes (%arg3% gegeven)',
    'error_attribute_validator_regex'               => '\'%arg3%\' voldoet niet aan de voorwaarden voor waardes van dit attribuut (%arg2%)',
    'error_attribute_validator_not_in_definitions'  => '%arg1% is niet bekend in het schema',
    'error_attribute_validator_allowed'             => '\'%arg3%\' is geen toegestane waarde voor dit attribuut',
    'error_attribute_validator_availability'        => '\'%arg3%\' is a gereserveerde SchacHomeOrganization voor een andere Identity Provider',

    'error_unknown_service'         => 'Error - Deze dienst is niet geregistreerd bij %suiteName%.',
    'error_unknown_service_desc'    => '<p>Deze dienst is niet bekend.</p>',

    'attributes_validation_succeeded' => 'Authenticatie geslaagd',
    'attributes_validation_failed'    => 'Sommige attributen kunnen niet gevalideerd worden',
    'attributes_data_mailed'          => 'De attribuutdata zijn gemaild',
    'idp_debugging_title'             => 'Toon verkregen response van Identity Provider',
    'retry'                           => 'Opnieuw',

    'attributes' => 'Attributen',
    'validation' => 'Validatie',
    'idp_debugging_mail_explain' => 'Indien gevraagd door %suiteName%,
                                        gebruik de "Mail naar %suiteName%" knop hieronder
                                        om de informatie op dit scherm naar %suiteName% beheer te e-mailen.',
    'idp_debugging_mail_button' => 'Mail naar %suiteName%',

    // Logout
    'logout' => 'uitloggen',
    'logout_description' => 'Deze applicatie maakt gebruik van centrale login. Hiermee is het mogelijk om met single sign on bij verschillende applicaties in te loggen. Om er 100% zeker van te zijn dat je uitgelogd bent, moet je de browser helemaal afsluiten.',
    'logout_information_link' => '',
];
