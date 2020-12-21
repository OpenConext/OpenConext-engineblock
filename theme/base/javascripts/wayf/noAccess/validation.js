import {nodeListToArray} from "../../utility/nodeListToArray";
import {formErrorClass, noAccessFieldsToValidy} from '../../selectors';

/**
 * Verify the name and email fields are not empty. Due to the way the validation was set up we can
 * not utilize the 'required' attribute on the form input fields. So a JS validation is required
 * to prevent empty form submits.
 *
 * Starts by hiding previous validation messages, they will reappear if the error persists.
 *
 * @param formData
 * @returns {boolean}   true if all is valid, false otherwise
 */
export const valid = (formData) => {
  let isValid = true;

  hideValidationMessages();
  noAccessFieldsToValidy.forEach(fieldName => {
    if (isAnEmptyField(formData, fieldName)) {
      isValid = false;
    }
  });

  return isValid;
};

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
    errorMessage.classList.add('hidden');
  });
}

function showValidationMessage(elementName){
  const errorMessage = document.querySelector(`p[data-labelfor="${elementName}"]`);
  errorMessage.classList.remove('hidden');
}
