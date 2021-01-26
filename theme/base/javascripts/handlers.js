import {enterHandler} from './consent/enterHandler';
import {keyboardBehaviour} from './consent/keyboardBehaviour';
import {keyboardBehaviour as wayfKeyboardBehaviour} from './wayf/keyboardBehaviour';
import {animateInteractiveSections} from './utility/animateInteractiveSections';
import {addAccessibilitySupport} from './consent/addA11ySupport';
import {switchConsentSection} from './consent/switchConsentSection';
import {addClickHandlerOnce} from './utility/addClickHandlerOnce';
import {
  backButtonSelector,
  configurationId,
  nokButtonSelector,
  nokSectionSelector,
  selectedIdpsSectionSelector,
  tooltipsAndModalLabels,
} from './selectors';
import {handlePreviousSelectionVisible} from './wayf/handlePreviousSelectionVisible';
import {mouseBehaviour} from './wayf/mouseBehaviour';
import {searchBehaviour} from './wayf/searchBehaviour';
import {submitForm} from './wayf/submitForm';
import {cancelButtonClickHandlerCreator} from './wayf/noAccess/cancelButtonClickHandler';
import {toggleFormFieldsAndButton} from './wayf/noAccess/toggleFormFieldsAndButton';
import {changeAriaHiddenValue} from './utility/changeAriaHiddenValue';
import {addTooltipAndModalAriaHandlers} from './consent/addTooltipAndModalAriaHandlers';

/***
 * CONSENT HANDLERS
 * ***/
export const consentCallbackAfterLoad = () => {
  addAccessibilitySupport();
  addClickHandlerOnce(nokButtonSelector, consentNokHandler);
};
export const consentEnterHandler = (target) => enterHandler(target);
export const consentKeyboardBehaviourHandler = keyboardBehaviour;
export const consentAnimateInteractiveElements = (selector) => {
  animateInteractiveSections(selector);
  addTooltipAndModalAriaHandlers(tooltipsAndModalLabels);
};
export const consentNokHandler = (e) => {
  const nokSection = document.querySelector(nokSectionSelector);
  switchConsentSection(e);
  addClickHandlerOnce(backButtonSelector, backButtonHandler(nokSection));
  changeAriaHiddenValue(nokSection);
};
export const backButtonHandler = (nokSection) => {
  return (e) => {
    switchConsentSection(e);
    changeAriaHiddenValue(nokSection);
  };
};


/***
 * WAYF HANDLERS
 * ***/
export const wayfCallbackAfterLoad = () => {
  // Initialize variables
  const selectedIdps = document.querySelector(selectedIdpsSectionSelector);
  const configuration = JSON.parse(document.getElementById(configurationId).innerHTML);
  const previouslySelectedIdps = configuration.previousSelectionList;

  // Initialize behaviour
  handlePreviousSelectionVisible(selectedIdps, previouslySelectedIdps);
  wayfKeyboardBehaviour(previouslySelectedIdps);
  mouseBehaviour();
  searchBehaviour();
};
export const idpSubmitHandler = (e) => {
  submitForm(e, true);
};
export const cancelButtonClickHandler = (parentSection, noAccess) => cancelButtonClickHandlerCreator(parentSection, noAccess);
export const requestButtonHandler = () => { toggleFormFieldsAndButton(); };
