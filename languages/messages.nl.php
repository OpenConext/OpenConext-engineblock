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

    // Email
    // %supportUrl% can be used as a placeholder for other translations
    'openconext_support_url' => 'https://example.org',
    'openconext_terms_of_use_url' => 'https://example.org',
    'name_id_support_url' => 'https://example.org',

    // General
    'value'                 => 'Waarde',
    'post_data'             => 'POST Data',
    'processing'            => 'Verbinden met de dienst',
    'processing_waiting'    => 'Wachten op een reactie van de dienst.',
    'processing_long'       => 'Wees a.u.b. geduldig, het kan even duren...',
    'go_back'               => '&lt;&lt; Ga terug',
    'note'                  => 'Mededeling',
    'note_no_script'        => 'Jouw browser ondersteunt geen JavaScript. Je moet op de onderstaande knop drukken om door te gaan.',
    'unknown_organization_name' => 'Onbekend',
    'site_notice'           => '<p>Hallo, mijnheer De Uil<br/>Waar breng je ons naar toe<br/>Naar Fabeltjesland</p><p>Eh, ja, naar Fabeltjesland</p><p>En lees je ons dan voor<br/>Uit de Fabeltjeskrant</p><p>Ja, ja, uit de Fabeltjeskrant<br/>Want daarin staat precies vermeld<br/>Hoe het met de dieren is gesteld</p><p>Echt waar? Echt waar<br/>Echt waar mijnheer De Uil.</p>',

     // Feedback
    'requestId'             => 'UR ID',
    'identityProvider'      => 'IdP',
    'serviceProvider'       => 'SP',
    'serviceProviderName'   => 'SP Name',
    'proxyServiceProvider'  => 'Proxy SP',
    'ipAddress'             => 'IP',
    'statusCode'            => 'Statuscode',
    'artCode'               => 'EC',
    'statusMessage'         => 'Statusbericht',
    'attributeName'         => 'Attribuutnaam',
    'attributeValue'        => 'Attribuutwaarde',

    // WAYF
    'our_suggestion'            => 'Eerder gekozen:',
    'edit'                      => 'bewerken',
    'done'                      => 'klaar',
    'idps_with_access'          => '%organisationNounPlural% met toegang:',
    'idps_without_access'       => '%organisationNounPlural% zonder toegang:',
    'loading_idps'              => '%organisationNounPlural% worden geladen...',
    'request_access'            => 'Toegang aanvragen',
    'no_idp_results'            => 'Je zoekterm heeft geen resultaten opgeleverd.',
    'no_idp_results_request_access' => 'Kun je je %organisationNoun% niet vinden? &nbsp;<a href="#no-access" class="noaccess">Vraag toegang aan</a>&nbsp;of pas je zoekopdracht aan.',
    'more_idp_results'          => '%arg1% resultaten worden niet getoond. Verfijn je zoekopdracht voor specifiekere resultaten.',
    'return_to_sp'              => 'Keer terug naar Service Provider',

    // Help page
    'help_header'       => 'Help',
    'help_page_content' => <<<HTML
