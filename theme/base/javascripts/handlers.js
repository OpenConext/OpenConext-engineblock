import {enterHandler} from './consent/enterHandler';
import {keyboardBehaviour} from './consent/keyboardBehaviour';
import {animateInteractiveSections} from './utility/animateInteractiveSections';
import {addAccessibilitySupport} from './consent/addA11ySupport';
import {addNokListener} from './consent/addNokListener';

/** Consent Handlers **/
export const consentCallbackAfterLoad = () => {
  addAccessibilitySupport();
  addNokListener();
};
export const consentEnterHandler = (target) => enterHandler(target);
export const consentKeyboardBehaviourHandler = keyboardBehaviour;
export const consentAnimateInteractiveElements = (selector) => animateInteractiveSections(selector);
