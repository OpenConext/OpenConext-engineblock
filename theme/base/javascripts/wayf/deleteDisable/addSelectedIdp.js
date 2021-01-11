import {savePreviousSelection} from './savePreviousSelection';
import {configurationId} from '../../selectors';

/**
 * Add the selected idp to the list of previouslyselected idps and save it to the cookie.
 *
 * @param isPreviouslySelectedList
 * @param element
 */
export const addSelectedIdp = (isPreviouslySelectedList, element) => {
  const configuration = JSON.parse(document.getElementById(configurationId).innerHTML);
  const cookieName = configuration.previousSelectionCookieName;
  let previouslySelectedIdps = configuration.previousSelectionList;
  const entityId = element.getAttribute('data-entityid');
  const count = Number(element.getAttribute('data-count'));
  let alreadyInCookie = false;

  if (isPreviouslySelectedList) {
    previouslySelectedIdps.forEach(idp => {
      if (idp.entityId === entityId) {
        idp.count += 1;
        savePreviousSelection(previouslySelectedIdps, cookieName);
        alreadyInCookie = true;
        return;
      }
    });
  }

  if (!alreadyInCookie) {
    previouslySelectedIdps = [...previouslySelectedIdps, { entityId: entityId, count: (count + 1) }];
  }

  savePreviousSelection(previouslySelectedIdps, cookieName);
};
