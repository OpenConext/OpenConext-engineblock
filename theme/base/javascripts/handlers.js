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
  selectedIdpsSelector,
} from './selectors';
import {handlePreviousSelectionVisible} from './wayf/handlePreviousSelectionVisible';
import {mouseBehaviour} from './wayf/mouseBehaviour';
import {searchBehaviour} from './wayf/searchBehaviour';
import {submitForm} from './wayf/submitForm';
import {cancelButtonClickHandlerCreator} from './wayf/noAccess/cancelButtonClickHandler';
import {toggleFormFieldsAndButton} from './wayf/noAccess/toggleFormFieldsAndButton';

/**
 * TODO: ensure that this gets copied in the scaffolding function for new themes
 * TODO: ensure that the imports get altered in the scaffolding function for new themes
 */

/***
 * CONSENT HANDLERS
 * ***/
export const consentCallbackAfterLoad = () => {
  addAccessibilitySupport();
  addClickHandlerOnce(nokButtonSelector, consentNokHandler);
};
export const consentEnterHandler = (target) => enterHandler(target);
export const consentKeyboardBehaviourHandler = keyboardBehaviour;
export const consentAnimateInteractiveElements = (selector) => animateInteractiveSections(selector);
export const consentNokHandler = (e) => {
  switchConsentSection(e);
  addClickHandlerOnce(backButtonSelector, switchConsentSection);
};


/***
 * WAYF HANDLERS
 * ***/
export const wayfCallbackAfterLoad = () => {
  // Initialize variables
  const selectedIdps = document.querySelector(selectedIdpsSelector);
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
