import {savePreviousSelection} from './savePreviousSelection';
import {configurationId, extendedIdpSelector} from '../../selectors';

/**
 * Delete an idp from the previous selection in both html & the previous selection list
 *
 * @param element               HTMLElement   the deleted idp
 * @param previousSelection     Array         the list of previously selected idps
 */
export const deleteIdp = (element, previousSelection) => {
  const configuration = JSON.parse(document.getElementById(configurationId).innerHTML);
  const cookieName = configuration.previousSelectionCookieName;
  const idp = element.closest(extendedIdpSelector);
  const index = Number(idp.getAttribute('data-index'));
  previousSelection.splice((index - 1), 1);
  savePreviousSelection(previousSelection, cookieName);

  // Remove deleted item from html
  idp.closest('li').remove();
};
