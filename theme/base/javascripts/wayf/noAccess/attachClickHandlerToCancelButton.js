import {hideNoAccess} from './hideNoAccess';
import {getData} from '../../utility/getData';
import {hideErrorMessage} from './hideErrorMessage';
import {toggleFormFieldsAndButton} from './toggleFormFieldsAndButton';
import {isHiddenElement} from '../../utility/isHiddenElement';

/**
 * Ensure clicking the Cancel button shows the right behaviour:
 * - hide no Access section
 * - hide error message if shown
 * - hide form fields & submit button + show request button
 *
 * @param parentSection
 * @param noAccess
 */
export const attachClickHandlerToCancelButton = (parentSection, noAccess) => {
  const cancelButton = document.querySelector('.cta__cancel');
  const hasClickHandler = getData(cancelButton, 'clickhandled');

  // if clickHandler's already been attached, do not attach it again
  if (hasClickHandler) {
    return;
  }

  // add clickHandler
  cancelButton.addEventListener('click', () => {
    hideNoAccess(parentSection, noAccess);
    if (isHiddenElement(noAccess.querySelector('.cta__showForm'))) {
      toggleFormFieldsAndButton();
    }
  });
  cancelButton.setAttribute('data-clickhandled', true);

  // hide errorMessage if shown
  hideErrorMessage(noAccess);
};
