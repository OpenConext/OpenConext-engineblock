<?php

$overrides = [];
$overridesFile = __DIR__ . '/overrides.es.php';
if (file_exists($overridesFile)) {
    $overrides = require $overridesFile;
}

return $overrides + [
    // Values used in placeholders for other translations
    // %suiteName%: OpenConext (default), SURFconext, ACMEconext
    'suite_name' => 'OpenConext',

    // Example translation message:
    //     'Find an %organisationNoun%'
    //
    // Becomes:
    //     'Find an organisation' (default)
    // or: 'Find an institution' (when overridden)
    'organisation_noun' => 'organización',
    'organisation_noun_plural' => 'organizaciones',

    // Example translation message:
    //     'Use your %accountNoun%'
    //
    // Becomes:
    //     'Use your organisation account' (default)
    // or: 'Use your institutional account' (when overridden)
    'account_noun' => 'cuenta de la organización',

    // Email
    // %supportUrl% can be used as a placeholder for other translations
    'openconext_support_url' => 'https://example.org',
    'openconext_terms_of_use_url' => 'https://example.org',
    'name_id_support_url' => 'https://example.org',

    // General
    'wayf_title'            => 'Iniciar sesión con',
    'value'                 => 'Valor',
    'post_data'             => 'Datos POST',
    'processing'            => 'Conectando con el servicio',
    'processing_waiting'    => 'Esperando la respuesta del servicio.',
    'processing_long'       => 'Ten paciencia, puede tardar un poco...',
    'go_back'               => '&lt;&lt; Volver',
    'note'                  => 'Nota',
    'note_no_script'        => 'Como tu navegador no admite JavaScript, debes pulsar el botón de abajo para continuar.',
    'unknown_organization_name' => 'Desconocida',
    'site_notice'           => '<p>Este es un aviso de ejemplo que se muestra en todas las pantallas de selección de cuenta. <strong>Puedes cambiar este texto o desactivarlo en la configuración.</strong></p><p>Por ejemplo: el próximo sábado, entre las 8:00 y las 10:00, el servicio podría no estar disponible por tareas de mantenimiento.</p>',

        // Feedback
    'requestId'             => 'UR ID',
    'correlationId'         => 'CID',
    'identityProvider'      => 'IdP',
    'serviceProvider'       => 'SP',
    'serviceProviderName'   => 'Nombre del SP',
    'proxyServiceProvider'  => 'SP proxy',
    'ipAddress'             => 'IP',
    'statusCode'            => 'Código de estado',
    'artCode'               => 'EC',
    'datetime'              => 'Hora',
    'requestUrl'            => 'URL de la solicitud',
    'statusMessage'         => 'Mensaje de estado',
    'attributeName'         => 'Nombre del atributo',
    'attributeValue'        => 'Valor del atributo',

    // WAYF
    'our_suggestion'            => 'Elección anterior:',
    'edit'                      => 'editar',
    'done'                      => 'listo',
    'idps_with_access'          => '%organisationNounPlural% con acceso:',
    'idps_without_access'       => '%organisationNounPlural% sin acceso:',
    'loading_idps'              => 'Cargando %organisationNounPlural%...',
    'request_access'            => 'Solicitar acceso',
    'no_idp_results'            => 'Tu búsqueda no ha devuelto ningún resultado.',
    'no_idp_results_request_access' => '¿No encuentras tu %organisationNoun%? &nbsp;<a href="#no-access" class="noaccess">Solicita acceso</a>&nbsp;o prueba a ajustar la búsqueda.',
    'more_idp_results'          => 'Hay %arg1% resultados que no se muestran. Afina la búsqueda para ver resultados más concretos.',
    'return_to_sp'              => 'Volver al proveedor de servicios',

    // Help page
    'help'                  => 'Ayuda',
    'help_header'       => 'Ayuda',
    'help_page_content' => <<<HTML
<p>No hay contenido de ayuda disponible.</p>
HTML
    ,

    // Remove cookies
    'remember_choice'           => 'Recordar mi elección',
    'remember_choice_per_idp'   => 'Recordar mi elección para este servicio durante %duration%',
    'remember_choice_duration_days' => '%count% días',
    'remember_choice_duration_minutes' => '%count% minutos',
    'remember_choice_tooltip_screenreader' => '¿Por qué recordamos tu elección?',
    'remember_choice_per_idp_tooltip' => 'Si activas esta opción, durante los próximos %duration% no tendrás que elegir cómo quieres iniciar sesión en este servicio y se omitirá la pantalla de inicio de sesión. <strong>Ten en cuenta</strong>: ¿a veces inicias sesión con otra cuenta? En ese caso, desactiva esta opción. Puedes deshacer este ajuste más adelante en <a href="http://profile.surfconext.nl/">profile.surfconext.nl</a>.',
    'cookie_removal_header'     => 'Eliminar cookies',
    'cookie_remove_button'      => 'Eliminar',
    'cookie_remove_all_button'  => 'Eliminar todas',
    'cookie_removal_description' => 'A continuación encontrarás un resumen de tus cookies y la posibilidad de eliminarlas una a una o todas a la vez.',
    'cookie_removal_confirm'     => 'Se ha eliminado tu cookie.',
    'cookies_removal_confirm'    => 'Se han eliminado tus cookies.',

    // Footer
    'service_by'            => 'Este servicio está conectado a través de',
    'serviceprovider_link_text'  => '%suiteName%',
    'serviceprovider_link_target'  => 'https://openconext.org/',
    'terms_of_service_link_text' => 'Condiciones del servicio',
    'terms_of_service_link_target' => '#',

    // Form
    'request_access_instructions_head' => 'Lamentablemente, no tienes acceso al servicio que buscas. ¿Qué puedes hacer?',
    'request_access_instructions_text' => 'Si quieres acceder a este servicio, rellena el formulario de abajo.
                               Después reenviaremos tu solicitud a la persona responsable de la gestión
                               del catálogo de servicios de tu %organisationNoun%.',
    'name'                  => 'Nombre',
    'name_error'            => 'Introduce tu nombre',
    'email'                 => 'Correo electrónico',
    'email_error'           => 'Introduce una dirección de correo electrónico (correcta)',
    '%organisationNoun%'    => '%organisationNoun%',
    'institution_error'     => 'Introduce una %organisationNoun%',
    'comment'               => 'Comentario',
    'comment_error'         => 'Escribe un comentario',
    'cancel'                => 'Cancelar',
    'send'                  => 'Enviar',
    'close'                 => 'Cerrar',
    'required'              => 'Obligatorio',
    'send_confirm'          => 'Se ha enviado tu solicitud',
    'send_confirm_desc'     => 'Tu solicitud se ha reenviado a tu %organisationNoun%. El personal de TI de tu %organisationNoun% se encargará de tramitarla y de decidir sobre la disponibilidad de este servicio.',

    // Consent
    'consent_attributes_screenreader'         => 'sobre %orgName%',
    'consent_attributes_show_more'            => 'Mostrar más información',
    'consent_attributes_show_less'            => 'Mostrar menos información',
    'consent_no_attributes_text'              => 'Este servicio no necesita ninguna información de tu %organisationNoun%.',
    'consent_buttons_ok_informational'        => 'Continuar a %sp%',
    'consent_buttons_nok'                     => 'No, no estoy de acuerdo',
    'consent_buttons_nok_informational'       => 'Cancelar',
    'consent_explanation_title'               => 'Presta atención al usar este servicio',
    'consent_name_id_label'                   => 'Identificador',
    'consent_name_id_support_link'            => 'Explicación',
    'consent_name_id_value_tooltip'           => 'El identificador para este servicio lo genera %arg1% y es distinto para cada servicio que usas a través de %arg1%. Así, el servicio puede reconocerte como el mismo usuario cuando vuelvas, pero los servicios no pueden reconocerte entre sí como el mismo usuario.',
    'consent_slidein_details_email'           => 'Correo electrónico',
    'consent_slidein_details_phone'           => 'Teléfono',
    'consent_slidein_text_contact'            => 'Si tienes alguna pregunta sobre esta página, ponte en contacto con el servicio de soporte de tu %organisationNoun%. %suiteName% tiene los siguientes datos de contacto:',
    'consent_slidein_text_no_support'         => 'No hay datos de contacto disponibles.',

    // Consent slidein: Is the data shown incorrect?
    'consent_slidein_correction_title' => '¿Los datos mostrados son incorrectos?',
    'consent_slidein_correction_text_idp'  => '%suiteName% recibe la información directamente de tu %organisationNoun% y no la almacena. Si tu información es incorrecta, ponte en contacto con el servicio de soporte de tu %organisationNoun% para que la modifique.',
    'consent_slidein_correction_text_aa'  => '%suiteName% recibe la información directamente del proveedor de atributos y no la almacena. Si tu información es incorrecta, ponte en contacto directamente con el proveedor de atributos para que la corrija. Puedes pedir ayuda con esto al servicio de soporte de tu %organisationNoun%.',

    // Generic slide-in
    'slidein_read_more' => 'Más información',

    // Error screens
    'error_feedback_info_intro' => '¿Se repite este mensaje de error? Usa los recursos de abajo para obtener ayuda. Si te pones en contacto con el servicio de soporte, incluye los siguientes códigos:',
    'error_wiki-href' => 'https://nl.wikipedia.org/wiki/SURFnet',
    'error_wiki-link-text' => 'Wiki de %suiteName%',
    'error_wiki-link-text-short' => 'Wiki',
    'error_help-desk-href' => 'https://www.surf.nl/over-surf/dienstverlening-support-werkmaatschappijen',
    'error_help-desk-link-text' => 'Servicio de soporte',
    'error_help-desk-link-text-short' => 'Soporte',
    'error_return-sp-link-text' => 'Volver a %spName%',
    'error_return-sp-link-text-short' => 'Volver al servicio',

    'error_404'                         => '404 - Página no encontrada',
    'error_404_desc'                    => 'No se ha encontrado esta página.',
    'error_405'                         => 'Método HTTP no permitido',
    'error_405_desc'                    => 'El método HTTP «%requestMethod%» no está permitido para la ubicación «%uri%». Los métodos admitidos son: %allowedMethods%.',
    'error_help_desc'               => '',
    'error_no_idps'                 => 'Error - No se han encontrado %organisationNounPlural%',
    'error_no_idps_desc'            => 'No es posible iniciar sesión en %spName% a través de %suiteName%. No hay %organisationNounPlural% con conexión a %spName%.',
    'error_no_idps_desc_no_sp_name'            => 'No es posible iniciar sesión en este servicio a través de %suiteName%. No hay %organisationNounPlural% con conexión a este servicio.',
    'error_session_lost'            => 'Error - Se ha perdido tu sesión',
    'error_session_lost_desc'       => 'Para acceder al servicio se necesita una sesión activa, pero tu sesión ha caducado. ¿Quizá has tardado demasiado en iniciar sesión? Vuelve al servicio e inténtalo de nuevo. Si no funciona, cierra primero el navegador y vuelve a intentarlo.',
    'error_session_not_started'            => 'Error - No se ha encontrado ninguna sesión',
    'error_session_not_started_desc'       => 'Para acceder al servicio se necesita una sesión activa, pero no se ha encontrado ninguna. Tu navegador debe aceptar cookies. También es posible que el enlace que has usado para llegar al servicio sea incorrecto. Vuelve al servicio e inténtalo de nuevo. Si no funciona, prueba con otro navegador.',
    'error_unsolicited_response'      => 'Error - No se ha podido completar el inicio de sesión',
    'error_unsolicited_response_desc' => 'No se ha podido completar tu inicio de sesión porque la solicitud se inició de una forma no admitida. Tu proveedor de identidad te ha enviado directamente a esta aplicación (por ejemplo, mediante un marcador, un acceso directo de un portal o un enlace guardado) sin que antes iniciaras sesión desde esta aplicación, y esto no está admitido. Vuelve a empezar desde el servicio al que intentabas acceder e inicia sesión desde allí.',
    'error_authorization_policy_violation'            => 'Error - Acceso denegado',
    'error_authorization_policy_violation_desc'       => 'No puedes usar %spName% porque %idpName% limita el acceso a él (el «proveedor de servicios») mediante una política de autorización. Ponte en contacto con el servicio de soporte de %idpName% si crees que deberías tener acceso a %spName%.',
    'error_authorization_policy_violation_desc_no_idp_name'       => 'No puedes usar %spName% porque tu %organisationNoun% limita el acceso a él (el «proveedor de servicios») mediante una política de autorización. Ponte en contacto con el servicio de soporte de tu %organisationNoun% si crees que deberías tener acceso a %spName%.',
    'error_authorization_policy_violation_desc_no_sp_name'       => 'No puedes usar este servicio porque %idpName% limita el acceso a él (el «proveedor de servicios») mediante una política de autorización. Ponte en contacto con el servicio de soporte de %idpName% si crees que deberías tener acceso a este servicio.',
    'error_authorization_policy_violation_desc_no_name' => 'No puedes usar este servicio porque tu %organisationNoun% limita el acceso a él (el «proveedor de servicios») mediante una política de autorización. Ponte en contacto con el servicio de soporte de tu %organisationNoun% si crees que deberías tener acceso a este servicio.',
    'error_authorization_policy_violation_info'       => 'Mensaje de %idpName%: ',
    'error_authorization_policy_violation_info_no_idp_name'       => 'Mensaje de tu %organisationNoun%: ',
    'error_unable_to_receive_message'      => 'Error - No se ha recibido ningún mensaje',
    'error_unable_to_receive_message_desc' => 'Esperábamos un mensaje SAML, pero no lo hemos recibido. Algo ha fallado. Inténtalo de nuevo.',
    'error_invalid_acs_location'    => 'Error - Ubicación de ACS no válida',
    'error_invalid_acs_location_desc' => 'El «Assertion Consumer Service» indicado es desconocido o no es válido.',
    'error_invalid_acs_binding'     => 'Error - Tipo de binding de ACS no válido',
    'error_invalid_acs_binding_desc'     => 'El tipo de binding del «Assertion Consumer Service» proporcionado o configurado es desconocido o no es válido.',
    'error_unsupported_signature_method' => 'Error - Método de firma no admitido',
    'error_unsupported_signature_method_desc' => 'El método de firma %arg1% no está admitido; actualiza a RSA-SHA256 (http://www.w3.org/2001/04/xmldsig-more#rsa-sha256).',
    'error_unknown_keyid' => 'Error - ID de clave desconocido',
    'error_unknown_keyid_desc' => '%suiteName% no conoce el ID de clave solicitado. Es posible que el proveedor de servicios use metadatos obsoletos o tenga un error de configuración.',
    'error_unknown_preselected_idp' => 'Error - No se puede acceder a %spName% a través de tu %organisationNoun%',
    'error_unknown_preselected_idp_no_sp_name' => 'Error - No se puede acceder al servicio a través de tu %organisationNoun%',
    'error_unknown_preselected_idp_desc' => 'La %organisationNoun% con la que quieres iniciar sesión en %spName% no ha activado el acceso a este servicio. Esto significa que no puedes usar %spName% a través de %suiteName%. Ponte en contacto con el servicio de soporte de tu %organisationNoun% para solicitar acceso. Indica que se trata de %spName% y por qué necesitas acceder.',
    'error_unknown_preselected_idp_desc_no_sp_name' => 'La %organisationNoun% con la que quieres iniciar sesión en este servicio no ha activado el acceso a él. Esto significa que no puedes usar este servicio a través de %suiteName%. Ponte en contacto con el servicio de soporte de tu %organisationNoun% para solicitar acceso a este servicio. Indica de qué servicio se trata (el «SP») y por qué necesitas acceder.',
    'error_unknown_service_provider'            => 'Error - Servicio desconocido: %spName%',
    'error_unknown_service_provider_no_sp_name' => 'Error - Servicio desconocido',
    'error_unknown_service_provider_desc'     => 'Intentas iniciar sesión en %spName%, pero %suiteName% no conoce este servicio. Es posible que %idpName% nunca haya activado el acceso a %spName%. Si quieres usarlo, ponte en contacto con el servicio de soporte de %idpName%.',
    'error_unknown_service_provider_desc_no_sp_name' => '%suiteName% no conoce el servicio en el que intentas iniciar sesión. Es posible que %idpName% nunca haya activado el acceso a este servicio. Si quieres usarlo, ponte en contacto con el servicio de soporte de %idpName%.',
    'error_unknown_service_provider_desc_no_idp_name' => 'Intentas iniciar sesión en %spName%. Es posible que tu %organisationNoun% nunca haya activado el acceso a %spName%. Si quieres usarlo, ponte en contacto con el servicio de soporte de tu %organisationNoun%.',
    'error_unknown_service_provider_desc_no_names' => '%suiteName% no conoce el servicio en el que intentas iniciar sesión. Es posible que tu %organisationNoun% nunca haya activado el acceso a este servicio. Si quieres usarlo, ponte en contacto con el servicio de soporte de tu %organisationNoun%.',

    'error_unsupported_acs_location_scheme' => 'Error - Esquema de URI no admitido en la ubicación de ACS',

    'error_unknown_identity_provider'               => 'Error - Proveedor de identidad desconocido: %idpName%',
    'error_unknown_identity_provider_no_idp_name'   => 'Error - %organisationNoun% desconocida',
    'error_unknown_identity_provider_desc'     => 'Intentas iniciar sesión con %idpName%, que %suiteName% no conoce.',
    'error_unknown_identity_provider_desc_no_idp_name'     => 'Intentas iniciar sesión con una %organisationNoun% que %suiteName% no conoce.',
    'error_unknown_signing_key' => 'Error - Clave de firma desconocida',
    'error_unknown_signing_key_desc' => '%suiteName% no conoce la clave de firma utilizada. Posiblemente se trate de un error de configuración.',
    'error_generic'                     => 'Error - Se ha producido un error',
    'error_generic_desc'                => 'No se ha podido iniciar sesión y no sabemos exactamente por qué. Primero, vuelve a %spName% e intenta iniciar sesión de nuevo. Si no funciona, ponte en contacto con el servicio de soporte de %idpName%.',
    'error_generic_desc_no_sp_name' => 'No se ha podido iniciar sesión y no sabemos exactamente por qué. Primero, vuelve al servicio e intenta iniciar sesión de nuevo. Si no funciona, ponte en contacto con el servicio de soporte de %idpName%.',
    'error_generic_desc_no_idp_name' => 'No se ha podido iniciar sesión y no sabemos exactamente por qué. Primero, vuelve a %spName% e intenta iniciar sesión de nuevo. Si no funciona, ponte en contacto con el servicio de soporte de tu %organisationNoun%.',
    'error_generic_desc_no_names' => 'No se ha podido iniciar sesión y no sabemos exactamente por qué. Primero, vuelve al servicio e intenta iniciar sesión de nuevo. Si no funciona, ponte en contacto con el servicio de soporte de tu %organisationNoun%.',
    'error_missing_required_fields'     => 'Error - Faltan campos obligatorios',
    'error_missing_required_fields_desc'=> '%idpName% no proporciona la información obligatoria o lo hace en un formato no válido. Por eso no puedes usar %spName%. Ponte en contacto con el servicio de soporte de %idpName% e indícales que uno o varios de los siguientes atributos obligatorios no se están enviando correctamente a %suiteName%:',
    'error_missing_required_fields_desc_no_idp_name'=> 'Tu %organisationNoun% no proporciona la información obligatoria. Por eso no puedes usar %spName%. Ponte en contacto con tu %organisationNoun% e indícale que faltan uno o varios de los siguientes atributos obligatorios en %suiteName%:',
    'error_missing_required_fields_desc_no_sp_name'=> '%idpName% no proporciona la información obligatoria. Por eso no puedes usar este servicio. Ponte en contacto con el servicio de soporte de %idpName% e indícales que faltan uno o varios de los siguientes atributos obligatorios en %suiteName%:',
    'error_missing_required_fields_desc_no_name'=> '
Tu %organisationNoun% no proporciona la información obligatoria. Por eso no puedes usar este servicio. Ponte en contacto con tu %organisationNoun% e indícale que faltan uno o varios de los siguientes atributos obligatorios en %suiteName%:',
    'error_invalid_attribute_value' => 'Error - Valor de atributo no permitido',
    'error_invalid_attribute_value_desc' => '%idpName% envía para el atributo %attributeName% un valor («%attributeValue%») que no está permitido para esta %organisationNoun%. Por eso no puedes iniciar sesión. Solo %idpName% puede solucionarlo. Ponte en contacto con el servicio de soporte de %idpName% para resolver este problema.',
    'error_invalid_attribute_value_desc_no_idp_name' => 'Tu %organisationNoun% envía para el atributo %attributeName% un valor («%attributeValue%») que no está permitido para esta %organisationNoun%. Por eso no puedes iniciar sesión. Solo tu %organisationNoun% puede solucionarlo. Ponte en contacto con el servicio de soporte de tu propia %organisationNoun% para resolver este problema.',
    'error_received_error_status_code'     => 'Error - Error del proveedor de identidad',
    'error_received_error_status_code_desc'=> 'Tu %organisationNoun% te ha denegado el acceso a este servicio. Tendrás que ponerte en contacto con tu propio servicio de soporte (de TI) para ver si se puede solucionar.',
    'error_received_invalid_response'       => 'Error - Respuesta SAML no válida de %idpName%',
    'error_received_invalid_response_no_idp_name'       => 'Error - Respuesta SAML no válida de tu %organisationNoun%',
    'error_received_invalid_signed_response'=> 'Error - Firma no válida en la respuesta de %idpName%',
    'error_received_invalid_signed_response_no_idp_name'=> 'Error - Firma no válida en la respuesta de tu %organisationNoun%',
    'error_stuck_in_authentication_loop' => 'Error - Te has quedado atrapado en un agujero negro',
    'error_stuck_in_authentication_loop_desc' => 'Te has autenticado correctamente en %idpName%, pero %spName% te devuelve de nuevo a %suiteName%. Como ya has iniciado sesión, %suiteName% te envía otra vez a %spName%, y así te quedas atrapado en un agujero negro sin fin. Probablemente se deba a un error de %spName%.',
    'error_stuck_in_authentication_loop_desc_no_idp_name' => 'Te has autenticado correctamente en tu %organisationNoun%, pero %spName% te devuelve de nuevo a %suiteName%. Como ya has iniciado sesión, %suiteName% te envía otra vez a %spName%, y así te quedas atrapado en un agujero negro sin fin. Probablemente se deba a un error de %spName%.',
    'error_stuck_in_authentication_loop_desc_no_sp_name' => 'Te has autenticado correctamente en %idpName%, pero el servicio al que intentas acceder te devuelve de nuevo a %suiteName%. Como ya has iniciado sesión, %suiteName% te envía otra vez al servicio, y así te quedas atrapado en un agujero negro sin fin. Probablemente se deba a un error del proveedor de servicios.',
    'error_stuck_in_authentication_loop_desc_no_name' => 'Te has autenticado correctamente en tu %organisationNoun%, pero el servicio al que intentas acceder te devuelve de nuevo a %suiteName%. Como ya has iniciado sesión, %suiteName% te envía otra vez al servicio, y así te quedas atrapado en un agujero negro sin fin. Probablemente se deba a un error del proveedor de servicios.',
    'error_authentication_limit_exceeded' => 'Error - Demasiadas autenticaciones en curso',
    'error_authentication_limit_exceeded_desc' => 'Hay demasiadas autenticaciones en curso',
    'error_no_authentication_request_received' => 'Error - No se ha recibido ninguna solicitud de autenticación.',
    'error_bookmarked_page'      => 'Error - Esta página ya no existe',
    'error_bookmarked_page_desc' => 'La página que intentas visitar ya no existe. Probablemente la guardaste como marcador, pero, por desgracia, eso no funciona. Busca el enlace correcto a la aplicación en la que quieres iniciar sesión para poder acceder.',
    'error_authn_context_class_ref_blacklisted'                     => 'Error - Valor de AuthnContextClassRef no permitido',
    'error_authn_context_class_ref_blacklisted_desc'                => 'No puedes iniciar sesión porque %idpName% ha enviado un valor de AuthnContextClassRef que no está permitido. Ponte en contacto con el servicio de soporte de %idpName% para solucionarlo.',
    'error_authn_context_class_ref_blacklisted_desc_no_idp_name'                => 'No puedes iniciar sesión porque tu %organisationNoun% ha enviado un valor de AuthnContextClassRef que no está permitido. Ponte en contacto con el servicio de soporte de tu %organisationNoun% para solucionarlo.',
    'error_invalid_mfa_authn_context_class_ref' => 'Error - Ha fallado la autenticación multifactor',
    'error_invalid_mfa_authn_context_class_ref_desc' => '%idpName% exige autenticación multifactor para este servicio, pero no se ha podido validar tu segundo factor. Ponte en contacto con el servicio de soporte de %idpName% para solucionarlo.',
    'error_invalid_mfa_authn_context_class_ref_desc_no_idp_name' => 'Tu %organisationNoun% exige autenticación multifactor para este servicio, pero no se ha podido validar tu segundo factor. Ponte en contacto con el servicio de soporte de tu %organisationNoun% para solucionarlo.',
    /**
     * %1 AttributeName
     * %2 Options
     * %3 (optional) Value
     * @url http://nl3.php.net/sprintf
     */
    'error_attribute_validator_type_uri'            => '«%arg3%» no es un URI válido',
    'error_attribute_validator_type_urn'            => '«%arg3%» no es un URN válido',
    'error_attribute_validator_type_url'            => '«%arg3%» no es una URL válida',
    'error_attribute_validator_type_hostname'       => '«%arg3%» no es un nombre de host válido',
    'error_attribute_validator_type_emailaddress'   => '«%arg3%» no es una dirección de correo electrónico válida',
    'error_attribute_validator_minlength'           => '«%arg3%» es demasiado corto (el mínimo es de %arg2% caracteres)',
    'error_attribute_validator_maxlength'           => '«%arg3%» es demasiado largo (el máximo es de %arg2% caracteres)',
    'error_attribute_validator_min'                 => '%arg1% debe tener al menos %arg2% valores (se han proporcionado %arg3%)',
    'error_attribute_validator_max'                 => '%arg1% no puede tener más de %arg2% valores (se han proporcionado %arg3%)',
    'error_attribute_validator_regex'               => '«%arg3%» no coincide con el formato esperado para este atributo (%arg2%)',
    'error_attribute_validator_not_in_definitions'  => '%arg1% no figura en el esquema',
    'error_attribute_validator_allowed'             => '«%arg3%» no es un valor permitido para este atributo',
    'allowed_scopes'                                => 'Ámbitos permitidos',

    'error_unknown_requesterid_in_authnrequest'         => 'Error - Servicio desconocido',
    'error_unknown_requesterid_in_authnrequest_desc'    => 'No se ha encontrado el servicio solicitado.',
    'error_clock_issue_title' => 'Error - La aserción aún no es válida o ha caducado',
    'error_clock_issue_desc' => 'Probablemente se deba a que la diferencia horaria entre %idpName% y %suiteName% es demasiado grande. Comprueba que la hora de la %organisationNoun% sea correcta.',
    'error_clock_issue_desc_no_idp_name' => 'Probablemente se deba a que la diferencia horaria entre la %organisationNoun% y %suiteName% es demasiado grande. Comprueba que la hora del IdP sea correcta.',
    'error_stepup_callout_unknown' => 'Error - Fallo desconocido en la autenticación reforzada',
    'error_stepup_callout_unknown_desc' => 'No se ha podido iniciar sesión con autenticación reforzada y no sabemos exactamente por qué. Primero, vuelve al servicio e intenta iniciar sesión de nuevo. Si no funciona, ponte en contacto con el servicio de soporte de tu %organisationNoun%.',
    'error_stepup_callout_unmet_loa_title' => 'Error - No se ha encontrado ningún token adecuado',
    'error_stepup_callout_unmet_loa_desc' => 'Para acceder a este servicio se necesita un token registrado con un determinado nivel de seguridad. Ahora mismo no tienes ningún token registrado o el nivel de seguridad del token que registraste es demasiado bajo. Consulta el enlace de abajo para obtener más información sobre el proceso de registro.',
    'error_stepup_callout_unmet_loa_link_text' => 'Más información sobre el proceso de registro.',
    'error_stepup_callout_unmet_loa_link_target' => 'https://support.surfconext.nl/stepup-noauthncontext-en',
    'error_stepup_callout_user_cancelled' => 'Error - Inicio de sesión cancelado',
    'error_stepup_callout_user_cancelled_desc' => 'Has cancelado el proceso de inicio de sesión. Vuelve al servicio si quieres intentarlo de nuevo.',
    'error_metadata_entity_id_not_found' => 'No se pueden generar los metadatos',
    'error_metadata_entity_id_not_found_desc' => 'Se ha producido el siguiente error: %message%',
    'attributes_validation_succeeded' => 'Autenticación correcta',
    'attributes_validation_failed'    => 'Algunos atributos no han superado la validación',
    'attributes_data_mailed'          => 'Se han enviado por correo los datos de los atributos',
    'idp_debugging_title'             => 'Mostrar la respuesta del proveedor de identidad',
    'retry'                           => 'Reintentar',

    'attributes' => 'Atributos',
    'validation' => 'Validación',
    'idp_debugging_mail_explain' => 'Si %suiteName% te lo pide,
                                    usa el botón «Enviar a %suiteName%» de abajo
                                    para enviar por correo la información de esta pantalla.',
    'idp_debugging_mail_button' => 'Enviar a %suiteName%',

    // Logout
    'logout' => 'Cerrar sesión',
    'logout_description' => 'Esta aplicación usa un inicio de sesión centralizado, que ofrece inicio de sesión único (single sign-on) para varias aplicaciones. Para asegurarte de que el cierre de sesión es 100 % seguro, cierra el navegador por completo.',
    'logout_information_link_text' => '',
    'logout_information_link_target' => '',

    // Error page wiki link in footer, keep empty to hide block in footer
    'error_feedback_wiki_links_feedback_unknown_error' => 'https://support.surfconext.nl/help-error-error-en',
    'error_feedback_wiki_links_authentication_feedback_unable_to_receive_message' => '',
    'error_feedback_wiki_links_authentication_feedback_session_lost' => 'https://support.surfconext.nl/help-session-lost-en',
    'error_feedback_wiki_links_authentication_feedback_session_not_started' => '',
    'error_feedback_wiki_links_authentication_feedback_unknown_identity_provider' => '',
    'error_feedback_wiki_links_authentication_feedback_no_idps' => '',
    'error_feedback_wiki_links_authentication_feedback_invalid_acs_location' => '',
    'error_feedback_wiki_links_authentication_feedback_unsupported_signature_method' => '',
    'error_feedback_wiki_links_authentication_feedback_unsupported_acs_location_uri_scheme' => '',
    'error_feedback_wiki_links_authentication_feedback_unknown_service_provider' => 'https://support.surfconext.nl/help-unknown-sp-en',
    'error_feedback_wiki_links_authentication_feedback_missing_required_fields' => 'https://support.surfconext.nl/help-missing-fields-en',
    'error_feedback_wiki_links_authentication_authn_context_class_ref_blacklisted' => '',
    'error_feedback_wiki_links_authentication_feedback_invalid_attribute_value' => 'https://support.surfconext.nl/help-scope-en',
    'error_feedback_wiki_links_authentication_feedback_custom' => '',
    'error_feedback_wiki_links_authentication_feedback_invalid_acs_binding' => 'https://support.surfconext.nl/help-bindings',
    'error_feedback_wiki_links_authentication_feedback_received_error_status_code' => '',
    'error_feedback_wiki_links_authentication_feedback_signature_verification_failed' => 'https://support.surfconext.nl/help-bindings',
    'error_feedback_wiki_links_authentication_feedback_verification_failed' => 'https://support.surfconext.nl/help-prepare-idp',
    'error_feedback_wiki_links_authentication_feedback_unknown_requesterid_in_authnrequest' => '',
    'error_feedback_wiki_links_authentication_feedback_pep_violation' => 'https://support.surfconext.nl/help-pep-en',
    'error_feedback_wiki_links_authentication_feedback_unknown_preselected_idp' => 'https://support.surfconext.nl/help-no-connection-en',
    'error_feedback_wiki_links_authentication_feedback_stuck_in_authentication_loop' => 'https://support.surfconext.nl/help-loop-en',
    'error_feedback_wiki_links_authentication_feedback_no_authentication_request_received' => '',
    'error_feedback_wiki_links_authentication_feedback_response_clock_issue' => 'https://support.surfconext.nl/help-ntp-en',
    'error_feedback_wiki_links_authentication_feedback_stepup_callout_user_cancelled' => '',
    'error_feedback_wiki_links_authentication_feedback_stepup_callout_unmet_loa' => '',
    'error_feedback_wiki_links_authentication_feedback_stepup_callout_unknown' => '',
    'error_feedback_wiki_links_authentication_feedback_metadata_entity_not_found' => '',

    // Error page idp contact link in footer, keep empty to hide block in footer
    'error_feedback_idp_contact_label_small_feedback_unknown_error' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_unable_to_receive_message' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_feedback_session_lost' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_session_not_started' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_feedback_unknown_identity_provider' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_no_idps' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_feedback_invalid_acs_location' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_unsupported_signature_method' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_feedback_unsupported_acs_location_uri_scheme' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_unknown_service_provider' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_feedback_missing_required_fields' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_authn_context_class_ref_blacklisted' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_invalid_attribute_value' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_feedback_custom' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_invalid_acs_binding' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_received_error_status_code' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_signature_verification_failed' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_feedback_verification_failed' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_feedback_unknown_requesterid_in_authnrequest' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_pep_violation' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_feedback_unknown_preselected_idp' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_feedback_stuck_in_authentication_loop' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_feedback_no_authentication_request_received' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_response_clock_issue' => 'Correo',
    'error_feedback_idp_contact_label_small_authentication_feedback_stepup_callout_user_cancelled' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_stepup_callout_unmet_loa' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_stepup_callout_unknown' => '',
    'error_feedback_idp_contact_label_small_authentication_feedback_metadata_entity_not_found' => '',

    // Metadata
    'metadata_organization_name' => '%suiteName%',
    'metadata_organization_displayname' => '%suiteName%',
    'metadata_organization_url' => '%supportUrl%',
];
