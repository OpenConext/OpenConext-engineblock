import {toggleVisibility} from '../../utility/toggleVisibility';

/**
 * Toggle the visibility of the error message
 */
export const toggleErrorMessage = () => {
  const errorMessage = document.querySelector('.notification__critical');

  toggleVisibility(errorMessage);
};
