import {hideErrorMessage} from './hideErrorMessage';
import {showSuccessMessage} from './showSuccessMessage';
import {toggleErrorMessage} from './toggleErrorMessage';
import {toggleFormFieldsAndButton} from './toggleFormFieldsAndButton';
import {valid} from "./validation";
import {scrollToTop} from './scrollToTop';

/**
 * Ensure submitting the form is possible.
 * On success:
 * - hide error message if present
 * - hide no access section
 * - show success message
 * - hide form fields
 * - hide submit button
 * - show request button
 *
 * On error:
 * - show error message
 * - log the error to the console
 *
 * @param form
 * @param parentSection
 * @param noAccess
 */
export const attachClickHandlerToForm = (form, parentSection, noAccess) => {
  form.addEventListener('submit', handleFormSubmission);
  form.setAttribute('data-clickhandled', true);

  function handleFormSubmission(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    if (!valid(formData)) {
      return false;
    }

    fetch('/authentication/idp/performRequestAccess', {
      method: 'POST',
      body: formData,
    }).then(function (response) {
      if (response.ok) {
        return response.text();
      }

      return Promise.reject(response);
    }).then(function () {
      hideErrorMessage(noAccess);
      toggleFormFieldsAndButton();
      showSuccessMessage(parentSection, noAccess);
      form.reset();
      scrollToTop();
    }).catch(function (error) {
      toggleErrorMessage();
      console.log(error);
    });
  }
};
