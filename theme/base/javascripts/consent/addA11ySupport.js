import {consentAnimatedElementSelectors} from '../selectors';
import {consentKeyboardBehaviourHandler, consentAnimateInteractiveElements} from '../handlers';

export const addAccessibilitySupport = () => {
  consentKeyboardBehaviourHandler();
  consentAnimateInteractiveElements(consentAnimatedElementSelectors);
};
