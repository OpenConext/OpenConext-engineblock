<?php
return [
    // General
    'button'        =>  'Knop: ',
    'logo'          =>  'logo',
    'required'      =>  'Verplicht',
    'search'        =>  'Zoeken',
    'send_request'  =>  'Verzoek sturen',

    // Forms
    'form_general_error'    =>  'Er ging iets fout tijdens het insturen van uw formulier.  Dit kan een probleem zijn met uw internetverbinding of iets anders.  Controleer uw invoer en probeer het later opnieuw.  Mocht het probleem zich blijven voordoen, neem dan contact op met uw beheerder.',
    'form_error_name'       =>  'Your name needs to be at least 2 characters long',
    'form_error_email'      =>  'This is an invalid email address',

    // WAYF
    'wayf_nothing_found'        => 'Niets gevonden',
    'wayf_apu'                  => 'Probeert u het opnieuw met andere zoektermen',
    'wayf_noscript_warning'     => '<p>Om deze pagina optimaal te laten functioneren moet JavaScript aan staan.</p><p>Zonder JavaScript zal u geen vorig gebruikte accounts kunnen onthouden.  Indien u deze functionaliteit toch wenst te gebruiken, vragen wij u vriendelijk om JavaScript aan te zetten.</p><p><strong>U kan, vanzelfsprekend, nog steeds inloggen.</strong></p>',
    'wayf_no_access_account'    => 'Geen toegang met deze account',
    'wayf_delete_account'       => 'Verwijderen uit uw accounts',
    'wayf_remaining_idps_title' => 'Voeg een account toe uit de onderstaande lijst',
    'wayf_select_account'       => 'Selecteer een account uit de onderstaande lijst',
    'wayf_search_placeholder'   => 'Zoeken...',
    'wayf_search_aria'          => 'Zoek een identity provider',
    'wayf_your_accounts'        => 'Uw accounts',
    'wayf_add_account'          => 'Gebruik een andere account',
    'wayf_no_access_helpdesk'   => 'U kan toegang vragen tot deze dienst.  We sturen deze aanvraag door naar de helpdesk van uw instituut.',
    'wayf_no_access'            => 'Sorry, geen toegang voor deze account',
    'wayf_noaccess_name'        => 'Uw naam',
    'wayf_noaccess_email'       => 'Uw e-mailadres',
    'wayf_noaccess_motivation'  => 'Motivatie',
    'wayf_noaccess_success'     => 'Je aanvraag tot toegang werd verstuurd.',
    'wayf_defaultIdp'                => '<a href="%defaultIdpLink%" class="wayf__defaultIdpLink">%defaultIdpName% is beschikbaar als alternatief</a> indien uw %organisation_noun% niet in de lijst staat.',
    'wayf_idp_title_screenreader' => 'Inloggen met ',
    'log_in_to'                 => 'Selecteer een %organisationNoun% en login bij %arg1%',

    // Consent
    'consent_h1_text'   => 'Geeft u toestemming om uw informatie te delen?',
    'consent_h1_text_minimal'   => 'Deze informatie wordt gedeeld met %arg1%',
    'consent_privacy_header'    => '%target% ontvangt',
    'consent_attributes_correction_text'    => 'Foutieve informatie?',
    'consent_ok'    =>  'Doorgaan',
    'consent_identifier_explanation'    => 'De id voor deze dienst wordt gegenereerd door %suite_name% en is verschillend voor elke dienst waarvan u gebruikt maakt via %suite_name%. De dienst kan u daarom herkennen als dezelfde gebruiker wanneer u terugkeert.  Diensten kunnen u echter niet herkennen als dezelfde gebruiker wanneer zij gegevens uitwisselen.',
    'consent_provided_by'   => 'aangeboden door <strong>%organisationName%</strong>',
    'consent_tooltip_screenreader'  => 'Waarom hebben we deze informatie nodig?',
    'consent_nojs_skeune'   => 'Tooltips / modals op deze pagina hebben om te werken met het toetsenbord JavaScript nodig.  Indien u enkel een toetsenbord gebruikt, gelieve dan JavaScript in te schakelen voor deze functionaliteit.',
    'consent_disclaimer_privacy_nolink' => '<div><strong>%org%</strong> heeft deze informatie nodig om te kunnen werken.</div>'
    ,
    'consent_disclaimer_privacy'    => <<<'TXT'
<div><strong>%org%</strong> heeft deze informatie nodig om te kunnen werken (lees hun <a href="%privacy_url%" target="_blank">privacy beleid</a>).</div>
TXT
    ,
    'consent_disclaimer_secure' => <<<'TXT'
Deze data zal veilig verstuurd worden van uw instituut via <input type="checkbox" tabindex="-1" role="button" aria-hidden="true" class="modal visually-hidden" id="consent_disclaimer_about" name="consent_disclaimer_about"><br /><label class="modal" tabindex="0" for="consent_disclaimer_about"><span class="visually-hidden">%buttonText%</span>%suite_name%</label> %modal_about% met behulp van <input type="checkbox" tabindex="-1" role="button" aria-hidden="true" class="modal visually-hidden" id="consent_disclaimer_number" name="consent_disclaimer_number"><label class="modal" tabindex="0" for="consent_disclaimer_number"><span class="visually-hidden">%buttonText%</span>een unieke id voor deze dienst.</label>
TXT
    ,
    'consent_disclaimer_secure_onemodal'    => <<<'TXT'
Deze data zal veilig verstuurd worden van uw instituut via <input type="checkbox" tabindex="-1" role="button" aria-hidden="true" class="modal visually-hidden" id="consent_disclaimer_about" name="consent_disclaimer_about"><label class="modal" tabindex="0" for="consent_disclaimer_about"><span class="visually-hidden">%buttonText%</span>%suite_name%</label> %modal_about% met behulp van een unieke id voor deze dienst.
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
<h3>Inloggen met %suiteName%</h3>
<p>%suiteName% laat mensen toe om eenvoudig en veilig in te loggen bij verschillende cloud-diensten met hun eigen %accountNoun%. %suiteName% zorgt daarbij voor uw privacy door zo weinig mogelijk persoonlijke gegevens naar deze diensten te sturen.</p>
TXT
    ,

    // Consent slidein: Reject_skeune
    'consent_slidein_reject_text_skeune'    => <<<'TXT'
<h3>u wil geen gegevens delen met deze dienst</h3>
<p>De dienst waar u bij wil inloggen heeft deze gegevens nodig om te kunnen functioneren.  Indien u verkiest om uw data niet te delen, kan u de dienst niet gebruiken.  Door uw browser of door deze tab te sluiten verhinderd u dat uw informatie gedeeld wordt.  Mocht u later van gedacht veranderen, kan u opnieuw inloggen bij deze dienst en krijgt u dit scherm opnieuw te zien.</p>
TXT
    ,
    'consent_groupmembership_show_more'     => 'Toon meer',
    'consent_groupmembership_show_less'     => 'Toon minder',
];
