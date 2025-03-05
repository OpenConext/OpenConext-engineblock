<?php

$overrides = [];
$overridesFile = __DIR__ . '/overrides.pt.php';
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
    'organisation_noun' => 'organização',
    'organisation_noun_plural' => 'organizações',

    // Example translation message:
    //     'use a sua %accountNoun%'
    //
    // Becomes:
    //     'Use your organisation account' (default)
    // or: 'Use your institutional account' (when overridden)
    'account_noun' => 'conta da organização',

    // Email
    // %supportUrl% can be used as a placeholder for other translations
    'openconext_support_url' => 'https://example.org',
    'openconext_terms_of_use_url' => 'https://example.org',
    'name_id_support_url' => 'https://example.org',

    // General
    'value'                 => 'Valor',
    'post_data'             => 'POST Data',
    'processing'            => 'A estabelecer ligação ao serviço',
    'processing_waiting'    => 'A aguardar uma resposta do serviço.',
    'processing_long'       => 'Aguarde um momento por favor, poderá demorar um pouco...',
    'go_back'               => '&lt;&lt; Volte atrás',
    'note'                  => 'Nota',
    'note_no_script'        => 'Visto que o seu browser não suporta JavaScript, deve pressionar no botão em baixo para prosseguir.',
    'unknown_organization_name' => 'Unknown',
    'site_notice'           => '<p>Frei João, frei João,<br/>Dormes-tu, dormes-tu ?<br/>Jà tocam os sinos,<br/>Jà tocam os sinos,<br/>Dling-dling dlong<br/>Dling-dling dlong.</p>',

    // Feedback
    'requestId'             => 'UR ID',
    'identityProvider'      => 'IdP',
    'serviceProvider'       => 'SP',
    'serviceProviderName'   => 'SP Name',
    'proxyServiceProvider'  => 'Proxy SP',
    'ipAddress'             => 'IP',
    'statusCode'            => 'Código de Estado',
    'artCode'               => 'EC',
    'statusMessage'         => 'Mensagem de Estado',
    'attributeName'         => 'Nome do Atributo',
    'attributeValue'        => 'Valor do Atributo',

    // WAYF
    'our_suggestion'            => 'Escolha anterior:',
    'edit'                      => 'editar',
    'done'                      => 'feito',
    'idps_with_access'          => 'Fornecedores de Identidade com acesso',
    'idps_without_access'       => 'Fornecedores de Identidade sem acesso',
    'loading_idps'              => 'A carregar Fornecedores de Identidade...',
    'request_access'            => 'Requisitar acesso',
    'no_idp_results'            => 'A sua pesquisa não devolveu qualquer resultado.',
    'no_idp_results_request_access' => 'Não é possivel encontrar a sua %organisationNoun%? &nbsp;<a href="#no-access" class="noaccess">Solicitar acesso</a>&nbsp;ou tente refazer a sua pesquisa.',
    'more_idp_results'          => '%arg1% resultados não mostrados. Redefina a sua pesquisa para mostrar resultados mais específicos.',
    'return_to_sp'              => 'Voltar ao Fornecedor de Identidade',

    // Help page
    'help_header'       => 'Ajuda',
    'help_page_content' => <<<HTML
