<?php

$overrides = [];
$overridesFile = __DIR__ . '/overrides.pt.php';
if (file_exists($overridesFile)) {
    $overrides = require $overridesFile;
}

return $overrides + [
    // General
    'search'                    => 'Procure por uma %organisationNoun%...',
    'search_screenreader'       => 'Procurar',
    'log_in_to'                 => 'Seleccione uma %organisationNoun% para se autenticar no serviço:',
    'hamburger_screenreader'     => 'pular para o rodapé',

    // Consent page
    'consent_header_title'                    => '%arg1% necessita da sua informação antes de efetuar login',
    'consent_header_text'                     => 'Este serviço necessita da seguinte informação para funcionar devidamente. Estes dados serão enviados de forma segura para a sua %organisationNoun% relativamente a %arg1% por <a class="help" href="#" data-slidein="about"><span>%suiteName%</span></a>.',
    'consent_privacy_title'                   => 'A informação seguinte será partilhada com %arg1%:',
    'consent_privacy_link'                    => 'Leia a política de privacidade para este serviço',
    'consent_attributes_correction_link'      => 'Os seus detalhes estão incorrectos?',
    'consent_buttons_title'                   => 'Concorda com a partilha desta informação?',
    'consent_buttons_ok'                      => 'Sim, prossiga para %arg1%',
    'consent_footer_text_singular'            => 'Você está a utilizar um outro serviço através do %suiteName%. <a href="%arg1%" target="_blank"><span>Veja a lista de serviços e as informações do seu perfil.</span></a>',
    'consent_footer_text_plural'              => 'Você está a utilizar %arg1% serviços através do %suiteName%. <a href="%arg2%" target="_blank"><span>Veja a lista de serviços e as informações do seu perfil.</span></a>',
    'consent_footer_text_first_consent'       => 'Você não está a usar nenhum serviço através do %suiteName%. <a href="%arg1%" target="_blank"><span>Veja a informação do seu perfil.</span></a>',
    // Consent slidein: About %suiteName%
    'consent_slidein_about_head'  => 'A efectuar login através da %suiteName%',
    'consent_slidein_about_text'  => '%suiteName% permite que as pessoas de forma fácil e segura acedam a vários serviços de cloud usando as sua própria %accountNoun%. %suiteName% oferece protecção extra na sua privacidade, enviando apenas um conjunto mínimo de dados pessoais para estes servioes em cloud.',

    // Consent slidein: Reject
    'consent_slidein_reject_head'  => 'Você recusou partilhar os seus dados',
    'consent_slidein_reject_text'  => 'O serviço ao qual se autenticou necessita dos seus dados para funcionar. Se não concorda com a partilha da sua informação, não poderá utilizar este serviço. Fechando o browser ou esta janela, está a recusar a partilha da informação necessária. Se mudar de ideias após esta decisão, volte a entrar no serviço e serão efectuadas as mesmas questões de partilha da informação de novo.',

    // Generic slide-in
    'slidein_close' => 'Fechar',
];
