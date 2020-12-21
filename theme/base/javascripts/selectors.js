/**
 * TODO: ensure that this gets copied in the scaffolding function for new themes
 * TODO: ensure that the imports get altered in the scaffolding function for new themes
 */

/***
 * TAGS
 * ***/
export const idpElement = 'ARTICLE';

/***
 * CONSENT SELECTORS
 * ***/
/**
 * All elements that are animated on the consent screen.  Used to ensure that people who prefer reduced motion do not see the animations.
 *
 * See the @motion mixin explanation for a longer explanation as to why.
 */
export const consentAnimatedElementSelectors = '.tooltip__value, .modal__value, .consent__attributes, .attribute__valueWrapper > .attribute__value--list';

export const nokButtonSelector = 'label[for="cta_consent_nok"]';
export const nokSectionSelector = '.consent__nok';
export const contentSectionSelector = '.consent__content';
export const backButtonSelector = '.consent__nok-back';



/***
 * WAYF SELECTORS
 * ***/
export const wayfPageSelector = 'main.wayf';
export const configurationId = 'wayf-configuration';
export const selectedIdpsSelector = '.wayf__previousSelection';
export const selectedIdpsListSelector = '.wayf__previousSelection .wayf__idpList';
export const previousSelectionFirstIdp = '.wayf__previousSelection li:first-of-type .wayf__idp';
export const addAccountButtonSelector = '.previousSelection__addAccount';
export const idpSelector = '.wayf__idp';
export const idpFormSelector = '.idp__form';
export const idpDeleteClass = 'idp__delete';
export const idpDeleteSelector = `.${idpDeleteClass}`;
export const idpDisabledSelector = '.idp__disabled';
export const idpDeleteDisabledClass = 'idp__deleteDisabled';
export const idpDeleteDisabledSelector = `.${idpDeleteDisabledClass}`;
export const remainingIdpSelector = '.wayf__remainingIdps';
export const searchFieldSelector = '.search__field';
export const noAccessSectionSelector = '.wayf__noAccess';
export const noAccessLi = '.wayf__idpList > li';
export const noAccessFormSelector = '.noAccess__requestForm';
export const noAccessFieldsetsSelector = '.noAccess__requestForm fieldset';
export const noAccessUnconnectableClass = 'wayf__noAccess--unconnectable';
export const noAccessConnectableClass = 'wayf__noAccess--connectable';
export const succesMessageSelector = '.notification__success';
export const entityIdInputSelector = 'input[name="idpEntityId"]';
export const entityInstitutionInputSelector = 'input[name="institution"]';
export const cancelButtonSelector = '.cta__cancel';
export const showFormSelector = '.cta__showForm';
export const submitRequestSelector = '.cta__request';
export const errorMessageSelector = '.notification__critical';
export const rememberChoiceId = 'rememberChoice';
