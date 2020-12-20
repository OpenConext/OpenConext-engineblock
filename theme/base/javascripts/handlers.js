import {enterHandler} from './consent/enterHandler';
import {keyboardBehaviour} from './consent/keyboardBehaviour';
import {animateInteractiveSections} from './utility/animateInteractiveSections';
import {addAccessibilitySupport} from './consent/addA11ySupport';
import {switchConsentSection} from './consent/switchConsentSection';
import {addClickHandlerOnce} from './utility/addClickHandlerOnce';
import {backButtonSelector, nokButtonSelector} from './selectors';

/**
 * TODO: ensure that this gets copied in the scaffolding function for new themes
 * TODO: ensure that the imports get altered in the scaffolding function for new themes
 */

/** Consent Handlers **/
export const consentCallbackAfterLoad = () => {
  addAccessibilitySupport();
  addClickHandlerOnce(nokButtonSelector, consentNokHandler);
};
export const consentEnterHandler = (target) => enterHandler(target);
export const consentKeyboardBehaviourHandler = keyboardBehaviour;
export const consentAnimateInteractiveElements = (selector) => animateInteractiveSections(selector);
export const consentNokHandler = (e) => {
  switchConsentSection(e);
  addClickHandlerOnce(backButtonSelector, switchConsentSection);
};
