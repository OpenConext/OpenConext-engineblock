import {consentAnimateInteractiveElements, consentKeyboardBehaviourHandler} from '../handlers';
import {consentAnimatedElementSelectors} from '../selectors';

export const addAccessibilitySupport = () => {
  consentKeyboardBehaviourHandler();
  consentAnimateInteractiveElements(consentAnimatedElementSelectors);
};
