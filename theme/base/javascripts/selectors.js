/***
 * TAGS
 * ***/
export const idpElement = 'ARTICLE';

/***
 * INDEX SELECTORS
 * ***/
export const metadataCertificateLinkSelector = 'dl.metadata-certificates-list a';
export const indexPageSelector = '#engine-main-page';

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
export const tooltipsAndModalLabels = 'label.tooltip, label.modal';
export const modalLabels = 'label.modal';

/***
 * WAYF SELECTORS
 * ***/
export const wayfPageSelector = 'main.wayf';
export const configurationId = 'wayf-configuration';
export const selectedIdpsSelector = '.wayf__previousSelection';
export const selectedIdpsListSelector = '.wayf__previousSelection .wayf__idpList';
export const previousSelectionFirstIdp = '.wayf__previousSelection li:first-of-type .wayf__idp';
export const addAccountButtonClass = 'previousSelection__addAccount';
export const addAccountButtonSelector = `.${addAccountButtonClass}`;
export const idpTag = 'article';
export const idpClass = 'wayf__idp';
export const idpSelector = `.${idpClass}`;
export const unconnectedIdpClass = 'wayf__idp--noAccess';
export const extendedIdpSelector = 'article.wayf__idp';
export const idpContentClass = 'idp__content';
export const idpTitleClass = 'idp__title';
export const idpSubmitClass = 'idp__submit';
export const idpLogoClass = 'idp__logo';
export const idpFormClass = 'idp__form';
export const idpFormSelector = `.${idpFormClass}`;
export const idpDeleteClass = 'idp__delete';
export const idpDeleteSelector = `.${idpDeleteClass}`;
export const idpDisabledClass = 'idp__disabled';
export const idpDisabledSelector = `.${idpDisabledClass}`;
export const idpDeleteDisabledClass = 'idp__deleteDisable';
export const idpDeleteDisabledSelector = `.${idpDeleteDisabledClass}`;
export const remainingIdpSelector = '.wayf__remainingIdps';
export const remainingIdpListSelector = '.wayf__remainingIdps .wayf__idpList';
export const searchFieldClass = 'search__field';
export const searchFieldSelector = `.${searchFieldClass}`;
export const searchResetSelector = '.search__reset';
export const searchSubmitSelector = '.search__submit';
export const noAccessFieldsToValidy = ['name', 'email'];
export const noAccessSectionSelector = '.wayf__noAccess';
export const noAccessLi = '.wayf__idpList > li';
export const noAccessFormSelector = '.noAccess__requestForm';
export const noAccessFieldsetsSelector = '.noAccess__requestForm fieldset';
export const noAccessUnconnectableClass = 'wayf__noAccess--unconnectable';
export const noAccessConnectableClass = 'wayf__noAccess--connectable';
export const formErrorClass = 'form__error';
export const succesMessageSelector = '.notification__success';
export const entityIdInputSelector = 'input[name="idpEntityId"]';
export const entityInstitutionInputSelector = 'input[name="institution"]';
export const cancelButtonSelector = '.cta__cancel';
export const showFormSelector = '.cta__showForm';
export const submitRequestSelector = '.cta__request';
export const errorMessageSelector = '.notification__critical';
export const rememberChoiceId = 'rememberChoice';
export const defaultIdpId = 'defaultIdp';
export const defaultIdpClass = 'wayf__defaultIdpLink';
export const defaultIdpSelector = `.${defaultIdpClass}`;
export const defaultIdpInformational = '.remainingIdps__defaultIdp';
export const remainingIdpCutoffMetSelector = '.wayf__remainingIdps .wayf__idpList--cutoffMet';
export const remainingIdpLiSelector = '.wayf__remainingIdps .wayf__idpList > li';
export const firstRemainingIdpSelector = '.wayf__remainingIdps li:first-of-type > .wayf__idp';
export const firstRemainingIdpAfterSearchSelector = '.wayf__remainingIdps .wayf__idpList > li:first-child > .wayf__idp:not([data-weight="0"])';
export const lastRemainingIdpSelector = '.wayf__remainingIdps li:last-of-type > .wayf__idp';
export const noMatchSelector = '.wayf__remainingIdps .wayf__idp[data-weight="0"]';
export const toggleButtonClass = 'previousSelection__toggleLabel';
export const editButtonClass = 'previousSelection__edit';
export const doneButtonClass = 'previousSelection__done';
export const searchingClass = 'wayf__idpList--searching';
export const noResultSectionSelector = '.wayf__noResults';
export const idpListSelector = '.wayf__idpList';
export const ariaPressedCheckboxSelector = 'input[aria-pressed]';
export const topId = 'top';
