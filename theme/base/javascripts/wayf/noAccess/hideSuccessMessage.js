import {toggleVisibility} from '../../utility/toggleVisibility';

/**
 * Hide the success notification
 */
export const hideSuccessMessage = () => {
  const successMessage = document.querySelector('.notification__success');

  if (!successMessage.classList.contains('hidden')) {
    toggleVisibility(successMessage);
  }
};
