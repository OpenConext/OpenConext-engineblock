<?php
$overrides = [];
$overridesFile = __DIR__ . '/overrides.pt.php';
if (file_exists($overridesFile)) {
    $overrides = require $overridesFile;
}
return $overrides + [
    // Values used in placeholders for other translations
    // %suiteName%: OpenConext (default), SURFconext, ACMEconext
    'suite_name' => '{{ instance_name }}',
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
    // General
    'value'                 => 'Valor',
    'post_data'             => 'POST Data',
    'processing'            => 'A estabelecer ligação ao serviço',
    'processing_waiting'    => 'A aguardar uma resposta do serviço.',
    'processing_long'       => 'Aguarde um momento por favor, poderá demorar um pouco...',
    'go_back'               => '&lt;&lt; Volte atrás',
    'note'                  => 'Nota',
    'note_no_script'        => 'Visto que o seu browser não suporta JavaScript, deve pressionar no botão em baixo para prosseguir.',
    // Feedback
    'timestamp'             => 'Timestamp',
    'requestId'             => 'ID Único do Pedido',
    'identityProvider'      => 'Fornecedor de Identidade',
    'serviceProvider'       => 'Fornecedor de Serviço',
    'serviceProviderName'   => 'Nome do Fornecedor do Serviço',
    'userAgent'             => 'Agente do Utilizador',
    'ipAddress'             => 'Endereço IP',
    'statusCode'            => 'Código de Estado',
    'statusMessage'         => 'Mensagem de Estado',
    'attributeName'         => 'Nome do Atributo',
    'attributeValue'        => 'Valor do Atributo',
    // WAYF
    'search'                    => 'Procure por uma %organisationNoun%...',
    'our_suggestion'            => 'Escolha anterior:',
    'edit'                      => 'editar',
    'done'                      => 'feito',
    'idps_with_access'          => 'Fornecedores de Identidade com acesso',
    'idps_without_access'       => 'Fornecedores de Identidade sem acesso',
    'log_in_to'                 => 'Seleccione uma %organisationNoun% para se autenticar no serviço:',
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
    'cookie_removal_description' => '<p>Em baixo poderá encontrar uma visão geral dos seus cookies e a possibilidade de os remover individualmente ou todos de uma vez.</p>',
    'cookie_removal_confirm'     => 'O seu cookie foi removido.',
    'cookies_removal_confirm'    => 'Os seus cookies foram removidos.',
    // Footer
    'service_by'            => 'Este serviço está ligado através da',
    'serviceprovider_link'  => '<a href="https://openconext.org/" target="_blank">%suiteName%</a>',
    'terms_of_service_link' => '<a href="#" target="_blank">Termos do Serviço</a>',
    // Form
    'request_access_instructions' => '<h2>Infelizmente, você não tem acesso ao serviço que seleccionou.
                                   O que pode fazer?</h2>
                                <p>Se pretende ter acesso a este serviço, preencha o formulário em baixo.
                                   Em seguida, nós iremos encaminhar o seu pedido à pessoa responsável pela
                                   gestão dos portfolios de serviço da sua %organisationNoun%.</p>',
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
    'send_confirm'          => 'O seu pedido foi enviado',
    'send_confirm_desc'     => '<p>A sua solicitação foi encaminha para a sua %organisationNoun%. As decisões para a disponibilidade deste serviço serão tomadas pela equipa de IT da sua %organisationNoun%.</p>',
    // Consent page
    'consent_header_title'                    => '%arg1% necessita da sua informação antes de efetuar login',
    'consent_header_text'                     => 'Este serviço necessita da seguinte informação para funcionar devidamente. Estes dados serão enviados de forma segura para a sua %organisationNoun% relativamente a %arg1% por <a class="help" href="#" data-slidein="about"><span>%suiteName%</span></a>.',
    'consent_privacy_title'                   => 'A informação seguinte será partilhada com %arg1%:',
    'consent_privacy_link'                    => 'Leia a política de privacidade para este serviço',
    'consent_attributes_correction_link'      => 'Os seus detalhes estão incorrectos?',
    'consent_attributes_show_more'            => 'Mostrar mais informação',
    'consent_attributes_show_less'            => 'Mostrar menos informação',
    'consent_buttons_title'                   => 'Concorda com a partilha desta informação?',
    'consent_buttons_ok'                      => 'Sim, prossiga para %arg1%',
    'consent_buttons_ok_minimal'              => 'Prossiga para %arg1%',
    'consent_buttons_nok'                     => 'Não, eu não concordo',
    'consent_buttons_nok_minimal'             => 'Cancelar',
    'consent_explanation_title'               => 'Preste atenção quando utiliza este serviço',
    'consent_footer_text_singular'            => 'Você está a utilizar um outro serviço através do %suiteName%. <a href="%arg1%" target="_blank"><span>Veja a lista de serviços e as informações do seu perfil.</span></a>',
    'consent_footer_text_plural'              => 'Você está a utilizar %arg1% serviços através do %suiteName%. <a href="%arg2%" target="_blank"><span>Veja a lista de serviços e as informações do seu perfil.</span></a>',
    'consent_footer_text_first_consent'       => 'Você não está a usar nenhum serviço através do %suiteName%. <a href="%arg1%" target="_blank"><span>Veja a informação do seu perfil.</span></a>',
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
    // Consent slidein: About %suiteName%
    'consent_slidein_about_text'  => <<<'TXT'
<h1>A efectuar login através da %suiteName%</h1>
<p>%suiteName% permite que as pessoas de forma fácil e segura acedam a vários serviços de cloud usando as sua própria %accountNoun%. %suiteName% oferece protecção extra na sua privacidade, enviando apenas um conjunto mínimo de dados pessoais para estes servioes em cloud.</p>
TXT
    ,
    // Consent slidein: Reject
    'consent_slidein_reject_text'  => <<<'TXT'
<h1>Você recusou partilhar os seus dados</h1>
<p>O serviço ao qual se autenticou necessita dos seus dados para funcionar. Se não concorda com a partilha da sua informação, não poderá utilizar este serviço. Fechando o browser ou esta janela, está a recusar a partilha da informação necessária. Se mudar de ideias após esta decisão, volte a entrar no serviço e serão efectuadas as mesmas questões de partilha da informação de novo.</p>
TXT
    ,
    // Generic slide-in
    'slidein_close' => 'Fechar',
    'slidein_read_more' => 'Leia mais',
    // Error screens
    'error_404'                         => '404 - Página não encontrada',
    'error_404_desc'                    => 'Esta página não foi encontrada.',
    'error_help_desc'               => '<p></p>',
    'error_no_consent'              => 'Não é possivel continua para o serviço',
    'error_no_consent_desc'         => 'Esta aplicação só pode ser utilizada quando a informação mencionada for partilhada.<br /><br />
Se pretende utilizar esta aplicação devem:<br />
<ul><li>reinicie o seu browser</li>
<li>faça a autenticação de novo</li>
<li>partilhe a sua informação</li></ul>',
    'error_no_idps'                 => 'Erro - Não foi encontrado nenhum Fornecedor de Identidade',
    'error_no_idps_desc'            => '<p>
O serviço (&lsquo;Service Provider&rsquo;) a que pretende ligar-se não está acessível através da %suiteName%.<br /><br />
    </p>',
    'error_session_lost'            => 'Erro - a sua sessão foi perdida',
    'error_session_lost_desc'       => '<p>
Por algum motivo, foi perdido o destino para onde pretendia ir. Terá aguardado demasiado tempo? Se foi o caso, pedimos que volte a tentar. Será que o seu browser aceita cookies? Você estará a utilizar um URL ou marcador desatualizado?<br /><br />
        <br /><br />
    </p>',
    'error_authorization_policy_violation'            => 'Erro - Sem acesso',
    'error_authorization_policy_violation_desc'       => '<p>
        Você autenticu-se com sucesso na sua %organisationNoun%, mas infelizmente você não pode utilizar este serviço (o &lsquo;Fornecedor de Serviço&rsquo;) porque não tem acesso. A sua %organisationNoun% limita o acesso a este serviço com uma <i>política de autorização</i>. Entre em contacto com o suporte da sua %organisationNoun% se acha que deve ser-lhe concedido acesso ao serviço.
    </p>',
    'error_authorization_policy_violation_info'       => 'Mensagem da sua %organisationNoun%: ',
    'error_no_message'              => 'Erro - Não foi recebido nenhuma mensagem',
    'error_no_message_desc'         => 'Estávamos a aguardar uma mensagem, mas não chegou nenhuma? Alguma coisa correu mal. Tente de novo por favor.',
    'error_invalid_acs_location'    => 'O "Serviço de Consumidor de Asserção" fornecido é desconhecido ou inválido.',
    'error_invalid_acs_binding'     => 'O ACS "Binding Type" é inválido',
    'error_invalid_acs_binding_desc'     => 'O "Binding Type" do "Serviço de Consumidor de Asserção" fornecido ou configurado é desconhecido ou inválido.',
    'error_unsupported_signature_method' => 'O método de assinatura não é suportado',
    'error_unsupported_signature_method_desc' => 'O método de assinatura %arg1% não é suportado, faça upgrade para RSA-SHA256 (http://www.w3.org/2001/04/xmldsig-more#rsa-sha256).',
    'error_unknown_preselected_idp' => 'Erro - Não há ligação entre %organisationNoun% e o serviço',
    'error_unknown_preselected_idp_desc' => '<p>
        A %organisationNoun% que pretende utilizar para se autenticar e aceder ao serviço, não tem activado o acesso a este serviço. Isto significa que não pode aceder a este serviço através da %suiteName%. Entre em contacto com com o suporte da sua %organisationNoun% para solicitar o acesso a este serviço. Declare que serviço se trata (o &lsquo;Fornecedor de Serviço&rsquo;) e porque necessita do acesso.
    </p>',
    'error_unknown_service_provider'          => 'Erro - Não é possível fornecer metadados para o EntityID \'%arg1%\'',
    'error_unknown_service_provider_desc'     => '<p>O serviço solicitado não foi encontrado.</p>',
    'error_unknown_issuer'          => 'Erro - Serviço desconhecido',
    'error_unknown_issuer_desc'     => '<p>
        O serviço a que pretende autenticar-se é desconhecido para a %suiteName%. Possivelmente a sua %organisationNoun% nunca permitiu o acesso a este serviço. Entre em contacto com o suporte da sua %organisationNoun% e fornecer-lhes as seguintes informações:
    </p>',
    'error_generic'                     => 'Erro - Ocorreu um erro',
    'error_generic_desc'                => '<p>
A sua autenticação falhou e não sabemos exactamente porquê. Tente de novo e no caso de voltar a não funcionar, entre em contacto com o suporte da sua %organisationNoun% para pedir ajuda.
    </p>',
    'error_missing_required_fields'     => 'Erro - Campo necessário em falta',
    'error_missing_required_fields_desc'=> '<p>
        Não pode usar esta aplicação porque a sua %organisationNoun% não está a fornecer a informação necessária.
    </p>
    <p>
        Entre em contacto com a sua %organisationNoun% com a informação indicada em baixo.
    </p>
    <p>
        A autenticação falhou porque o Fornecedor de Identidade da sua %organisationNoun% não forneceu %suiteName% com um ou mais dos seguintes atributos obrigatórios:
        <ul>
            <li>UID</li>
            <li>schacHomeOrganization</li>
        </ul>
    </p>',
    'error_invalid_attribute_value' => 'Valor do atributo não permitido',
    'error_invalid_attribute_value_desc' => '<p>
        A sua %organisationNoun% utilizou um valor para o atributo %attributeName% ("%attributeValue%") o que não é permitido para esta %organisationNoun%. Desta forma, não pode autenticar-se.
    </p>
    <p>
        Apenas a sua %organisationNoun% pode resolver esta situação. Entre em contacto com o suporte deste serviço da sua %organisationNoun%.
    </p>',
    'error_received_error_status_code'     => 'Erro - Erro no Fornecedor de Identidade',
    'error_received_error_status_code_desc'=> '<p>
A sua %organisationNoun% negou-lhe acesso a este serviço. Terá de entrar em contacto com o suporte (IT) para ver se é possível corrigir a situação.
    </p>',
    'error_received_invalid_response'       => 'Erro - Resposta inválida do Fornecedor de Identidade',
    'error_received_invalid_signed_response'=> 'Erro - resposta de assinatura inválida do Fornecedor de Identidade',
    'error_stuck_in_authentication_loop' => 'Erro - Ficou preso(a) no vazio',
    'error_stuck_in_authentication_loop_desc' => '<p>
        Autenticou-se com sucesso no seu Fornecedor de Identidade, mas o serviço ao qual está a tentar aceder reencaminhou-o de volta para %suiteName%. Como já está autenticado, o %suiteName% o reencaminha de volta para o serviço, o que resulta num ciclo infinito. Muito provavelmente, isto é provocado por um erro no Fornecedor de Serviço.
    </p>',
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
    'error_unknown_service'         => 'Erro - Serviço desconhecido',
    'error_unknown_service_desc'    => '<p>O serviço solicitado não foi encontrado.</p>',
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
];
