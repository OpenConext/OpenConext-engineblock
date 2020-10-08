import {hasSelectedIdps} from './hasSelectedIdps';
import {handleNoneLeft} from './deleteDisable/handleNoneLeft';
import {deleteIdp} from './deleteDisable/deleteIdp';
import {moveIdpToRemaining} from './deleteDisable/moveIdpToRemaining';
import {reindexAndFocus} from './deleteDisable/reindexAndFocus';

/**
 * Handle what happens if a user clicks on either the delete button, or the disabled button.
 *
 * @param e
 * @param previouslySelectedIdps
 */
export const handleDeleteDisable = (e, previouslySelectedIdps) => {
  e.preventDefault();
  let element = e.target;

  // in case the origin is the span setting the element needs to be done differently
  if (e.target.tagName === 'SPAN') {
    element = e.target.closest('.idp__deleteDisable');
  }

  // handle clicking disabled button
  if (element.querySelector('.idp__delete').style.display === 'none') {
    return; // todo work this out
  }

  // Move it to the remaining idp list
  moveIdpToRemaining(element);
  // Remove item from previous selection & html
  deleteIdp(element, previouslySelectedIdps);

  // If no items are left: do what's needed.
  if (!hasSelectedIdps()) {
    handleNoneLeft();
    return;
  }

  // If there are items left: reindex & focus first one
  reindexAndFocus();
};
