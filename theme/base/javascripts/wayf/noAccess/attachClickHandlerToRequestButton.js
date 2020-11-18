import {attachClickHandlerToForm} from './attachClickHandlerToForm';
import {getData} from '../../utility/getData';
import {toggleFormFieldsAndButton} from './toggleFormFieldsAndButton';

/**
 * Ensure clicking the request button shows the right behaviour:
 * - hide request button
 * - show submit button
 * - show formfields
 * - ensure submitting the form is possible
 *
 * @param parentSection
 * @param noAccess
 * @param form
 */
export const attachClickHandlerToRequestButton = (parentSection, noAccess, form) => {
  const requestButton = document.querySelector('.cta__showForm');
  const hasClickHandler = getData(requestButton, 'clickhandled');

  // if clickHandler's already been attached, do not attach it again
  if (hasClickHandler) {
    return;
  }

  // attach clickHandler to request button
  requestButton.addEventListener('click', () => {
    toggleFormFieldsAndButton();
  });
  requestButton.setAttribute('data-clickhandled', true);

  // attach clickHandler for form
  attachClickHandlerToForm(form, parentSection, noAccess);
};
