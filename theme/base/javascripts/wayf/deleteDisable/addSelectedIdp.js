import {savePreviousSelection} from './savePreviousSelection';
import {configurationId} from '../../selectors';
import Cookies from 'js-cookie';

/**
 * Add the selected idp to the list of previouslyselected idps and save it to the cookie.
 *
 * @param element
 */
export const addSelectedIdp = (element) => {
  const cookieName = JSON.parse(document.getElementById(configurationId).innerHTML).previousSelectionCookieName;
  const entityId = element.getAttribute('data-entityid');
  let alreadyInCookie = false;
  let cookie = Cookies.get(cookieName) || [];

  if (cookie.length) {
    cookie = JSON.parse(cookie);
    cookie.forEach(idp => {
      if (idp.idp === entityId) {
        idp.count += 1;
        savePreviousSelection(cookie, cookieName);
        alreadyInCookie = true;
        return;
      }
    });
  }

  if (!alreadyInCookie) {
    cookie = [...cookie, { idp: entityId, count: 1 }];
    savePreviousSelection(cookie, cookieName);
  }
};