<p>Geen help-informatie beschikbaar.</p>
HTML
    ,

    // Remove cookies
    'remember_choice'           => 'Onthoud mijn keuze',
    'cookie_removal_header'     => 'Cookies verwijderen',
    'cookie_remove_button'      => 'Verwijderen',
    'cookie_remove_all_button'  => 'Alles verwijderen',
    'cookie_removal_description' => 'In onderstaande overzicht vindt u uw opgeslagen cookies en een de mogelijk om deze individueel of allemaal tegelijk te verwijderen.',
    'cookie_removal_confirm'     => 'Uw cookie is verwijderd.',
    'cookies_removal_confirm'    => 'Uw cookies zijn verwijderd.',

    // Footer
    'service_by'            => 'Deze dienst is verbonden via',
    'serviceprovider_link_text'  => '%suiteName%',
    'serviceprovider_link_target'  => 'https://openconext.org/',
    'terms_of_service_link_text' => 'Gebruiksvoorwaarden',
    'terms_of_service_link_target' => '#',

    // Request Access Form
    'request_access_instructions_head' => 'Helaas, je hebt geen toegang tot de dienst die je zoekt. Wat nu?',
    'request_access_instructions_text' => 'Wil je toch graag toegang tot deze dienst, vul dan het onderstaande formulier in.
                                   Wij sturen je verzoek door naar de juiste persoon binnen jouw %organisationNoun%.',
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
    'required'              => 'Verplicht',
    'send_confirm'          => 'Je verzoek is verzonden',
    'send_confirm_desc'     => 'Je verzoek is doorgestuurd naar de juiste persoon binnen jouw %organisationNoun%. Het is aan deze persoon om actie te ondernemen op basis van jouw verzoek. Het kan zijn dat er nog afspraken gemaakt moeten worden tussen jouw %organisationNoun% en de dienstaanbieder.',

    // Consent page
    'consent_attributes_screenreader'         => 'over %orgName%',
    'consent_attributes_show_more'            => 'Toon alle gegevens',
    'consent_attributes_show_less'            => 'Toon minder gegevens',
    'consent_no_attributes_text'              => 'Voor deze dienst zijn geen gegevens van jouw %organisationNoun% nodig.',
    'consent_buttons_ok_informational'        => 'Doorgaan naar %sp%',
    'consent_buttons_nok'                     => 'Nee, ik ga niet akkoord',
    'consent_buttons_nok_informational'       => 'Annuleren',
    'consent_explanation_title'               => 'Let op bij het gebruik van deze dienst',
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


    // Generic slide-in
    'slidein_read_more' => 'Lees meer',

    // Error screens
    'error_feedback_info_intro' => 'Blijft deze foutmelding terugkomen? Maak dan gebruik van de hieronder vermelde hulpopties. Vermeld bij contact met de helpdesk de onderstaande codes:',
    'error_wiki-href' => 'https://nl.wikipedia.org/wiki/SURFnet',
    'error_wiki-link-text' => '%suiteName% Wiki',
    'error_wiki-link-text-short' => 'Wiki',
    'error_help-desk-href' => 'https://www.surf.nl/over-surf/dienstverlening-support-werkmaatschappijen',
    'error_help-desk-link-text' => 'Helpdesk',
    'error_help-desk-link-text-short' => 'Helpdesk',
    'error_return-sp-link-text' => 'Terug naar %spName%',
    'error_return-sp-link-text-short' => 'Terug naar dienst',

    'error_404'                         => '404 - Pagina niet gevonden',
    'error_404_desc'                    => 'De pagina is niet gevonden.',
    'error_405'                         => 'HTTP methode is niet toegestaan',
    'error_405_desc'                    => 'De HTTP-methode "%requestMethod%" is niet toegestaan voor locatie "%uri%". Ondersteunde methodes zijn: %allowedMethods%.',
    'error_help_desc'                   => '',
    'error_no_idps'                     => 'Error - Geen %organisationNounPlural% gevonden',
    'error_no_idps_desc'                => 'Inloggen op %spName% via %suiteName% is onmogelijk. %spName% is niet gekoppeld met een %organisationNoun%.',
    'error_no_idps_desc_no_sp_name'                => 'Inloggen op de dienst via %suiteName% is onmogelijk. De dienst is niet gekoppeld met een %organisationNoun%.',
    'error_session_lost'                => 'Fout - Sessie is verloren gegaan',
    'error_session_lost_desc'           => 'Om verder te gaan naar de dienst heb je een actieve sessie nodig, maar deze is verlopen. Heb je misschien te lang gewacht met inloggen? Ga terug naar de dienst en probeer het nog een keer. Als dat niet werkt, sluit je browser af en probeer nogmaals opnieuw in te loggen.',
    'error_session_not_started'                => 'Fout - Geen sessie gevonden',
    'error_session_not_started_desc'           => 'Om verder te gaan naar de dienst heb je een actieve sessie nodig, maar we kunnen deze niet vinden. Je browser moet cookies ondersteunen. Ook kan de link die je hebt gebruikt om bij de dienst te komen, verkeerd zijn. Ga terug naar de dienst en probeer het opnieuw. Als dat niet werkt, probeer een andere browser.',
    'error_authorization_policy_violation'            => 'Fout - Geen toegang',
    'error_authorization_policy_violation_desc'       => 'Neem contact op met de helpdesk van %idpName% als je toegang tot %spName% wilt. Vermeld daarbij dat je probeerde in te loggen op %spName% en dat je werd tegengehouden door een autorisatieregel van %suiteName%, geconfigureerd door %idpName%.',
    'error_authorization_policy_violation_desc_no_idp_name'       => 'Neem contact op met de helpdesk van je eigen %organisationNoun% als je toegang tot %spName% wilt. Vermeld daarbij dat je probeerde in te loggen op %spName% en dat je werd tegengehouden door een autorisatieregel van %suiteName%, geconfigureerd door jouw eigen %organisationNoun%.',
    'error_authorization_policy_violation_desc_no_sp_name'       => 'Neem contact op met de helpdesk van %idpName% als je toegang tot deze dienst wilt. Vermeld daarbij op welke dienst je probeerde in te loggen en dat je werd tegengehouden door een autorisatieregel van %suiteName%, geconfigureerd door %idpName%.',
    'error_authorization_policy_violation_desc_no_name'       => 'Neem contact op met de helpdesk van je eigen %organisationNoun% als je toegang tot deze dienst wilt. Vermeld daarbij op welke dienst je probeerde in te loggen en dat je werd tegengehouden door een autorisatieregel van %suiteName%, geconfigureerd door jouw eigen %organisationNoun%.',
    'error_authorization_policy_violation_info'       => 'Bericht van %idpName%: ',
    'error_authorization_policy_violation_info_no_idp_name'       => 'Bericht van je %organisationNoun%: ',
    'error_no_message'                  => 'Fout - Geen bericht ontvangen',
    'error_no_message_desc'             => 'We verwachtten een SAML bericht, maar we hebben er geen ontvangen. Er is iets fout gegaan. Probeer het alstublieft opnieuw.',
    'error_invalid_acs_location'        => 'De opgegeven "Assertion Consumer Service" is onjuist of bestaat niet.',
    'error_invalid_acs_binding'        => 'Fout - Onjuist ACS binding type',
    'error_invalid_acs_binding_desc'        => 'Het opgegeven of geconfigureerde "Assertion Consumer Service" Binding Type is onjuist of bestaat niet.',
    'error_unsupported_signature_method' => 'Fout - Ondertekeningsmethode wordt niet ondersteund',
    'error_unsupported_signature_method_desc' => 'De ondertekeningsmethode %arg1% wordt niet ondersteund, upgrade naar RSA-SHA256 (http://www.w3.org/2001/04/xmldsig-more#rsa-sha256).',
    'error_unknown_keyid' => 'Fout - onbekend key-ID',
    'error_unknown_keyid_desc' => 'De gevraagde key-ID is niet bekend bij %suiteName%. Wellicht gebruikt de service provider achterhaalde metadata of is er sprake van een andere configuratiefout.',
    'error_unknown_preselected_idp' => 'Fout - %spName% niet toegankelijk via %organisationNoun%',
    'error_unknown_preselected_idp_no_sp_name' => 'Fout - Dienst niet toegankelijk via %organisationNoun%',
    'error_unknown_preselected_idp_desc' => 'De %organisationNoun% waarmee je wilt inloggen heeft toegang tot %spName% niet geactiveerd. Dat betekent dat jij geen gebruik kunt maken van deze dienst via %suiteName%. Neem contact op met de helpdesk van jouw %organisationNoun% als je toegang wilt krijgen tot %spName%. Geef daarbij aan dat het om %spName% gaat en waarom je toegang wilt.',
    'error_unknown_preselected_idp_desc_no_sp_name' => 'De %organisationNoun% waarmee je wilt inloggen heeft toegang tot deze dienst niet geactiveerd. Dat betekent dat jij geen gebruik kunt maken van deze dienst via %suiteName%. Neem contact op met de helpdesk van jouw %organisationNoun% als je toegang wilt krijgen tot deze dienst. Geef daarbij aan om welke dienst het gaat (de "SP") en waarom je toegang wilt.',
    'error_unknown_service_provider'                => 'Error - %spName% onbekend',
    'error_unknown_service_provider_no_sp_name'     => 'Error - Onbekende dienst',
    'error_unknown_service_provider_desc'     => '%spName% is onbekend bij %suiteName%. Wellicht heeft %idpName% toegang tot deze dienst niet geactiveerd. Wil je gebruik maken van %spName%, wend je dan tot de helpdesk van %idpName%.',
    'error_unknown_service_provider_desc_no_sp_name' => 'De verzochte Service Provider is onbekend bij %suiteName%. Wellicht heeft %idpName% toegang tot deze dienst niet geactiveerd. Wil je gebruik maken van deze dienst, wend je dan tot de helpdesk van %idpName%.',
    'error_unknown_service_provider_desc_no_idp_name' => '%spName% is onbekend bij %suiteName%. Wellicht heeft je %organisationNoun% toegang tot deze dienst niet geactiveerd. Wil je gebruik maken van %spName%, wend je dan tot de helpdesk van je %organisationNoun%.',
    'error_unknown_service_provider_desc_no_names' => 'De verzochte Service Provider is onbekend bij %suiteName%. Wellicht heeft je %organisationNoun% toegang tot deze dienst niet geactiveerd. Wil je gebruik maken van deze dienst, wend je dan tot de helpdesk van je %organisationNoun%.',
    'error_unsupported_acs_location_scheme' => 'Fout - URI scheme van de ACS locatie wordt niet ondersteund',
    'error_unknown_identity_provider'              => 'Error - %idpName% onbekend',
    'error_unknown_identity_provider_no_idp_name'   => 'Error - Onbekende %organisationNoun%',
    'error_unknown_identity_provider_desc'     => '%idpName%, waarmee je probeert in te loggen, is onbekend bij %suiteName%.',
    'error_unknown_identity_provider_desc_no_idp_name'     => 'De %organisationNoun% waarmee je probeert in te loggen is onbekend bij %suiteName%.',
    'error_unknown_signing_key' => 'Error - onbekende signing key',
    'error_unknown_signing_key_desc' => 'De gebruikte signing key is niet bekend bij %suiteName%. Dit komt waarschijnlijk door een configuratiefout.',
    'error_generic'                     => 'Fout - Generieke foutmelding',
    'error_generic_desc'                => 'Inloggen is niet gelukt en we weten niet precies waarom. Probeer het eerst eens opnieuw door terug te gaan naar %spName% en opnieuw in te loggen. Lukt dit niet, neem dan contact op met de helpdesk van %idpName%.',
    'error_generic_desc_no_sp_name' => 'Inloggen is niet gelukt en we weten niet precies waarom. Probeer het eerst eens opnieuw door terug te gaan naar de dienst en opnieuw in te loggen. Lukt dit niet, neem dan contact op met de helpdesk van %idpName%.',
    'error_generic_desc_no_idp_name' => 'Inloggen is niet gelukt en we weten niet precies waarom. Probeer het eerst eens opnieuw door terug te gaan naar %spName% en opnieuw in te loggen. Lukt dit niet, neem dan contact op met de helpdesk van je %organisationNoun%.',
    'error_generic_desc_no_names' => 'Inloggen is niet gelukt en we weten niet precies waarom. Probeer het eerst eens opnieuw door terug te gaan naar de dienst en opnieuw in te loggen. Lukt dit niet, neem dan contact op met de helpdesk van je %organisationNoun%.',
    'error_missing_required_fields'     => 'Error - Verplichte velden ontbreken',
    'error_missing_required_fields_desc'=> '%idpName% geeft niet de benodigde informatie vrij. Daarom kun je %spName% niet gebruiken. Neem alstublieft contact op met de helpdesk van %idpName%. Geef hierbij de onderstaande informatie door. Omdat %idpName% niet de juiste attributen aan %suiteName% doorgeeft, of in het onjuiste formaat, is het inloggen mislukt. De volgende attributen zijn vereist om succesvol in te loggen op het %suiteName% platform:',
    'error_missing_required_fields_desc_no_idp_name'=> 'Jouw %organisationNoun% geeft niet de benodigde informatie vrij. Daarom kun je %spName% niet gebruiken. Neem alstublieft contact op met de helpdesk van jouw %organisationNoun%. Geef hierbij de onderstaande informatie door. Omdat je %organisationNoun% niet de juiste attributen aan %suiteName% doorgeeft is het inloggen mislukt. De volgende attributen zijn vereist om succesvol in te loggen op het %suiteName% platform:',
    'error_missing_required_fields_desc_no_sp_name'=> '%idpName% geeft niet de benodigde informatie vrij. Daarom kun je deze applicatie  niet gebruiken. Neem alstublieft contact op met de helpdesk van %idpName%. Geef hierbij de onderstaande informatie door. Omdat %idpName% niet de juiste attributen aan %suiteName% doorgeeft is het inloggen mislukt. De volgende attributen zijn vereist om succesvol in te loggen op het %suiteName% platform:',
    'error_missing_required_fields_desc_no_name'=> 'Jouw %organisationNoun% geeft niet de benodigde informatie vrij. Daarom kun je deze applicatie niet gebruiken. Neem alstublieft contact op met jouw %organisationNoun%. Geef hierbij de onderstaande informatie door. Omdat je %organisationNoun% niet de juiste attributen aan %suiteName% doorgeeft is het inloggen mislukt. De volgende attributen zijn vereist om succesvol in te loggen op het %suiteName% platform:',
    'error_invalid_attribute_value' => 'Fout - Attribuutwaarde niet toegestaan',
    'error_invalid_attribute_value_desc' => '%idpName% geeft een waarde door in het attribuut %attributeName% ("%attributeValue%") die niet is toegestaan voor deze %organisationNoun%. Inloggen is daarom niet mogelijk. Alleen %idpName% kan dit oplossen. Neem dus contact op met de helpdesk van %idpName%.',
    'error_invalid_attribute_value_desc_no_idp_name' => 'Je %organisationNoun% geeft een waarde door in het attribuut %attributeName% ("%attributeValue%") die niet is toegestaan voor deze %organisationNoun%. Inloggen is daarom niet mogelijk. Alleen jouw %organisationNoun% kan dit oplossen. Neem dus contact op met de helpdesk van je eigen %organisationNoun%.',
    'error_received_error_status_code'     => 'Error - Fout bij Identity Provider',
    'error_received_error_status_code_desc'=> 'Je %organisationNoun% heeft je de toegang geweigerd tot deze dienst. Je zult dus contact moeten opnemen met de (IT-)helpdesk van je eigen %organisationNoun% om te kijken of dit verholpen kan worden.',
    'error_received_invalid_response'        => 'Fout - Ongeldig SAML-bericht van %idpName%',
    'error_received_invalid_response_no_idp_name'        => 'Fout - Ongeldig SAML-bericht van %organisationNoun%',
    'error_received_invalid_signed_response' => 'Fout - Ongeldige handtekening op antwoord van %idpName%',
    'error_received_invalid_signed_response_no_idp_name' => 'Fout - Ongeldige handtekening op antwoord van %organisationNoun%',
    'error_stuck_in_authentication_loop' => 'Fout - Je zit vast in een zwart gat',
    'error_stuck_in_authentication_loop_desc' => 'Je bent succesvol ingelogd bij %idpName% maar %spName% stuurt je weer terug naar %suiteName%. Omdat je succesvol bent ingelogd, stuurt %suiteName% je weer naar %spName%, wat resulteert in een oneindig zwart gat. Dit komt waarschijnlijk door een foutje aan de kant van %spName%.',
    'error_stuck_in_authentication_loop_desc_no_idp_name' => 'Je bent succesvol ingelogd bij je %organisationNoun% maar %spName% stuurt je weer terug naar %suiteName%. Omdat je succesvol bent ingelogd, stuurt %suiteName% je weer naar %spName%, wat resulteert in een oneindig zwart gat. Dit komt waarschijnlijk door een foutje aan de kant van %spName%.',
    'error_stuck_in_authentication_loop_desc_no_sp_name' => 'Je bent succesvol ingelogd bij %idpName% maar de dienst waar je naartoe wilt stuurt je weer terug naar %suiteName%. Omdat je succesvol bent ingelogd, stuurt %suiteName% je weer naar de dienst, wat resulteert in een oneindig zwart gat. Dit komt waarschijnlijk door een foutje aan de kant van de dienst.',
    'error_stuck_in_authentication_loop_desc_no_name' => 'Je bent succesvol ingelogd bij je %organisationNoun% maar de dienst waar je naartoe wilt stuurt je weer terug naar %suiteName%. Omdat je succesvol bent ingelogd, stuurt %suiteName% je weer naar de dienst, wat resulteert in een oneindig zwart gat. Dit komt waarschijnlijk door een foutje aan de kant van de dienst.',
    'error_authentication_limit_exceeded' => 'Fout - teveel onafgeronde authenticaties tegelijkertijd.',
    'error_authentication_limit_exceeded_desc' => 'Teveel onafgeronde authenticaties tegelijkertijd.',
    'error_no_authentication_request_received' => 'Fout - Geen authenticatie-aanvraag ontvangen.',
    'error_authn_context_class_ref_blacklisted' => 'Fout - Waarde van AuthnContextClassRef is niet toegestaan',
    'error_authn_context_class_ref_blacklisted_desc' => 'Je kunt niet inloggen omdat %idpName% een waarde stuurde voor AuthnContextClassRef die niet is toegestaan. Neem contact op met de helpdesk van %idpName% om dit op te lossen.',
    'error_authn_context_class_ref_blacklisted_desc_no_idp_name' => 'Je kunt niet inloggen omdat je %organisationNoun% een waarde stuurde voor AuthnContextClassRef die niet is toegestaan. Neem contact op met de helpdesk van je %organisationNoun% om dit op te lossen.',
    'error_invalid_mfa_authn_context_class_ref' => 'Fout - Multi-factor authenticatie mislukt',
    'error_invalid_mfa_authn_context_class_ref_desc' => '%idpName% vereist multi-factor authenticatie voor deze dienst. Je tweede factor kon echter niet gevalideerd worden. Neem contact op met de helpdesk van %idpName% om dit op te lossen.',
    'error_invalid_mfa_authn_context_class_ref_desc_no_idp_name' => 'Jouw %organisationNoun% vereist multi-factor authenticatie voor deze dienst. Je tweede factor kon echter niet gevalideerd worden. Neem contact op met de helpdesk van je %organisationNoun% om dit op te lossen.',

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
    'allowed_scopes'                                => 'Toegestane scopes',

    'error_unknown_requesterid_in_authnrequest'         => 'Error - Deze dienst is niet geregistreerd bij %suiteName%.',
    'error_unknown_requesterid_in_authnrequest_desc'    => 'Deze dienst is niet bekend.',
    'error_clock_issue_title' => 'Fout - De Assertion is nog niet geldig of is verlopen',
    'error_clock_issue_desc' => 'Dit komt waarschijnlijk doordat de tijd tussen %idpName% en %suiteName% te ver uiteen loopt. Controleer de tijd op de %organisationNoun%.',
    'error_clock_issue_desc_no_idp_name' => 'Dit komt waarschijnlijk doordat de tijd tussen de %organisationNoun% en %suiteName% te ver uiteen loopt. Controleer de tijd op de IdP.',
    'error_stepup_callout_unknown_title' => 'Fout - Onbekend sterke authenticatie probleem',
    'error_stepup_callout_unknown_desc' => 'Inloggen met sterke authenticatie is niet gelukt en we weten niet precies waarom. Probeer het eerst eens opnieuw door terug te gaan naar de dienst en opnieuw in te loggen. Lukt dit niet, neem dan contact op met de helpdesk van je %organisationNoun%.',
    'error_stepup_callout_unmet_loa_title' => 'Fout - Geen geschikt token gevonden',
    'error_stepup_callout_unmet_loa_desc' => 'Om toegang te krijgen tot deze dienst heb je een geregistreerd token nodig met een bepaald zekerheidsniveau. Je hebt nu ofwel geen token geregistreerd, of het zekerheidsniveau van het token dat je hebt geregistreerd is te laag. Volg de link hieronder voor meer informatie over het registratieproces.',
    'error_stepup_callout_unmet_loa_link_text' => 'Lees meer over het registratieproces.',
    'error_stepup_callout_unmet_loa_link_target' => 'https://support.surfconext.nl/stepup-noauthncontext-nl',
    'error_stepup_callout_user_cancelled_title' => 'Fout - Inloggen afgebroken',
    'error_stepup_callout_user_cancelled_desc' => 'Je hebt het inloggen afgebroken. Ga terug naar de dienst als je het opnieuw wilt proberen.',
    'error_metadata_entity_id_not_found' => 'Metadata kan niet gegenereerd worden',
    'error_metadata_entity_id_not_found_desc' => 'De volgende fout is opgetreden: %message%',
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
    'logout_information_link_text' => '',
    'logout_information_link_target' => '',

    // Error page wiki link in footer, keep empty to hide block in footer
    'error_feedback_wiki_links_feedback_unknown_error' => 'https://support.surfconext.nl/help-error-error-nl',
    'error_feedback_wiki_links_authentication_feedback_unable_to_receive_message' => '',
    'error_feedback_wiki_links_authentication_feedback_session_lost' => 'https://support.surfconext.nl/help-session-lost-nl',
    'error_feedback_wiki_links_authentication_feedback_session_not_started' => '',
    'error_feedback_wiki_links_authentication_feedback_unknown_identity_provider' => '',
    'error_feedback_wiki_links_authentication_feedback_no_idps' => '',
    'error_feedback_wiki_links_authentication_feedback_invalid_acs_location' => '',
    'error_feedback_wiki_links_authentication_feedback_unsupported_signature_method' => '',
    'error_feedback_wiki_links_authentication_feedback_unsupported_acs_location_uri_scheme' => '',
    'error_feedback_wiki_links_authentication_feedback_unknown_service_provider' => 'https://support.surfconext.nl/help-unknown-sp-nl',
    'error_feedback_wiki_links_authentication_feedback_missing_required_fields' => 'https://support.surfconext.nl/help-missing-fields-nl',
    'error_feedback_wiki_links_authentication_authn_context_class_ref_blacklisted' => '',
    'error_feedback_wiki_links_authentication_feedback_invalid_attribute_value' => 'https://support.surfconext.nl/help-scope-nl',
    'error_feedback_wiki_links_authentication_feedback_custom' => '',
    'error_feedback_wiki_links_authentication_feedback_invalid_acs_binding' => 'https://support.surfconext.nl/help-bindings',
    'error_feedback_wiki_links_authentication_feedback_received_error_status_code' => '',
    'error_feedback_wiki_links_authentication_feedback_signature_verification_failed' => 'https://support.surfconext.nl/help-bindings',
    'error_feedback_wiki_links_authentication_feedback_verification_failed' => 'https://support.surfconext.nl/help-prepare-idp',
    'error_feedback_wiki_links_authentication_feedback_unknown_requesterid_in_authnrequest' => '',
    'error_feedback_wiki_links_authentication_feedback_pep_violation' => 'https://support.surfconext.nl/help-pep-nl',
    'error_feedback_wiki_links_authentication_feedback_unknown_preselected_idp' => 'https://support.surfconext.nl/help-no-connection-nl',
    'error_feedback_wiki_links_authentication_feedback_stuck_in_authentication_loop' => 'https://support.surfconext.nl/help-loop-nl',
    'error_feedback_wiki_links_authentication_feedback_no_authentication_request_received' => '',
    'error_feedback_wiki_links_authentication_feedback_response_clock_issue' => 'https://support.surfconext.nl/help-ntp-nl',
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
