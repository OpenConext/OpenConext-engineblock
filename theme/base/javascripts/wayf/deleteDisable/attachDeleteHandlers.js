import {handleDeleteDisable} from '../handleDeleteDisable';
import {idpDeleteDisabledSelector} from '../../selectors';

/**
 * Ensure all delete buttons can actually delete.
 */
export const attachDeleteHandlers = () => {
  const buttons = document.querySelectorAll(idpDeleteDisabledSelector);

  buttons.forEach(button => button.addEventListener('click', (e) => handleDeleteDisable(e)));
};
