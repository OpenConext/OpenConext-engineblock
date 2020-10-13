import {hideNoAccess} from './hideNoAccess';
import {toggleVisibility} from '../../utility/toggleVisibility';

export const showSuccessMessage = (parentSection, noAccess) => {
  const successMessage = document.querySelector('.notification__success');
  hideNoAccess(parentSection, noAccess);

  if (successMessage.classList.contains('hidden')) {
    toggleVisibility(successMessage);
  }
};