<p>Não existe conteúdo de ajuda disponível.</p>
HTML
    ,

    // Remove cookies
    'remember_choice'           => 'Relembrar a minha escolha',
    'cookie_removal_header'     => 'Remover cookies',
    'cookie_remove_button'      => 'Remover',
    'cookie_remove_all_button'  => 'Remover todos',
    'cookie_removal_description' => 'Em baixo poderá encontrar uma visão geral dos seus cookies e a possibilidade de os remover individualmente ou todos de uma vez.',
    'cookie_removal_confirm'     => 'O seu cookie foi removido.',
    'cookies_removal_confirm'    => 'Os seus cookies foram removidos.',

    // Footer
    'service_by'            => 'Este serviço está ligado através da',
    'serviceprovider_link_text'  => '%suiteName%',
    'serviceprovider_link_target'  => 'https://openconext.org/',
    'terms_of_service_link_text' => 'Termos do Serviço',
    'terms_of_service_link_target' => '#',

    // Form
    'request_access_instructions_head' => 'Infelizmente, você não tem acesso ao serviço que seleccionou. O que pode fazer?',
    'request_access_instructions_text' => 'Se pretende ter acesso a este serviço, preencha o formulário em baixo.
                                   Em seguida, nós iremos encaminhar o seu pedido à pessoa responsável pela
                                   gestão dos portfolios de serviço da sua %organisationNoun%.',
    'name'                  => 'Nome',
    'name_error'            => 'Insira o seu nome',
    'email'                 => 'E-mail',
    'email_error'           => 'Insira o seu endereço de e-mail (correcto)',
    '%organisationNoun%'    => '%organisationNoun%',
    'institution_error'     => 'Insira uma %organisationNoun%',
    'comment'               => 'Comentário',
    'comment_error'         => 'Insira um comentário',
    'cancel'                => 'Cancelar',
    'send'                  => 'Enviar',
    'close'                 => 'Fechar',
    'required'              => 'Obrigatório',

    'send_confirm'          => 'O seu pedido foi enviado',
    'send_confirm_desc'     => 'A sua solicitação foi encaminha para a sua %organisationNoun%. As decisões para a disponibilidade deste serviço serão tomadas pela equipa de IT da sua %organisationNoun%.',

    // Consent page
    'consent_attributes_screenreader'         => 'about %orgName%',
    'consent_attributes_show_more'            => 'Mostrar mais informação',
    'consent_attributes_show_less'            => 'Mostrar menos informação',
    'consent_no_attributes_text'              => 'Este serviço não requer informações da sua instituição',
    'consent_buttons_ok_informational'        => 'Proceed to %sp%',
    'consent_buttons_nok'                     => 'Não, eu não concordo',
    'consent_buttons_nok_informational'       => 'Cancel',
    'consent_explanation_title'               => 'Preste atenção quando utiliza este serviço',
    'consent_name_id_label'                   => 'Identificador',
    'consent_name_id_support_link'            => 'Explicação',
    'consent_name_id_value_tooltip'           => 'O identificador para este serviço é gerado por %arg1% e difere entre cada serviço que você usa através da %arg1%. O serviço pode reconhecê-lo com o mesmo utilizador ao regressar ao serviço, contudo, os serviços não o podem reconhecer entre si pelo mesmo utilizador.',
    'consent_slidein_details_email'           => 'Email',
    'consent_slidein_details_phone'           => 'Telefone',
    'consent_slidein_text_contact'            => 'Se tem alguma questão sobre esta página, por favor entre en contacto com o serviço de apoio da sua %organisationNoun%. %suiteName% tem o seguinte contacto para informações:',
    'consent_slidein_text_no_support'         => 'Sem dados de contacto disponíveis.',

    // Consent slidein: Is the data shown incorrect?
    'consent_slidein_correction_title' => 'Os dados mostrados estão incorrectos?',
    'consent_slidein_correction_text_idp'  => '%suiteName% recebe a informação directamente da sua %organisationNoun% e não armazena a informação para si. Se a sua informação está incorrecta, entre em contacto com o suporte da sua %organisationNoun% para a alterar.',
    'consent_slidein_correction_text_aa'  => '%suiteName% recebe a informação directamente do fornecedor de atributos e não armazena a informação para si. Se a sua informação está incorrecta, entre em contacto com o fornecedor dos atributos directamente para que sejam corrigidas. Pode pedir ao suporte da sua %organisationNoun% que lhe dê assistência para esta situação.',

    // Generic slide-in
    'slidein_read_more' => 'Leia mais',

    // Error screens
    'error_feedback_info_intro' => 'Does this error message recur? Use the resources below to get help. When contacting the help desk, please include the following codes:',
    'error_wiki-href' => 'https://nl.wikipedia.org/wiki/SURFnet',
    'error_wiki-link-text' => '%suiteName% Wiki',
    'error_wiki-link-text-short' => 'Wiki',
    'error_help-desk-href' => 'https://www.surf.nl/over-surf/dienstverlening-support-werkmaatschappijen',
    'error_help-desk-link-text' => 'Helpdesk',
    'error_help-desk-link-text-short' => 'Helpdesk',
    'error_return-sp-link-text' => 'Voltar ao %spName%',
    'error_return-sp-link-text-short' => 'Voltar ao serviço',

    'error_404'                         => '404 - Página não encontrada',
    'error_404_desc'                    => 'Esta página não foi encontrada.',
    'error_405'                         => 'Método HTTP não permitido',
    'error_405_desc'                    => 'O método HTTP "%requestMethod%" não é permitido para o endereço "%uri%". Os métodos suportados são: %allowedMethods%.',
    'error_help_desc'               => '',
    'error_no_idps'                 => 'Erro - Não foi encontrado nenhum Fornecedor de Identidade',
    'error_no_idps_desc'            => 'O %spName% a que pretende ligar-se não está acessível através da %organisationNounPlural%.',
    'error_no_idps_desc_no_sp_name'            => 'O serviço ("Service Provider") a que pretende ligar-se não está acessível através da %organisationNounPlural%.',
    'error_session_lost'            => 'Erro - a sua sessão foi perdida',
    'error_session_lost_desc'       => '<p>Esta ação requer uma sessão ativa, no entanto, não conseguimos encontrar a sessão. Está a aguardar há muito tempo? Feche o browser e tente novamente, ou tente um browser diferente.</p>',
    'error_session_not_started'            => 'Erro - a sua sessão não foi encontrada',
    'error_session_not_started_desc'       => '<p>Esta ação requer uma sessão ativa, no entanto, não recebemos nenhum cookie de sessão. O browser deve aceitar cookies. Não utilize endereços do marcador ou link. Feche o browser e tente novamente, ou tente um browser diferente.</p>',
    'error_authorization_policy_violation'            => 'Erro - Sem acesso',
    'error_authorization_policy_violation_desc'       => 'Você autenticu-se com sucesso na %idpName%, mas infelizmente você não pode utilizar %spName% (o "Fornecedor de Serviço") porque não tem acesso. A %idpName% limita o acesso a %spName% com uma política de autorização. Entre em contacto com o suporte da %idpName% se acha que deve ser-lhe concedido acesso ao serviço.',
    'error_authorization_policy_violation_desc_no_idp_name'       => 'Você autenticu-se com sucesso na sua %organisationNoun%, mas infelizmente você não pode utilizar %spName% (o "Fornecedor de Serviço") porque não tem acesso. A sua %organisationNoun% limita o acesso a %spName% com uma política de autorização. Entre em contacto com o suporte da sua %organisationNoun% se acha que deve ser-lhe concedido acesso ao serviço.',
    'error_authorization_policy_violation_desc_no_sp_name'       => 'Você autenticu-se com sucesso na %idpName%, mas infelizmente você não pode utilizar este serviço (o "Fornecedor de Serviço") porque não tem acesso. A %idpName% limita o acesso a este serviço com uma política de autorização. Entre em contacto com o suporte da %idpName% se acha que deve ser-lhe concedido acesso ao serviço.',
    'error_authorization_policy_violation_desc_no_name'       => 'Você autenticu-se com sucesso na sua %organisationNoun%, mas infelizmente você não pode utilizar este serviço (o "Fornecedor de Serviço") porque não tem acesso. A sua %organisationNoun% limita o acesso a este serviço com uma política de autorização. Entre em contacto com o suporte da sua %organisationNoun% se acha que deve ser-lhe concedido acesso ao serviço.',
    'error_authorization_policy_violation_info'       => 'Mensagem da %idpName%: ',
    'error_authorization_policy_violation_info_no_idp_name'       => 'Mensagem da sua %organisationNoun%: ',
    'error_no_message'              => 'Erro - Não foi recebido nenhuma mensagem',
    'error_no_message_desc'         => 'Estávamos a aguardar uma mensagem, mas não chegou nenhuma? Alguma coisa correu mal. Tente de novo por favor.',
    'error_invalid_acs_location'    => 'O "Serviço de Consumidor de Asserção" fornecido é desconhecido ou inválido.',
    'error_invalid_acs_binding'     => 'O ACS "Binding Type" é inválido',
    'error_invalid_acs_binding_desc'     => 'O "Binding Type" do "Serviço de Consumidor de Asserção" fornecido ou configurado é desconhecido ou inválido.',
    'error_unsupported_signature_method' => 'O método de assinatura não é suportado',
    'error_unsupported_signature_method_desc' => 'O método de assinatura %arg1% não é suportado, faça upgrade para RSA-SHA256 (http://www.w3.org/2001/04/xmldsig-more#rsa-sha256).',
    'error_unknown_keyid' => 'Error - unknown key id',
    'error_unknown_keyid_desc' => 'The requested key-ID is not known to %suiteName%. Perhaps the service provider is using outdated metadata or has a configuration error.',
    'error_unknown_preselected_idp' => 'Erro - Não há ligação entre %organisationNoun% e o serviço',
    'error_unknown_preselected_idp_no_sp_name' => 'Erro - Não há ligação entre %organisationNoun% e o serviço',
    'error_unknown_preselected_idp_desc' => 'A %organisationNoun% que pretende utilizar para se autenticar e aceder ao serviço, não tem activado o acesso a este serviço. Isto significa que não pode aceder a este serviço através da %suiteName%. Entre em contacto com com o suporte da sua %organisationNoun% para solicitar o acesso a este serviço. Declare que serviço se trata (o "Fornecedor de Serviço") e porque necessita do acesso.',
    'error_unknown_preselected_idp_desc_no_sp_name' => 'A %organisationNoun% que pretende utilizar para se autenticar e aceder ao serviço, não tem activado o acesso a este serviço. Isto significa que não pode aceder a este serviço através da %suiteName%. Entre em contacto com com o suporte da sua %organisationNoun% para solicitar o acesso a este serviço. Declare que serviço se trata (o "Fornecedor de Serviço") e porque necessita do acesso.',
    'error_unknown_service_provider'          => 'Erro - Serviço desconhecido',
    'error_unknown_service_provider_desc'     => 'O serviço a que pretende autenticar-se é desconhecido para a %suiteName%. Possivelmente a sua %organisationNoun% nunca permitiu o acesso a este serviço. Entre em contacto com o suporte da sua %organisationNoun% e fornecer-lhes as seguintes informações:',

    'error_unsupported_acs_location_scheme' => 'Erro - URI scheme não suportado na localização ACS',

    'error_unknown_identity_provider'          => 'Erro - %organisationNoun% desconhecido',
    'error_unknown_identity_provider_no_idp_name'   => 'Erro - %organisationNoun% desconhecido',
    'error_unknown_identity_provider_desc'     => 'O %organisationNoun% a que pretende autenticar-se é desconhecido para a %suiteName%.',
    'error_unknown_identity_provider_desc_no_idp_name'     => 'O %organisationNoun% a que pretende autenticar-se é desconhecido para a %suiteName%.',
    'error_unknown_signing_key' => 'Erro - chave de assinatura desconhecida',
    'error_unknown_signing_key_desc' => 'A chave de assinatura utilizada não é conhecida por %suiteName%. Isto pode ser um erro de configuração.',
    'error_generic'                     => 'Erro - Ocorreu um erro',
    'error_generic_desc'                => 'A sua autenticação falhou e não sabemos exactamente porquê. Tente de novo e no caso de voltar a não funcionar, entre em contacto com o suporte da %idpName% para pedir ajuda.',
    'error_missing_required_fields'     => 'Erro - Campo necessário em falta',
    'error_missing_required_fields_desc'=> 'Não pode usar esta aplicação porque a %idpName% não está a fornecer a informação necessária. Entre em contacto com a %idpName% com a informação indicada em baixo. A autenticação falhou porque o Fornecedor de Identidade da %idpName% não forneceu %suiteName% com um ou mais dos seguintes atributos obrigatórios:',
    'error_missing_required_fields_desc_no_idp_name'=> 'Não pode usar esta aplicação porque a sua %organisationNoun% não está a fornecer a informação necessária. Entre em contacto com a sua %organisationNoun% com a informação indicada em baixo. A autenticação falhou porque o Fornecedor de Identidade da sua %organisationNoun% não forneceu %suiteName% com um ou mais dos seguintes atributos obrigatórios:',
    'error_missing_required_fields_desc_no_sp_name'=> 'Não pode usar esta aplicação porque a %idpName% não está a fornecer a informação necessária. Entre em contacto com a %idpName% com a informação indicada em baixo. A autenticação falhou porque o Fornecedor de Identidade da %idpName% não forneceu %suiteName% com um ou mais dos seguintes atributos obrigatórios:',
    'error_missing_required_fields_desc_no_name'=> 'Não pode usar esta aplicação porque a sua %organisationNoun% não está a fornecer a informação necessária. Entre em contacto com a sua %organisationNoun% com a informação indicada em baixo. A autenticação falhou porque o Fornecedor de Identidade da sua %organisationNoun% não forneceu %suiteName% com um ou mais dos seguintes atributos obrigatórios:',
    'error_invalid_attribute_value' => 'Valor do atributo não permitido',
    'error_invalid_attribute_value_desc' => 'A %idpName% utilizou um valor para o atributo %attributeName% ("%attributeValue%") o que não é permitido para esta %organisationNoun%. Desta forma, não pode autenticar-se. Apenas a %idpName% pode resolver esta situação. Entre em contacto com o suporte deste serviço da %idpName%.',
    'error_invalid_attribute_value_desc_no_idp_name' => 'A sua %organisationNoun% utilizou um valor para o atributo %attributeName% ("%attributeValue%") o que não é permitido para esta %organisationNoun%. Desta forma, não pode autenticar-se. Apenas a sua %organisationNoun% pode resolver esta situação. Entre em contacto com o suporte deste serviço da sua %organisationNoun%.',
    'error_received_error_status_code'     => 'Erro - Erro no Fornecedor de Identidade',
    'error_received_error_status_code_desc'=> '<p>
