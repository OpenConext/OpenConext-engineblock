import {enterHandler} from './consent/enterHandler';
import {keyboardBehaviour} from './consent/keyboardBehaviour';
import {keyboardBehaviour as wayfKeyboardBehaviour} from './wayf/keyboardBehaviour';
import {animateInteractiveSections} from './utility/animateInteractiveSections';
import {addAccessibilitySupport} from './consent/addA11ySupport';
import {switchConsentSection} from './consent/switchConsentSection';
import {addClickHandlerOnce} from './utility/addClickHandlerOnce';
import {
  backButtonSelector,
  nokButtonSelector,
  tooltipsAndModalLabels,
} from './selectors';
import {handlePreviousSelectionVisible} from './wayf/handlePreviousSelectionVisible';
import {mouseBehaviour} from './wayf/mouseBehaviour';
import {searchBehaviour} from './wayf/searchBehaviour';
import {submitForm} from './wayf/submitForm';
import {cancelButtonClickHandlerCreator} from './wayf/noAccess/cancelButtonClickHandler';
import {toggleFormFieldsAndButton} from './wayf/noAccess/toggleFormFieldsAndButton';
import {addTooltipAndModalAriaHandlers} from './consent/addTooltipAndModalAriaHandlers';
import {hideInvisibleTooltips} from './consent/hideInvisibleTooltips';
import {showInvisibleTooltips} from './consent/showInvisibleTooltips';
import {handleInvisibleTooltips} from './consent/handleInvisibleTooltips';
import {toggleTooltipPressedStates} from './consent/toggleTooltipPressedStates';
import {handleNokFocus} from './consent/handleNokFocus';

/***
 * CONSENT HANDLERS
 * ***/
export const consentCallbackAfterLoad = () => {
  addClickHandlerOnce(nokButtonSelector, consentNokHandler);
  addAccessibilitySupport();
};
export const consentHandleNokFocus = handleNokFocus;
export const consentEnterHandler = (target) => enterHandler(target);
export const consentKeyboardBehaviourHandler = keyboardBehaviour;
export const consentAnimateInteractiveElements = (selector) => {
  animateInteractiveSections(selector);
  addTooltipAndModalAriaHandlers(tooltipsAndModalLabels);
};
export const consentHideInvisibleTooltips = hideInvisibleTooltips;
export const consentShowInvisibleTooltips = showInvisibleTooltips;
export const consentHandleInvisibleTooltips = handleInvisibleTooltips;
export const consentToggleTooltipPressedState = toggleTooltipPressedStates;
export const consentNokHandler = (e) => {
  switchConsentSection(e);
  consentHandleNokFocus();
  addClickHandlerOnce(backButtonSelector, backButtonHandler());
};
export const backButtonHandler = () => {
  return (e) => {
    switchConsentSection(e);
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
export const idpSubmitHandler = (e) => {
  submitForm(e);
};
export const cancelButtonClickHandler = (parentSection, noAccess) => cancelButtonClickHandlerCreator(parentSection, noAccess);
export const requestButtonHandler = () => { toggleFormFieldsAndButton(); };
