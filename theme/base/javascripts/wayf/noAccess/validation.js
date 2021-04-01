import {nodeListToArray} from "../../utility/nodeListToArray";
import {
  formErrorClass,
  noAccessFieldsToValidy,
  requestFormAnnouncementId,
} from '../../selectors';
import {hideElement} from '../../utility/hideElement';
import {showElement} from '../../utility/showElement';
import {getData} from '../../utility/getData';

/**
 * Verify the name and email fields are not empty. Due to the way the validation was set up we can not utilize the 'required' attribute on the form input fields (it's also not good for accessibility). So a JS validation is required to prevent empty form submits.
 *
 * Starts by hiding previous validation messages & disassociating them from the inputs, they will reappear if the error persists.
 *
 * If error messages are shown: associates them with their input fields & moves focus to the first error message.
 *
 * @param formData
 * @returns {boolean}   true if all is valid, false otherwise
 */
export const valid = (formData) => {
  let isValid = true;
  const fieldsWithErrors = [];

  hideValidationMessages();
  removeErrorAssociations();
  noAccessFieldsToValidy.forEach(fieldName => {
    if (isAnEmptyField(formData, fieldName)) {
      addErrorAssociation(fieldName);
      isValid = false;
      fieldsWithErrors.push(fieldName);
    }
  });

  if (!isValid) {
    fieldsWithErrors.sort().reverse();
    addAnnouncement(fieldsWithErrors);
    document.querySelector(`[name="${fieldsWithErrors[0]}"]`).focus();
  }

  return isValid;
};

function addAnnouncement(fieldsWithErrors) {
  const requestFormAnnouncement = document.getElementById(requestFormAnnouncementId);
  requestFormAnnouncement.innerHTML = getData(requestFormAnnouncement, 'announcement');
}

function removeErrorAssociations() {
  removeErrorAssociation('[id="nameerror"]');
  removeErrorAssociation('[id="emailerror"]');
}

function removeErrorAssociation(selector) {
  document.querySelector(selector).removeAttribute('aria-describedby');
}

function addErrorAssociation(fieldName) {
  document.querySelector(`[name="${fieldName}"]`).setAttribute('aria-describedby', `${fieldName}error`);
}

function isAnEmptyField(formData, elementName) {
  const value = document.getElementById(elementName).value.trim();

  if (!value) {
    showValidationMessage(elementName);
    return true;
  }

  return false;
}

function hideValidationMessages(){
  const errorMessages = nodeListToArray(document.getElementsByClassName(formErrorClass));
  errorMessages.forEach(errorMessage => {
    hideElement(errorMessage);
  });
}

function showValidationMessage(elementName){
  const errorMessage = document.querySelector(`p[data-labelfor="${elementName}"]`);
  showElement(errorMessage);
}
