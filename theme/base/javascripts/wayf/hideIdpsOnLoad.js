import {hasSelectedIdps} from './hasSelectedIdps';
import {toggleVisibility} from '../utility/toggleVisibility';
import {toggleRemaining} from './toggleRemaining';
import {showRemaining} from './showRemaining';
import {handleDeleteDisable} from './handleDeleteDisable';
import {submitForm} from './submitForm';

/**
 * Check if user has any previous selected Idps.  If so: show those, else show the larger list.
 *
 * @param selectedIdps    HTMLElement   the list of user-selected idps
 */
export const hideIdpsOnLoad = (selectedIdps) => {
  if (hasSelectedIdps()) {
    toggleRemaining();
    toggleVisibility(selectedIdps);
    mouseHandlersHiddenIdps();
  }
};

/**
 * Mouse handlers for the previous selection.  As it's initially hidden we should not add them on load, but on show of that section.
 */
const mouseHandlersHiddenIdps = () => {
  // Show remaining idp section when hitting the add account button
  document
    .querySelector('.previousSelection__addAccount')
    .addEventListener('click', showRemaining);
  // Handle clicking the "garbage can" after hitting edit
  document
    .querySelector('.idp__deleteDisable')
    .addEventListener('click', handleDeleteDisable);
  document
    .querySelector('.wayf__remainingIdps .wayf__idpList')
    .addEventListener('click', (e) => {
      submitForm(e);
    });
};
