import {fireClickEvent} from '../utility/fireClickEvent';
import {animateInteractiveSections} from '../utility/animateInteractiveSections';

export const addAccessibilitySupport = () => {
  consentKeyboardBehaviourHandler();
  consentAnimateInteractiveElements(consentAnimatedElementSelectors);
};
