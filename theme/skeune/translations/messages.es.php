<?php

$overrides = [];
$overridesFile = __DIR__ . '/overrides.es.php';
if (file_exists($overridesFile)) {
    $overrides = require $overridesFile;
}

return $overrides + [
    // General
    'button_screenreader'        =>  ', botón',
    'button_expandable_screenreader' => ', botón, desplegable',
    'button_expanded_screenreader' => ', botón, desplegado',
    'required_screenreader'      =>  'Obligatorio',
    'search_screenreader'        =>  'Buscar',
    'send_request'  =>  'Enviar solicitud',
    'back'          =>  'Volver',
    'noscript'      =>  'Para que esta página funcione de forma óptima, necesitas tener JavaScript activado.',
    'language_switcher'     => 'Selector de idioma',

    // FOOTER
    'log_in_to'     => 'Selecciona una cuenta para iniciar sesión en %arg1%',
    'helpLink'       => 'https://support.surfconext.nl/wayf-en',
    'footer_navigation_screenreader'    => 'Navegación del pie de página',

    // Forms
    'form_general_error'    =>  'Se ha producido un error al enviar el formulario. Puede deberse a un fallo de la conexión a internet o a otro problema. Revisa los datos introducidos y vuelve a intentarlo dentro de un rato. Si el problema persiste, ponte en contacto con tu servicio de soporte.',
    'form_error_name'       =>  'Tu nombre debe tener al menos 2 caracteres',
    'form_error_email'      =>  'Esta dirección de correo electrónico no es válida',

    // REDIRECT
    'processing_waiting'    =>  'Esperando respuesta',

    // WAYF
    'wayf_nothing_found'        => 'No se ha encontrado nada',
    'wayf_apu'                  => 'Vuelve a intentarlo con otras palabras clave',
    'wayf_noscript_warning_intro'     => 'Sin JavaScript no podrás recordar las cuentas que has usado antes ni hacer búsquedas. Si quieres usar estas funciones, activa JavaScript.',
    'wayf_noscript_warning_end'     => 'Por supuesto, puedes iniciar sesión igualmente.',
    'wayf_delete_account_screenreader'       => 'Eliminar %idpTitle% de tus cuentas',
    'wayf_deleted_account_screenreader'      => ' se ha eliminado de tus cuentas',
    'wayf_remaining_idps_title_screenreader' => 'Inicia sesión con una cuenta de la lista de abajo',
    'wayf_select_account_screenreader'       => 'Selecciona una cuenta de la lista de abajo',
    'wayf_search_placeholder'   => 'Buscar...',
    'wayf_search_screenreader'          => 'Buscar una %organisationNoun%',
    'wayf_search_reset_screenreader'    => 'Borrar el campo de búsqueda',
    'wayf_search_results_screenreader' => '%orgNoun% en los resultados',
    'wayf_your_accounts'        => 'Tus cuentas',
    'wayf_add_account'          => 'Usar otra cuenta',
    'wayf_no_access'            => 'Lo sentimos, esta cuenta no tiene acceso',
    'wayf_no_access_account_screenreader'    => 'Sin acceso con esta cuenta',
    'wayf_no_access_helpdesk'   => 'Si quieres, puedes solicitar acceso a este servicio. Enviaremos la solicitud al servicio de soporte de tu %orgNoun%.',
    'wayf_no_access_helpdesk_not_connected'     =>  "Vuelve a la página anterior y haz clic en «%buttonText%».",
    'wayf_noaccess_title_screenreader'       => 'Solicitar acceso para esta cuenta',
    'wayf_noaccess_name'        => 'Tu nombre',
    'wayf_noaccess_email'       => 'Tu dirección de correo electrónico',
    'wayf_noaccess_motivation'  => 'Motivo',
    'wayf_noaccess_success'     => 'Se ha enviado tu solicitud de acceso.',
    'wayf_noaccess_request_access_screenreader'  => 'Abrir el formulario de solicitud de acceso',
    'wayf_noaccess_form_announcement_screenreader' => 'Hay campos obligatorios sin rellenar o rellenados incorrectamente.',
    'wayf_defaultIdp_start'     => 'Si tu %organisation_noun% no aparece en la lista,',
    'wayf_defaultIdp_linkText'  => 'puedes usar %defaultIdpName% como alternativa.',
    'wayf_remaining_idps_search_label' => 'O busca una institución neerlandesa en la lista',
    'wayf_idp_title_screenreader' => 'Iniciar sesión con ',
    'wayf_idp_title_noaccess_screenreader'  => 'Sin acceso con',

    // Consent
    'consent_h1_text'   => '¿Aceptas compartir tu información?',
    'consent_h1_text_informational'   => 'Revisa la información que se va a compartir.',
    'consent_privacy_header'    => '%target% recibirá',
    'consent_attributes_correction_text'    => '¿Hay algo incorrecto?',
    'consent_ok'    =>  'Sí, acepto',
    'consent_identifier_explanation'    => 'El identificador para este servicio lo genera %suite_name% y es distinto para cada servicio que usas a través de %suite_name%. Así, el servicio puede reconocerte como el mismo usuario cuando vuelvas, pero los servicios no pueden reconocerte entre sí como el mismo usuario.',
    'consent_provided_by'   => 'proporcionado por',
    'consent_tooltip_screenreader'  => '¿Por qué necesitamos tu %attr_name%?',
    'consent_nojs' => 'Algunas funciones de esta página necesitan JavaScript para poder usarse con el teclado. Si quieres usar el teclado, activa JavaScript en tu navegador.',
    'consent_disclaimer_privacy_statement' => '(ofrecido por %org%) necesita esta información para funcionar correctamente',
    'consent_disclaimer_privacy_read'    => 'consulta su',
    'consent_disclaimer_privacy_policy'  => 'política de privacidad',
    'consent_disclaimer_secure' => 'es la plataforma que usa tu %orgNoun% para enviar tu información de forma segura a %spName% (más información sobre',
    'consent_reject_text_skeune_header'    => "No quieres compartir tus datos con el servicio",
    'consent_reject_text_skeune_body'    => "El servicio en el que estás iniciando sesión necesita tus datos para funcionar correctamente. Si prefieres no compartirlos, no podrás usar este servicio. Si cierras el navegador o solo esta pestaña, evitarás que tu información se comparta con el servicio. Si más adelante cambias de opinión, vuelve a iniciar sesión en el servicio y esta pantalla aparecerá de nuevo.",
    'consent_nok_title'     => "No quieres compartir tus datos con el servicio",
    'consent_nok_text'      => "El servicio en el que estás iniciando sesión necesita tus datos para funcionar correctamente. Si prefieres no compartirlos, no podrás usar este servicio. Si cierras el navegador o solo esta pestaña, evitarás que tu información se comparta con el servicio. Si más adelante cambias de opinión, vuelve a iniciar sesión en el servicio y esta pantalla aparecerá de nuevo.",
    'consent_groupmembership_show_more'     => 'Mostrar más',
    'consent_groupmembership_show_less'     => 'Mostrar menos',
    'consent_warning_allowed_html'      => '<a><u><i><br><wbr><strong><em><blink><marquee>',
];
