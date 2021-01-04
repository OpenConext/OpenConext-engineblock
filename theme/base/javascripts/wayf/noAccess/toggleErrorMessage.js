import {toggleVisibility} from '../../utility/toggleVisibility';
import {errorMessageSelector} from '../../selectors';

/**
 * Toggle the visibility of the error message
 */
export const toggleErrorMessage = () => {
  const errorMessage = document.querySelector(errorMessageSelector);

  toggleVisibility(errorMessage);
};
