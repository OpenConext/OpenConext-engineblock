import {hasSelectedIdps} from './utility/hasSelectedIdps';
import {showRemaining} from './utility/showRemaining';
import {submitForm} from './submitForm';
import {attachDeleteHandlers} from './deleteDisable/attachDeleteHandlers';

/**
 * Check if user has any previous selected Idps.
 * If so: show those & attach mouse handlers.
 * Else: do nothing as the remaining idps are shown by default.
 *
 * @param selectedIdps         HTMLElement   the list of user-selected idps
 * @param previouslySelectedIdps    Array    the list of previously selected idps
 */
export const handlePreviousSelectionVisible = (selectedIdps, previouslySelectedIdps) => {
  if (hasSelectedIdps()) {
    mouseHandlersHiddenIdps(previouslySelectedIdps);
    // put focus on the first IDP, so you can just hit enter & go
    document.querySelector('.wayf__previousSelection li:first-of-type .wayf__idp').focus();
  }
};

/**
 * Mouse handlers for the previous selection.  As it's initially hidden we should not add them on load, but on show of that section.
 */
const mouseHandlersHiddenIdps = (previouslySelectedIdps) => {
  // Show remaining idp section when hitting the add account button
  document
    .querySelector('.previousSelection__addAccount')
    .addEventListener('click', showRemaining);

  // Handle clicking the "garbage can" after hitting edit
  attachDeleteHandlers(previouslySelectedIdps);

  // Attach event listener to previous selection idps-list
  document
    .querySelector('.wayf__previousSelection .wayf__idpList')
    .addEventListener('click', (e) => {
      submitForm(e, previouslySelectedIdps);
    });
};
