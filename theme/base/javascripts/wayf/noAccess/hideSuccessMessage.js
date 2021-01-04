import {toggleVisibility} from '../../utility/toggleVisibility';
import {isHiddenElement} from '../../utility/isHiddenElement';
import {succesMessageSelector} from '../../selectors';

/**
 * Hide the success notification
 */
export const hideSuccessMessage = () => {
  const successMessage = document.querySelector(succesMessageSelector);

  if (!isHiddenElement(successMessage)) {
    toggleVisibility(successMessage);
  }
};
