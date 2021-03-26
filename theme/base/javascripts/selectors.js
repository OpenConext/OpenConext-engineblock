/***
 * INDEX SELECTORS
 * ***/
export const metadataCertificateLinkSelector = 'dl.metadata-certificates-list a';
export const indexPageSelector = '#engine-main-page';

/***
 * ERROR PAGE SELECTORS
 * ***/
export const errorTitleHeadingSelector = '.error-title__heading';
export const errorTitleMessageSelector = '.error-title__error-message';
export const languageErrorSelector = '.comp-language.error';

/***
 * CONSENT SELECTORS
 * ***/
/**
 * All elements that are animated on the consent screen.  Used to ensure that people who prefer reduced motion do not see the animations.
 *
 * See the @motion mixin explanation for a longer explanation as to why.
 */
export const consentAnimatedElementSelectors = '.tooltip__value, .modal__value, .consent__attributes, .attribute__valueWrapper > .attribute__value--list';
export const nokCheckboxId = 'cta_consent_nok';
export const nokButtonSelector = `label[for="${nokCheckboxId}"]`;
export const nokButtonSelectorForKeyboard = '.consent__ctas > .button--tertiary';
export const nokSectionSelector = '.consent__nok';
export const nokTitleSelector = '.consent__nok-title';
export const contentSectionSelector = '.consent__content';
export const backButtonSelector = '.consent__nok-back';
export const tooltipsAndModalLabels = 'label.tooltip, label.modal';
export const modalLabels = 'label.modal';
export const attributesSelector = 'ul.consent__attributes li';
export const tooltipLabelSelector = 'label.tooltip';
export const primaryTooltipLabelSelector = `.ie11__label > ${tooltipLabelSelector}`;
export const invisibleTooltipLabelsSelector = '.consent__attribute:nth-of-type(n+6) label.tooltip';
export const openToggleLabelSelector = '.openToggle__label';
export const firstInvisibleAttributeSelector = `${attributesSelector}:nth-of-type(6)`;
export const showMoreCheckboxId = 'showMoreCheckbox';

/***
 * WAYF SELECTORS
 * ***/
export const idpClass = 'wayf__idp';
export const idpSelector = `.${idpClass}`;
export const deletedAnnouncementId = 'deletedAnnouncement';
export const wayfPageSelector = 'main.wayf';
export const configurationId = 'wayf-configuration';
export const selectedIdpsSectionSelector = '.wayf__previousSelection';
export const selectedIdpsListSelector = '.wayf__previousSelection .wayf__idpList';
export const selectedIdpsLiSelector = `${selectedIdpsListSelector} > li`;
export const selectedIdpsFirstIdp = `${selectedIdpsSectionSelector} ${idpSelector}:first-of-type`;
export const previousSelectionFirstIdp = '.wayf__previousSelection li:first-of-type .wayf__idp';
export const deleteButtonTemplateId = 'deleteButton';
export const addAccountButtonClass = 'previousSelection__addAccount';
export const addAccountButtonSelector = `.${addAccountButtonClass}`;
export const idpTemplateSelector = 'idpTemplate';
export const selectedIdpsSelector = `${selectedIdpsSectionSelector} ${idpSelector}`;
export const unconnectedLiClass = 'idpItem--noAccess';
export const unconnectedIdpClass = 'wayf__idp--noAccess';
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
export const remainingIdpSectionSelector = '.wayf__remainingIdps';
export const remainingIdpListSelector = '.wayf__remainingIdps .wayf__idpList';
export const remainingIdpSelector = '.wayf__remainingIdps .wayf__idpList .wayf__idp';
export const searchFieldClass = 'search__field';
export const searchFieldSelector = `.${searchFieldClass}`;
export const searchResetClass = 'search__reset';
export const searchResetSelector = `.${searchResetClass}`;
export const searchSubmitClass = 'search__submit';
export const searchSubmitSelector = `.${searchSubmitClass}`;
export const searchAnnouncementId = 'searchResultAnnouncement';
export const noAccessFieldsToValidy = ['name', 'email'];
export const noAccessSectionSelector = '.wayf__noAccess';
export const noAccessLi = '.wayf__idpList > li';
export const noAccessFormSelector = '.noAccess__requestForm';
export const noAccessFieldsetsSelector = '.noAccess__requestForm fieldset';
export const noAccessTitle = '.noAccess__title';
export const noAccessUnconnectableClass = 'wayf__noAccess--unconnectable';
export const noAccessConnectableClass = 'wayf__noAccess--connectable';
export const formErrorClass = 'form__error';
export const succesMessageSelector = '.notification__success';
export const entityIdInputSelector = 'input[name="idpEntityId"]';
export const entityInstitutionInputSelector = 'input[name="institution"]';
export const cancelButtonSelector = '.cta__cancel';
export const showFormSelector = '.cta__showForm';
export const submitRequestSelector = '.cta__request';
export const nameFieldSelector = '#name';
export const nameErrorSelector = `${nameFieldSelector} + .form_error`;
export const emailFieldSelector = '#email';
export const emailErrorSelector = `${emailFieldSelector} + .form_error`;
export const errorMessageSelector = '.notification__critical';
export const rememberChoiceId = 'rememberChoice';
export const defaultIdpId = 'defaultIdp';
export const defaultIdpClass = 'wayf__defaultIdpLink';
export const defaultIdpSelector = `.${defaultIdpClass}`;
export const defaultIdpItemSelector = '#defaultIdp';
export const defaultIdpInformational = '.remainingIdps__defaultIdp';
export const remainingIdpCutoffMetSelector = '.wayf__remainingIdps .wayf__idpList--cutoffMet';
export const remainingIdpLiSelector = '.wayf__remainingIdps .wayf__idpList > li';
export const remainingIdpAfterSearchSelector = '.wayf__remainingIdps .wayf__idpList > li:not([data-weight="0"]) .wayf__idp';
export const firstRemainingIdpAfterSearchSelector = '.wayf__remainingIdps .wayf__idpList > li:not([data-weight="0"]):first-of-type .wayf__idp';
export const lastRemainingIdpAfterSearchSelector = '.wayf__remainingIdps .wayf__idpList > li:not([data-weight="0"]):last-of-type .wayf__idp';
export const noMatchSelector = '.wayf__remainingIdps .wayf__idp[data-weight="0"]';
export const matchSelector = '.wayf__remainingIdps .wayf__idp:not([data-weight="0"])';
export const toggleButtonClass = 'previousSelection__toggleLabel';
export const toggleButtonSelector = `.${toggleButtonClass}`;
export const editButtonClass = 'previousSelection__edit';
export const doneButtonClass = 'previousSelection__done';
export const previousSelectionTitleSelector = '.previousSelection__title';
export const searchingClass = 'wayf__idpList--searching';
export const noResultSectionSelector = '.wayf__noResults';
export const idpListSelector = '.wayf__idpList';
export const ariaPressedCheckboxSelector = 'input[aria-pressed]';
export const topId = 'top';
