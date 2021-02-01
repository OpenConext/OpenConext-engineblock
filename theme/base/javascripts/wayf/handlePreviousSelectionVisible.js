import {hasSelectedIdpsInList} from './utility/hasSelectedIdpsInList';
import {attachDeleteHandlers} from './deleteDisable/attachDeleteHandlers';
import {switchIdpSection} from './utility/switchIdpSection';
import {focusOn} from "../utility/focusOn";
import {addAccountButtonSelector, previousSelectionFirstIdp, selectedIdpsListSelector} from '../selectors';
import {addClickHandlerOnce} from '../utility/addClickHandlerOnce';
import {idpSubmitHandler} from '../handlers';
import {matchPreviouslySelectedWithCookie} from './matchPreviouslySelectedWithCookie';

/**
 * Check if user has any previous selected Idps.
 * If so: show those & attach mouse handlers.
 * Else: do nothing as the remaining idps are shown by default.
 */
export const handlePreviousSelectionVisible = () => {
  matchPreviouslySelectedWithCookie();

  if (hasSelectedIdpsInList()) {
    mouseHandlersHiddenIdps();
    // put focus on the first IDP, so you can just hit enter & go
    focusOn(previousSelectionFirstIdp);
  }
};

/**
 * Mouse handlers for the previous selection.  As it's initially hidden we should not add them on load, but on show of that section.
 */
const mouseHandlersHiddenIdps = () => {
  // Show remaining idp section when hitting the add account button
  addClickHandlerOnce(addAccountButtonSelector, switchIdpSection);

  // Handle clicking the "garbage can" after hitting edit
  attachDeleteHandlers();

  // Attach event listener to previous selection idps-list
  addClickHandlerOnce(selectedIdpsListSelector, idpSubmitHandler);
};
