<?php

return array(
    'dutch'                 => 'Nederlands',

    //General
    'back'                  => 'terug',
    'attribute'             => 'Attribuut',
    'value'                 => 'Waarde',
    'post_data'             => 'POST Data',
    'processing'            => 'Verbinden met de dienst',
    'processing_waiting'    => 'Wachten op een reactie van de dienst.',
    'processing_long'       => 'Duurt het te lang?',
    'note'                  => 'Mededeling',
    'note_no_script'        => 'Jouw browser ondersteunt geen JavaScript. Je moet op de onderstaande knop drukken om door te gaan.',
    'go_back'               => '&lt;&lt; Ga terug',
    'authentication_urls'   => 'Authenticatie URLs',
    'timestamp'             => 'Timestamp',

     // Feedback
    'requestId'             => 'Uniek Request ID',
    'identityProvider'      => 'Identity Provider',
    'serviceProvider'       => 'Service Provider',
    'userAgent'             => 'User Agent',
    'ipAddress'             => 'IP-adres',
    'statusCode'            => 'Statuscode',
    'statusMessage'         => 'Statusbericht',

    //WAYF
    'idp_selection_title'       => 'Identity Provider Selectie - %s',
    'idp_selection_subheader'   => 'Login via je eigen instelling',
    'search'                    => 'Zoek een instelling...',
    'idp_selection_desc'        => 'Selecteer een instelling en login bij <i>%s</i>',
    'our_suggestion'            => 'Eerder gekozen:',
    'idps_with_access'          => 'Instellingen met toegang',
    'no_access'                 => 'Geen toegang.',
    'no_access_more_info'       => 'Geen toegang. &raquo;',
    'no_results'                => 'Geen resultaten gevonden.',
    'log_in_to'                 => 'Selecteer een instelling en login bij',
    'press_enter_to_select'     => 'Druk op enter om te kiezen',
    'loading_idps'              => 'Instellingen worden geladen...',
    'edit'                      => 'Bewerken',
    'done'                      => 'Klaar',
    'remove'                    => 'Verwijderen',
    'request_access'            => 'Toegang aanvragen',
    'no_idp_results'            => 'Je zoekterm heeft geen resultaten opgeleverd.',
    'no_idp_results_request_access' => 'Kun je je instelling niet vinden? &nbsp;<a href="#no-access" class="noaccess">Vraag toegang aan</a>&nbsp;of pas je zoekopdracht aan.',
    'return_to_sp'              => 'Keer terug naar Service Provider',

    //Footer
    'service_by'            => 'Deze dienst is verbonden via',
    'serviceprovider_link'  => '<a href="https://www.surfconext.nl/" target="_blank">SURFconext</a>',
    'terms_of_service_link' => '<a href="https://wiki.surfnet.nl/display/conextsupport/Terms+of+Service+%28NL%29" target="_blank">Gebruiksvoorwaarden</a>',
    'footer'                => '<a href="https://www.surfconext.nl/" target="_blank">SURFconext</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="https://wiki.surfnet.nl/display/conextsupport/Terms+of+Service+%28NL%29">Gebruiksvoorwaarden</a>',

    //Help
    'help'                  => 'Help',
    'help_header'           => 'Help',
    'help_description'      => '<p>Heb je vragen over dit scherm of de SURFconext dienstverlening, bekijk dan de antwoorden bij de FAQ hieronder.</p>

    <p>Staat je vraag er niet bij, of ben je niet tevreden met een antwoord? Bezoek dan <a href="https://wiki.surfnet.nl/display/conextsupport/">de SURFconext support pagina</a> of stuur een mail naar <a href="mailto:help@surfconext.nl">help@surfconext.nl</a></p>',

    'close_question'        =>      'Sluit',

    //Help questions
		// general help questions
    'question_surfconext'               =>      'Wat is SURFconext?',
    'answer_surfconext'                 =>      '<p>SURFconext is een verbindingsinfrastructuur die een aantal bouwstenen voor online samenwerking met elkaar verbindt. Die bouwstenen zijn services voor federatieve authenticatie, groepsbeheer, sociale netwerken en cloud applicaties van verschillende aanbieders. Met SURFconext is het mogelijk om met je eigen instellingsaccount toegang te krijgen tot diensten van verschillende aanbieders.</p>',
    'question_log_in'                   =>      'Hoe werkt inloggen via SURFconext?',
    'answer_log_in'                     =>      '<ul>
                            <li>Je selecteert in dit scherm je eigen instelling.</li>
                            <li>Je wordt doorgestuurd naar een inlogpagina van je eigen instelling.Daar log je in.</li>
                            <li>Je instelling geeft door aan SURFconext dat je succesvol bent ingelogd.</li>
                            <li>Je wordt doorgestuurd naar de dienst waarop je hebt ingelogd om deze te gaan gebruiken.</li>
                        </ul>',
    'question_security'                 =>      'Is de SURFconext infrastructuur veilig?',
    'answer_security'                   =>      '<p>Jouw instelling en SURFnet hechten veel belang aan de privacy van gebruikers.<br />
<br />
Persoonsgegevens worden alleen verstrekt aan een dienstaanbieder wanneer dat noodzakelijk is voor het gebruik van de dienst. Contractuele afspraken tussen jouw instelling, SURFnet en de dienstaanbieder waarborgen dat er zorgvuldig wordt omgegaan met jouw persoonsgegevens.<br />
<br />
Het privacybeleid voor deze dienstverlening is in detail beschreven en na te lezen op <a href="https://wiki.surfnet.nl/display/conextsupport/">de SURFconext support pagina</a>. Heb je vragen ten aanzien van het privacybeleid van SURFconext, mail deze dan naar de SURFconext helpdesk via <a href="mailto:help@surfconext.nl">help@surfconext.nl</a>.
</p>',

    	// consent help questions
    'question_consentscreen'           	=>      'Waarom dit scherm?',
    'answer_consentscreen'             	=>      '<p>Om toegang te krijgen tot deze dienst is het noodzakelijk dat een aantal persoonlijke gegevens wordt gedeeld met deze dienst.</p>',
    'question_consentinfo'           	=>      'Wat gebeurt er met mijn gegevens?',
    'answer_consentinfo'             	=>      '<p>Indien je akkoord gaat met het verstrekken van je gegevens aan de dienst dan zullen de getoonde gegevens met deze dienst gedeeld worden. De dienstverlener zal de gegevens gebruiken en mogelijk opslaan voor een goede werking van de dienst. Op dit scherm vind je tevens een link naar de gebruiksvoorwaarden van de dienst en SURFconext welke meer informatie geven over de omgang met persoonlijke gegevens.</p>',
    'question_consentno'           		=>      'Wat gebeurt er als ik mijn gegevens niet wil delen?',
    'answer_consentno'             		=>      '<p>Als je niet akkoord gaat met het delen van je gegevens kun je geen gebruik maken van de dienst. De getoonde gegevens zullen in dit geval niet met de dienst worden gedeeld.</p>',
    'question_consentagain'           	=>      'Ik heb eerder al toestemming gegeven voor het delen van mijn gegevens, waarom krijg ik deze vraag opnieuw?',
    'answer_consentagain'             	=>      '<p>Indien de gegevens die doorgegeven worden aan deze dienst zijn gewijzigd zal er nogmaals gevraagd worden of je akkoord gaat met het delen van jouw gegevens.</p>',

		// WAYF help questions
    'question_screen'                   =>      'Waarom dit scherm?',
    'answer_screen'                     =>      '<p>Je kunt met je instellingsaccount inloggen bij deze dienst. In dit scherm geef je aan via welke instelling je wilt inloggen.</p>',
    'question_institution_not_listed'   =>      'Ik zie mijn instelling er niet tussen staan, wat nu?',
    'answer_institution_not_listed'     =>      '<p>Staat jouw instelling niet in de lijst? Dan is jouw instelling waarschijnlijk nog niet aangesloten op SURFconext. Ga terug naar de pagina van de dienst; soms biedt een dienst ook alternatieve manieren om in te loggen.</p>',
    'question_institution_no_access'    =>      'Mijn instelling geeft geen toegang tot deze dienst, wat nu?',
    'answer_institution_no_access'      =>      '<p>Het kan zijn dat je instelling wel is aangesloten op SURFconext maar (nog) geen afspraken heeft gemaakt met de dienstaanbieder over het gebruik van deze dienst. Wij zullen je verzoek doorsturen naar de verantwoordelijke binnen jouw instelling die de toegang tot diensten organiseert. Wellicht is jouw verzoek voor je instelling aanleiding om alsnog afspraken met deze dienstaanbieder te maken.</p>',
    'question_asked_institution_access'  =>      'Ik heb toegang aangevraagd voor mijn instelling, maar mijn instelling geeft nog steeds geen toegang. Waarom niet?',
    'answer_asked_institution_access'    =>      '<p>Blijkbaar is jouw instelling nog niet tot een overeenkomst met de dienstaanbieder gekomen of, het gebruik van deze dienst is niet wenselijk binnen jouw instelling. SURFnet heeft geen controle over de snelheid waarmee je antwoord of toegang krijgt. Die verantwoordelijkheid en zeggenschap ligt bij de instelling.</p>',
    'question_cannot_select'            =>      'Ik kan in mijn browser mijn instelling niet selecteren, wat nu?',
    'answer_cannot_select'              =>      '<p>Het keuzescherm van SURFconext is te gebruiken in de meest gangbare browsers waaronder, Internet Explorer, Firefox, Chrome en Safari. Andere browsers worden mogelijk niet ondersteund. Verder moet je browser het gebruik van cookies en javascript toestaan.</p>',

    // Request Access Form
    'sorry'                 => 'Helaas,',
    'form_description'      => 'heeft geen toegang tot deze dienst. Wat nu?</h2>
            <p>Wil je toch graag toegang tot deze dienst, vul dan
      het onderstaande formulier in. Wij sturen je verzoek door naar de juiste persoon binnen jouw instelling.</p>',
    'request_access_instructions' => '<h2>Helaas, je hebt geen toegang tot de dienst die je zoekt. Wat nu?</h2>
                                <p>Wil je toch graag toegang tot deze dienst, vul dan het onderstaande formulier in.
                                   Wij sturen je verzoek door naar de juiste persoon binnen jouw instelling.</p>',
    'name'                  => 'Naam',
    'name_error'            => 'Vul je naam in',
    'email'                 => 'E-mail',
    'email_error'           => 'Vul je (correcte) e-mailadres in',
    'institution'           => 'Instelling',
    'institution_error'     => 'Vul jouw instelling in',
    'comment'               => 'Toelichting',
    'comment_error'         => 'Vul een toelichting in',
    'cancel'                => 'Annuleren',
    'send'                  => 'Verstuur',
    'close'                 => 'Sluiten',

    'send_confirm'          => 'Je verzoek is verzonden',
    'send_confirm_desc'     => '<p>SURFnet stuurt je verzoek door aan de juiste persoon binnen jouw instelling. Het is aan deze persoon om actie te ondernemen op basis van jouw verzoek. Het kan zijn dat er nog afspraken gemaakt moeten worden tussen jouw instelling en de dienstaanbieder.</p>

    <p>SURFnet faciliteert het doorsturen van je verzoek maar heeft geen controle over de snelheid waarmee je antwoord of toegang krijgt.</p>

    <p>Heb je vragen over je verzoek, neem dan contact op met <a href="mailto:help@surfconext.nl">help@surfconext.nl</a></p>',

    // Profile
    'profile_header'                    => 'SURFconext',
    'profile_subheader'                 => 'Overzicht van jouw SURFconext-profiel.',
    'profile_header_my_profile'         => 'Mijn Profiel',
    'profile_header_my_groups'          => 'Mijn Groepen',
    'profile_header_my_apps'            => 'Mijn Diensten',
    'profile_header_exit'               => 'Exit',
    'profile_header_surfteams'          => 'SURFconext Teams',
    'profile_header_auth_needed'        => 'Authenticatie vereist',
    'profile_header_leave_surfconext'   => 'Verlaat SURFconext',
    'profile_store_info'                => 'Van jouw instelling hebben wij de volgende gegevens ontvangen. Deze gegevens worden opgeslagen in (en gebruikt door) SURFconext. Een subset van deze gegevens wordt verstrekt aan diensten die je via SURFconext benadert.',

    'profile_group_membership_desc'     => 'Je bent lid van de volgende groepen.',
    'profile_no_groups'                 => 'Geen groepen',
    'profile_extra_groups_desc'         => 'Om jouw instellingsgroepen te zien moet je het gebruik hiervoor binnen SURFconext autoriseren.',
    'profile_leave_surfconext_desc'     => 'Je gebruikt SURFconext om met je instellingsaccount in te loggen op een of meerdere diensten. Je kan jouw SURFconext profiel verwijderen door op onderstaande knop te drukken.',
    'profile_leave_surfconext_link'     => 'Verwijder mijn profiel!',
    'profile_leave_surfconext_disclaim' => 'Let op:
                                            <ul>
                                                <li>Alleen de informatie die in SURFconext wordt opgeslagen zal worden verwijderd.</li>
                                                <li>Diensten waar je met SURFconext op bent ingelogd zullen niet worden ingelicht. Het is dus mogelijk dat jouw persoonlijke gegevens daar nog opgeslagen zijn.</li>
                                                <li>Na een nieuwe login via SURFconext zal er automatisch weer een profiel worden aangemaakt.</li>
                                             </ul>
                                             <br>Meer informatie over welke informatie er wordt opgeslagen door SURFconext kan je vinden op de <a href="https://wiki.surfnet.nl/display/conextsupport/Profile+page" target="_blank">SURFconext supportpagina\'s</a>.',
    'profile_leave_surfconext_link_add' => '(Sluit je browser na deze actie om de verwijderprocedure te voltooien.)',
    'profile_revoke_access'             => 'Trek toegang in',
    'profile_leave_surfconext_conf'     => 'Weet je zeker dat je jouw profiel wilt verwijderen? Je zult de browser moeten afsluiten om deze actie te voltooien',
    'profile_eula_link'                 => 'Gebruiksvoorwaarden',
    'profile_support_link'              => 'Help URL',
    'profile_mail_text'                 => 'SURFconext support kan je vragen om bovenstaande informatie te delen. Deze informatie kan hen helpen om de supportvraag te beantwoorden.',
    'profile_mail_attributes'           => 'Mail data naar help@surfconext.nl',
    'profile_mail_send_success'         => 'De mail met bovenstaande informatie is succesvol verstuurd.',
    'profile_helplink'                  => 'https://wiki.surfnet.nl/display/conextsupport/Profile+page',

    // Profile MyApps
    'profile_apps_connected_aps'        => 'Mijn diensten via SURFconext',
    'profile_apps_share'                => 'Je hebt toestemming gegeven om jouw informatie te delen met de volgende diensten:',
    'profile_apps_service_th'           => 'Dienst/Applicatie',
    'profile_apps_eula_th'              => 'EULA',
    'profile_apps_support_name_th'      => 'Support contact',
    'profile_apps_support_url_th'       => 'Support URL',
    'profile_apps_support_email_th'	    => 'Support email',
    'profile_apps_support_phone_th'     => 'Support telefoon',
    'profile_apps_consent_th'           => 'Consent groep informatie',
    'profile_revoke_consent'            => 'Consent intrekken',
    'profile_no_consent'                => 'Nog niet uitgedeeld',
    'profile_consent'                   => 'Consent gegeven',
    'profile_attribute_release'         => 'Aan deze Service Provider worden de volgende attributen vrijgegeven:',
    'profile_attribute_release_all'     => 'Deze dienst ontvangt alle attributen die door jouw instelling worden geleverd.',

    // Delete User
    'deleteuser_success_header'         => 'SURFconext exit procedure',
    'deleteuser_success_subheader'      => 'U bent bijna klaar...',
    'deleteuser_success_desc'           => '<strong>Belangrijk!</strong> Om de exit procedure succesvol af te ronden, moet je nu de browser afsluiten.',


    // Consent
    'external_link'                     => 'opent in een nieuw venster',
    'consent_header'                    => '%s verzoekt jouw informatie',
    'consent_subheader'                 => '%s verzoekt jouw informatie',
    'consent_intro'                     => '%s verzoekt deze informatie die %s voor jou heeft opgeslagen:',
    'consent_idp_provides'              => 'wilt de volgende informatie vrijgeven:',
    'consent_sp_is_provided'            => 'aan',
    'consent_terms_of_service'          => 'Deze informatie zal worden doorgegeven aan %s. Gebruiksvoorwaarden van %s en %s zijn van toepassing.',

    'consent_accept'                    => 'Ja, deel deze gegevens',
    'consent_decline'                   => 'Nee, ik wil geen gebruik maken van deze dienst',
    'consent_notice'                    => '(We zullen dit nogmaals vragen als jouw informatie wijzigt)',

    // New Consent
    'consent_header_info'               => 'Verzoek voor doorgeven van jouw informatie',
    'consent_sp_idp_info'               => 'Om met je instellingsaccount in te kunnen loggen op de dienst <strong class="service-provider">%1$s</strong> maakt <strong class="identity-provider">%2$s</strong> gebruik van SURFconext. Voor het functioneren van deze dienst is het noodzakelijk dat <strong class="identity-provider">%2$s</strong> een aantal gegevens via SURFconext deelt met deze dienst. Hiervoor is jouw toestemming nodig. Het gaat om de volgende gegevens:',
    'sp_terms_of_service'               => 'Bekijk de %s\'s <a href="%s" target="_blank">gebruiksvoorwaarden</a>',
    'name_id'                           => 'SURFconext gebruikers ID',

    // Error screens
    'error_404'                         => '404 - Pagina niet gevonden',
    'error_404_desc'                    => 'De pagina is niet gevonden.',
    'error_help_desc'                   => '<p>
        Bezoek <a href="https://support.surfconext.nl/">de support pagina</a> als dit je probleem niet oplost.
        Op deze pagina vind je meer informatie over de mogelijke oorzaken en hoe je contact kan opnemen met het supportteam.
    </p>',
    'error_no_consent'                  => 'Niet mogelijk om verder te gaan naar dienst',
    'error_no_consent_desc'             => 'Deze applicatie kan enkel worden gebruikt wanneer de vermelde informatie wordt gedeeld.<br /><br />

Als je deze applicatie wilt gebruiken moet je:<br />
<ul><li>de browser herstarten</li>
<li>opnieuw inloggen</li>
<li>jouw informatie delen</li></ul>',
    'error_no_idps'                     => 'Error - Geen instellingen gevonden',
    'error_no_idps_desc'                => '<p>
        De dienst die je probeert te benaderen (de &lsquo;Service Provider&rsquo;) is niet toegankelijk via de SURFconext infrastructuur.
        Ga alstublieft <a href="javascript:history.back();">terug</a> en neem contact op met de helpdesk van SURFconext via <a href="mailto:help@surfconext.nl">help@surfconext.nl</a>.
        <br /><br />
    </p>',
    'error_session_lost'                => 'Error - Sessie is verloren gegaan',
    'error_session_lost_desc'           => '<p>
        Jouw sessie is ergens verloren gegaan.<br />
        Waarschijnlijk mocht de cookie niet worden gezet door de strikte privacy configuratie van jouw browser?<br />
        Ga alstublieft terug en probeer het opnieuw.
        <br /><br />
    </p>',
    'error_no_message'                  => 'Error - Geen bericht ontvangen',
    'error_no_message_desc'             => 'We verwachtten een bericht, maar we hebben er geen ontvangen. Er is iets fout gegaan. Probeer het alstublieft opnieuw.',
    'error_invalid_acs_location'        => 'De opgegeven "Assertion Consumer Service" is onjuist of bestaat niet.',
    'error_invalid_acs_binding'        => 'Onjuist ACS Binding Type',
    'error_invalid_acs_binding_desc'        => 'Het opgegeven of geconfigureerde "Assertion Consumer Service" Binding Type is onjuist of bestaat niet.',
    'error_unknown_service_provider'              => 'Error - Kan geen metadata ophalen voor EntityID \'%s\'',
    'error_unknown_service_provider_desc'     => '<p>
        Er kon geen Service Provider worden gevonden met het opgegeven EntityID. Neem contact op met de SURFconext helpdesk op <a href="mailto:help@surfconext.nl">help@surfconext.nl</a>.
    </p>',

    'error_unknown_issuer'              => 'Error - Onbekende dienst',
    'error_unknown_issuer_desc'     => '<p>
        De dienst waarop je probeert in te loggen is niet bekend bij SURFconext. Mogelijk heeft jouw instelling de toegang tot deze dienst nooit aangevraagd. Neem contact op met de helpdesk van je instelling en geef daarbij de volgende informatie door:
    </p>',
    'error_vo_membership_required'      => 'Lidmaatschap van een Virtuele Organisatie vereist',
    'error_vo_membership_required_desc' => 'Je bent succesvol ingelogd bij jouw instelling, maar om gebruik te kunnen maken van deze dienst moet je ook lid zijn van een Virtuele Organisatie.',
    'error_generic'                     => 'Error - Foutmelding',
    'error_generic_desc'                => '<p>
        Het is niet mogelijk om in te loggen. Probeer het alstublieft opnieuw.
    </p>',
    'error_missing_required_fields'     => 'Error - Verplichte velden ontbreken',
    'error_missing_required_fields_desc'=> '<p>
        Jouw instelling geeft niet de benodigde informatie vrij. Daarom kun je deze applicatie niet gebruiken.
    </p>
    <p>
        Neem alstublieft contact op met jouw instelling. Geef hierbij de onderstaande informatie door.
    </p>
    <p>
        Omdat je instelling niet de juiste attributen aan SURFconext doorgeeft is het inloggen mislukt. De volgende attributen zijn vereist om succesvol in te loggen op het SURFconext platform:
        <ul>
            <li>UID</li>
            <li>schacHomeOrganization</li>
        </ul>
    </p>',
    'error_group_oauth'            =>  'Error - Groepautorisatie is mislukt',
    'error_group_oauth_desc'       => '<p>
        De extere groepprovider <b>%s</b> retourneerde een fout.<br />
        Neem contact op met de SURFconext helpdesk via <a href="mailto:help@surfconext.nl">help@surfconext.nl</a>.
        <br />
    </p>',

    'error_received_error_status_code'     => 'Error - Instellingsfout',
    'error_received_error_status_code_desc'=> '<p>
        Jouw instelling stuurde een authenticatie respons met een fout code.
    </p>',
    'error_received_invalid_response'        => 'Error - Ongeldig antwoord van instelling',
    'error_received_invalid_signed_response' => 'Error - Ongeldige handtekening op instellingsantwoord',
    'error_received_status_code_desc'=> '<p>
        Jouw instelling stuurde een ongeldig authenticatie respons terug.
    </p>',

    /**
     * %1 AttributeName
     * %2 Options
     * %3 (optional) Value
     * @url http://nl3.php.net/sprintf
     */
    'error_attribute_validator_type_uri'            => '\'%3$s\' is geen geldige URI',
    'error_attribute_validator_type_urn'            => '\'%3$s\' is geen geldige URN',
    'error_attribute_validator_type_url'            => '\'%3$s\' is geen geldige URL',
    'error_attribute_validator_type_hostname'       => '\'%3$s\' is geen geldige hostname',
    'error_attribute_validator_type_emailaddress'   => '\'%3$s\' is geen geldig emailadres',
    'error_attribute_validator_minlength'           => '\'%3$s\' is niet lang genoeg (minimaal %2$d karakters)',
    'error_attribute_validator_maxlength'           => '\'%3$s\' is te lang (maximaal %2$d karakters)',
    'error_attribute_validator_min'                 => '%1$s heeft minimaal %2$d waardes nodig (%3$d gegeven)',
    'error_attribute_validator_max'                 => '%1$s heeft maximaal %2$d waardes (%3$d gegeven)',
    'error_attribute_validator_regex'               => '\'%3$s\' voldoet niet aan de voorwaarden voor waardes van dit attribuut (%2$s)',
    'error_attribute_validator_not_in_definitions'  => '%1$s is niet bekend in het SURFconext schema',
    'error_attribute_validator_allowed'             => '\'%3$s\' is geen toegestane waarde voor dit attribuut',
    'error_attribute_validator_availability'        => '\'%3$s\' is a gereserveerde SchacHomeOrganization voor een andere Identity Provider',

    'attributes_validation_succeeded' => 'Authenticatie geslaagd',
    'attributes_validation_failed'    => 'Sommige attributen falen validatie',
    'attributes_data_mailed'          => 'De attribuutdata is gemaild',
    'idp_debugging_title'             => 'Toon verkregen response van Identity Provider',
    'retry'                           => 'Opnieuw',

    'attributes' => 'Attributen',
    'validation' => 'Validatie',
    'idp_debugging_mail_explain' => 'Indien gevraagd door SURFconext,
                                        gebruik de "Mail naar SURFconext" knop hieronder
                                        om de informatie op dit scherm naar SURFconext beheer te e-mailen.',
    'idp_debugging_mail_button' => 'Mail naar SURFconext',

    // Logout
    'logout' => 'uitloggen',
    'logout_description' => 'Deze applicatie maakt gebruik van centrale login. Hiermee is het mogelijk om met single sign on bij verschillende applicaties in te loggen. Om er 100% zeker van te zijn dat je uitgelogd bent, moet je de browser helemaal afsluiten.',
    'logout_information_link' => '<a href="https://wiki.surfnet.nl/display/conextsupport/Uitloggen+SURFconext">Meer informatie over veilig uitloggen</a>',

    // Internal
    'info_mail_link' => '<a href="support@surfconext.nl">support@surfconext.nl</a>',
);
