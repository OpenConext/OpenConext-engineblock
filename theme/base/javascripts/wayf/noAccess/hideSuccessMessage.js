import {toggleVisibility} from '../../utility/toggleVisibility';
import {isHiddenElement} from '../../utility/isHiddenElement';

/**
 * Hide the success notification
 */
export const hideSuccessMessage = () => {
  const successMessage = document.querySelector('.notification__success');

  if (!isHiddenElement(successMessage)) {
    toggleVisibility(successMessage);
  }
};
