import {showSuccessMessage} from './showSuccessMessage';
import {getData} from '../../utility/getData';

export const attachClickHandlerToForm = (form, parentSection, noAccess) => {
  const hasClickHandler = getData(form, 'clickhandled');

  // if clickHandler's already been attached, do not attach it again
  if (hasClickHandler) {
    return;
  }

  form.addEventListener('submit', handleFormSubmission);
  form.setAttribute('data-clickhandled', true);

  function handleFormSubmission(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    fetch('/authentication/idp/performRequestAccess', {
      method: 'POST',
      body: formData,
    }).then(function (response) {
      if (response.ok) {
        return response.text();
      }

      return Promise.reject(response);
    }).then(function () {
      showSuccessMessage(parentSection, noAccess);
    }).catch(function (error) {
      console.warn(error);
    });
  }
};
