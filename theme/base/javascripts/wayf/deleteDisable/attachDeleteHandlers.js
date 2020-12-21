import {handleDeleteDisable} from '../handleDeleteDisable';
import {idpDeleteDisabledSelector} from '../../selectors';

/**
 * Ensure all delete buttons can actually delete.
 *
 * @param previouslySelectedIdps
 */
export const attachDeleteHandlers = (previouslySelectedIdps) => {
  const buttons = document.querySelectorAll(idpDeleteDisabledSelector);

  buttons.forEach(button => button.addEventListener('click', (e) => handleDeleteDisable(e, previouslySelectedIdps)));
};
