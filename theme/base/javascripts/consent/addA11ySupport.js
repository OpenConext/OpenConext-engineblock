import {
  consentAnimateInteractiveElements,
  consentHandleInvisibleTooltips,
  consentHideInvisibleTooltips,
  consentKeyboardBehaviourHandler,
  consentToggleTooltipPressedState,
} from '../handlers';
import {consentAnimatedElementSelectors, openToggleLabelSelector} from '../selectors';
import {addClickHandlerOnce} from '../utility/addClickHandlerOnce';

export const addAccessibilitySupport = () => {
  consentKeyboardBehaviourHandler();
  consentAnimateInteractiveElements(consentAnimatedElementSelectors);
  consentHideInvisibleTooltips();
  addClickHandlerOnce(openToggleLabelSelector, consentHandleInvisibleTooltips);
  consentToggleTooltipPressedState();
};
