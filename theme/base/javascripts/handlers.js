import {consentKeyboardBehaviour} from './consent/keyboardBehaviour';
import {keyboardBehaviour as wayfKeyboardBehaviour} from './wayf/keyboardBehaviour';
import {animateInteractiveSections} from './utility/animateInteractiveSections';
import {addClickHandlerOnce} from './utility/addClickHandlerOnce';
import {
  backButtonSelector,
  nokButtonSelector,
  tooltipsAndModalLabels,
  contentSectionSelector,
  nokSectionSelector,
  consentAnimatedElementSelectors,
  nokCheckboxId,
  openToggleLabelSelector,
} from './selectors';
import {handlePreviousSelectionVisible} from './wayf/handlePreviousSelectionVisible';
import {mouseBehaviour} from './wayf/mouseBehaviour';
import {searchBehaviour} from './wayf/searchBehaviour';
import {addTooltipAndModalAriaHandlers} from './consent/addTooltipAndModalAriaHandlers';
import {hideInvisibleTooltips} from './consent/hideInvisibleTooltips';
import {handleInvisibleTooltips} from './consent/handleInvisibleTooltips';
import {toggleTooltipPressedStates} from './consent/toggleTooltipPressedStates';
import {handleNokFocus} from './consent/handleNokFocus';
import {toggleVisibility} from './utility/toggleVisibility';

/***
 * CONSENT HANDLERS
 * ***/
export const consentCallbackAfterLoad = () => {
  addClickHandlerOnce(nokButtonSelector, consentNokHandler);
  addAccessibilitySupport();
};
export const consentHandleNokFocus = handleNokFocus;
export const consentAnimateInteractiveElements = (selector) => {
  animateInteractiveSections(selector);
  addTooltipAndModalAriaHandlers(tooltipsAndModalLabels);
};

export const addAccessibilitySupport = () => {
  consentKeyboardBehaviour();
  consentAnimateInteractiveElements(consentAnimatedElementSelectors);
  hideInvisibleTooltips();
  addClickHandlerOnce(openToggleLabelSelector, handleInvisibleTooltips);
  toggleTooltipPressedStates();
  addClickHandlerOnce(nokButtonSelector, consentHandleNokFocus);
  const el = document.getElementById(nokCheckboxId);
  if (el && el.classList) {
    el.classList.add('js');
  }
};

export const consentNokHandler = (e) => {
  if (!!e) {
    e.preventDefault();
  }
  const nokSection = document.querySelector(nokSectionSelector);
  const contentSection = document.querySelector(contentSectionSelector);
  toggleVisibility(nokSection);
  toggleVisibility(contentSection);
  consentHandleNokFocus();
  addClickHandlerOnce(backButtonSelector, backButtonHandler());
};
export const backButtonHandler = () => {
  return (e) => {
    if (!!e) {
      e.preventDefault();
    }
    const nokSection = document.querySelector(nokSectionSelector);
    const contentSection = document.querySelector(contentSectionSelector);
    toggleVisibility(nokSection);
    toggleVisibility(contentSection);
    consentHandleNokFocus();
  };
};


/***
 * WAYF HANDLERS
 * ***/
export const wayfCallbackAfterLoad = () => {
  // Initialize behaviour
  handlePreviousSelectionVisible();
  wayfKeyboardBehaviour();
  mouseBehaviour();
  searchBehaviour();
};

