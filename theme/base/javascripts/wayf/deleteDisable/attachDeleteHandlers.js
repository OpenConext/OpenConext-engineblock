import {handleDeleteDisable} from '../handleDeleteDisable';

/**
 * Ensure all delete buttons can actually delete.
 *
 * @param previouslySelectedIdps
 */
export const attachDeleteHandlers = (previouslySelectedIdps) => {
  const buttons = document.querySelectorAll('.idp__deleteDisable');

  buttons.forEach(button => button.addEventListener('click', (e) => handleDeleteDisable(e, previouslySelectedIdps)));
};
