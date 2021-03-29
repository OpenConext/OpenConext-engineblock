import {
  consentAnimateInteractiveElements,
  consentHandleInvisibleTooltips,
  consentHandleNokFocus,
  consentHideInvisibleTooltips,
  consentKeyboardBehaviourHandler,
  consentToggleTooltipPressedState,
} from '../handlers';
import {consentAnimatedElementSelectors, nokButtonSelector, nokCheckboxId, openToggleLabelSelector} from '../selectors';
import {addClickHandlerOnce} from '../utility/addClickHandlerOnce';

export const addAccessibilitySupport = () => {
  consentKeyboardBehaviourHandler();
  consentAnimateInteractiveElements(consentAnimatedElementSelectors);
  consentHideInvisibleTooltips();
  addClickHandlerOnce(openToggleLabelSelector, consentHandleInvisibleTooltips);
  consentToggleTooltipPressedState();
  addClickHandlerOnce(nokButtonSelector, consentHandleNokFocus);
  document.getElementById(nokCheckboxId).classList.add('js');
};
