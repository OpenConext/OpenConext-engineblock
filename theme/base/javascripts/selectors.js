/**
 * TODO: ensure that this gets copied in the scaffolding function for new themes
 * TODO: ensure that the imports get altered in the scaffolding function for new themes
 */

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
export const selectedIdpsSelector = '.wayf__previousSelection';
export const configurationId = 'wayf-configuration';
