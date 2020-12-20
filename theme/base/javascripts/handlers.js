import {enterHandler} from './consent/enterHandler';
import {keyboardBehaviour} from './consent/keyboardBehaviour';
import {animateInteractiveSections} from './utility/animateInteractiveSections';

/** Consent Handlers **/
export const consentEnterHandler = (target) => enterHandler(target);
export const consentKeyboardBehaviourHandler = keyboardBehaviour;
export const consentAnimateInteractiveElements = (selector) => animateInteractiveSections(selector);
