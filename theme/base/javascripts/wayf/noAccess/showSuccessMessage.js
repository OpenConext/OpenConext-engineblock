import {hideNoAccess} from './hideNoAccess';
import {toggleVisibility} from '../../utility/toggleVisibility';
import {isHiddenElement} from '../../utility/isHiddenElement';

/**
 * Show the success message.  Hide the no Access section & show the Idp list to do so.
 *
 * @param parentSection
 * @param noAccess
 */
export const showSuccessMessage = (parentSection, noAccess) => {
  const successMessage = document.querySelector('.notification__success');
  hideNoAccess(parentSection, noAccess);

  if (isHiddenElement(successMessage)) {
    toggleVisibility(successMessage);
  }

  successMessage.focus();
};