A sua %organisationNoun% negou-lhe acesso a este serviço. Terá de entrar em contacto com o suporte (IT) para ver se é possível corrigir a situação.
    </p>',
    'error_received_invalid_response'       => 'Erro - Resposta inválida do %idpName%',
    'error_received_invalid_response_no_idp_name'       => 'Erro - Resposta inválida do Fornecedor de Identidade',
    'error_received_invalid_signed_response'=> 'Erro - resposta de assinatura inválida do %idpName%',
    'error_received_invalid_signed_response'=> 'Erro - resposta de assinatura inválida do %organisationNoun%',
    'error_stuck_in_authentication_loop' => 'Erro - Ficou preso(a) no vazio',
    'error_stuck_in_authentication_loop_desc' => 'Autenticou-se com sucesso no seu %idpName%, mas o %spName% reencaminhou-o de volta para %suiteName%. Como já está autenticado, o %suiteName% o reencaminha de volta para o %spName%, o que resulta num ciclo infinito. Muito provavelmente, isto é provocado por um erro no %spName%.',
    'error_stuck_in_authentication_loop_desc_no_idp_name' => 'Autenticou-se com sucesso no seu Fornecedor de Identidade, mas o %spName% reencaminhou-o de volta para %suiteName%. Como já está autenticado, o %suiteName% o reencaminha de volta para o %spName%, o que resulta num ciclo infinito. Muito provavelmente, isto é provocado por um erro no %spName%.',
    'error_stuck_in_authentication_loop_desc_no_sp_name' => 'Autenticou-se com sucesso no seu %idpName%, mas o serviço reencaminhou-o de volta para %suiteName%. Como já está autenticado, o %suiteName% o reencaminha de volta para o serviço, o que resulta num ciclo infinito. Muito provavelmente, isto é provocado por um erro no Fornecedor de Serviço.',
    'error_stuck_in_authentication_loop_desc_no_name' => 'Autenticou-se com sucesso no seu Fornecedor de Identidade, mas o serviço ao qual está a tentar aceder reencaminhou-o de volta para %suiteName%. Como já está autenticado, o %suiteName% o reencaminha de volta para o serviço, o que resulta num ciclo infinito. Muito provavelmente, isto é provocado por um erro no Fornecedor de Serviço.',
    'error_authentication_limit_exceeded' => 'Error - too many authentications in progress',
    'error_authentication_limit_exceeded_desc' => 'Too many authentications in progress',
    'error_authn_context_class_ref_blacklisted'                     => 'Erro - O valor para AuthnContextClassRef não é permitido',
    'error_authn_context_class_ref_blacklisted_desc'                => '<p>Não pode autenticar-se porque a %idpName% enviou um valor para AuthnContextClassRef que não é permitido.</p>',
    'error_authn_context_class_ref_blacklisted_desc_no_idp_name'                => '<p>Não pode autenticar-se porque a sua %organisationNoun% enviou um valor para AuthnContextClassRef que não é permitido.</p>',
    'error_invalid_mfa_authn_context_class_ref' => 'Erro - falha na autenticação segundo fator de autenticação (2FA)',
    'error_invalid_mfa_authn_context_class_ref_desc' => '<p>A %idpName% requer segurança adicional para este serviço, por meio de um segundo fator de autenticação (2FA). No entanto, o seu segundo fator de autenticação não pôde ser verificado. Entre em contato com o suporte da %idpName% para validar esta situação.</p>',
    'error_invalid_mfa_authn_context_class_ref_desc_no_idp_name' => '<p>A sua %organisationNoun% requer segurança adicional para este serviço, por meio de um segundo fator de autenticação (2FA). No entanto, o seu segundo fator de autenticação não pôde ser verificado. Entre em contato com o suporte da sua %organisationNoun% para validar esta situação.</p>',
    'error_no_authentication_request_received' => 'Não foi recebida nenhuma solicitação de autenticação.',
    /**
     * %1 AttributeName
     * %2 Options
     * %3 (optional) Value
     * @url http://nl3.php.net/sprintf
     */
    'error_attribute_validator_type_uri'            => '\'%arg3%\' não é um URI válido',
    'error_attribute_validator_type_urn'            => '\'%arg3%\' não é um URN válido',
    'error_attribute_validator_type_url'            => '\'%arg3%\' não é um URL válido',
    'error_attribute_validator_type_hostname'       => '\'%arg3%\' não é um hostname válido',
    'error_attribute_validator_type_emailaddress'   => '\'%arg3%\' não é um endereço de email válido',
    'error_attribute_validator_minlength'           => '\'%arg3%\' demasiado curto (mínimo de %arg2% caracteres)',
    'error_attribute_validator_maxlength'           => '\'%arg3%\' demasiado longo (máximo de %arg2% caracteres)',
    'error_attribute_validator_min'                 => '%arg1% deve ter pelo menos %arg2% valores (%arg3% dados)',
    'error_attribute_validator_max'                 => '%arg1% não deve ter mais que %arg2% valores (%arg3% dados)',
    'error_attribute_validator_regex'               => '\'%arg3%\' não corresponde a formato esperado deste atributo (%arg2%)',
    'error_attribute_validator_not_in_definitions'  => '%arg1% não é conhecido no schema',
    'error_attribute_validator_allowed'             => '\'%arg3%\' não é um valor permitido para este atributo',
    'error_attribute_validator_availability'        => '\'%arg3%\' é um schacHomeOrganization reservado para outro Fornecedor de Identidade',
    'allowed_scopes'                                => 'Allowed scopes',

    'error_unknown_requesterid_in_authnrequest'         => 'Erro - Serviço desconhecido',
    'error_unknown_requesterid_in_authnrequest_desc'    => '<p>O serviço solicitado não foi encontrado.</p>',
    'error_clock_issue_title' => 'Erro - A asserção ainda não é válida ou pode ter expirado',
    'error_clock_issue_desc' => '<p>Por favor, verifique se a hora no IdP está correta.</p>',
    'error_clock_issue_desc_no_idp_name' => '<p>Por favor, verifique se a hora no IdP está correta.</p>',
    'error_stepup_callout_unknown_title' => 'Erro - falha por autenticação forte desconhecida',
    'error_stepup_callout_unknown_desc' => 'O login com autenticação forte falhou e não sabemos exatamente qual o motivo. Tente aceder de novo ao serviço e efetuar uma nova autenticação. Se voltar a não funcionar, entre em contato com o suporte técnico da sua %organisationNoun%.',
    'error_stepup_callout_unmet_loa_title' => 'Erro - não foi encontrado nenhum token adequado',
    'error_stepup_callout_unmet_loa_desc' => 'Para continuar neste serviço, é necessário que o token registado tenho um determinado nível de confiança. Atualmente, você não tem um token registado, ou o nível de confiança do seu token é muito baixo. Veja o endereço abaixo para mais informações sobre o processo de registo.',
    'error_stepup_callout_unmet_loa_link_text' => 'Leia mais sobre o processo de registro.',
    'error_stepup_callout_unmet_loa_link_target' => 'https://support.surfconext.nl/stepup-noauthncontext',
    'error_stepup_callout_user_cancelled_title' => 'Erro - Carregamento cancelado',
    'error_stepup_callout_user_cancelled_desc' => 'Você cancelou o processo de autenticação. Volte ao serviço se você pretender tentar de novo.',
    'error_metadata_entity_id_not_found' => 'Metadata can not be generated',
    'error_metadata_entity_id_not_found_desc' => 'The following error occurred: %message%',
    'attributes_validation_succeeded' => 'Autenticação com sucesso',
    'attributes_validation_failed'    => 'Alguns atributos falharam na validação',
    'attributes_data_mailed'          => 'Os dados dos atributos foram enviados',
    'idp_debugging_title'             => 'Mostrar resposta do Fornecedor de Identidade',
    'retry'                           => 'Tente novamente',

    'attributes' => 'Atributos',
    'validation' => 'Validação',
    'idp_debugging_mail_explain' => 'Quando solicitado por %suiteName%,
                                        utilize o botão "Enviar Email a %suiteName%" em baixo
                                        para enviar o email com a informação deste ecrã.',
    'idp_debugging_mail_button' => 'Enviar Email a %suiteName%',

    // Logout
    'logout' => 'logout',
    'logout_description' => 'Esta aplicação utiliza autenticação centralizada, que permite uma única autenticação para várias aplicações. Para garantir que o seu logout está 100% assegurado, deve fe3char o seu browser completamente.',
    'logout_information_link' => '',
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
