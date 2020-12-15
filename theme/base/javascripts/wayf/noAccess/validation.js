import {nodeListToArray} from "../../utility/nodeListToArray";

/**
 * Verify the name and email fields are not empty. Due to the way the validation was set up we can
 * not utilize the 'required' attribute on the form input fields. So a JS validation is required
 * to prevent empty form submits.
 *
 * @param formData
 * @returns {boolean}
 */
export const valid = (formData) => {
  // Start with hiding previous validation messages, they will reappear if the error persists.
  hideValidationMessages();
  const nameValid = notAnEmptyField(formData, 'name');
  const emailValid = notAnEmptyField(formData, 'email');
  // Next, test the name and email fields for validity, set error message if invalid.
  return nameValid && emailValid;
};

function notAnEmptyField(formData, elementName) {
  const value = document.getElementById(elementName).value.trim();
  if (!value) {
    showValidationMessage(elementName);
    return false;
  }
  return true;
}

function hideValidationMessages(){
  const errorMessages = nodeListToArray(document.getElementsByClassName('form__error'));
  errorMessages.forEach(errorMessage => {
    errorMessage.classList.add('hidden');
  });
}

function showValidationMessage(elementName){
  const errorMessage = document.querySelector(`p[data-labelfor="${elementName}"]`);
  errorMessage.classList.remove('hidden');
}
