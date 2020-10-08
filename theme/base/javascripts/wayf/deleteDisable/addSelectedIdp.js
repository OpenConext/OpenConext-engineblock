import {savePreviousSelection} from './savePreviousSelection';

/**
 * Add the selected idp to the list of previouslyselected idps and save it to the cookie.
 *
 * @param previouslySelectedIdps
 * @param element
 */
export const addSelectedIdp = (previouslySelectedIdps, element) => {
  const configuration = JSON.parse(document.getElementById('wayf-configuration').innerHTML);
  const cookieName = configuration.previousSelectionCookieName;
  const entityId = element.getAttribute('data-entityid');
  const count = Number(element.getAttribute('data-count'));

  if (previouslySelectedIdps) {

  }

  previouslySelectedIdps.forEach(idp => {
    if (idp.entityId === entityId) {
      idp.count += 1;
      savePreviousSelection(previouslySelectedIdps, cookieName);
      return;
    }
  });

  previouslySelectedIdps = [...previouslySelectedIdps, { entityId: entityId, count: (count + 1) }];
  savePreviousSelection(previouslySelectedIdps, cookieName);
};
