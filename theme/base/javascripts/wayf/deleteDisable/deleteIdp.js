import {savePreviousSelection} from './savePreviousSelection';
import {
  configurationId,
  deletedAnnouncementId,
  idpDeleteDisabledSelector,
  idpSelector,
  selectedIdpsLiSelector,
  toggleButtonSelector,
} from '../../selectors';
import Cookies from 'js-cookie';
import {getData} from '../../utility/getData';

/**
 * Delete an idp from the previous selection in both html & the previous selection list
 *
 * @param element               HTMLElement   the deleted idp
 */
export const deleteIdp = (element) => {
  const cookieName = JSON.parse(document.getElementById(configurationId).innerHTML).previousSelectionCookieName;
  const cookieValue = Cookies.get(cookieName);
  const cookie = JSON.parse(cookieValue);

  const idp = element.closest(idpSelector);
  const id = getData(idp, 'entityid');
  const parent = idp.parentElement;
  const title = getData(parent, 'title');
  const parentIndex = parseInt(getData(parent, 'index'));



  cookie.forEach((idp, index) => {
    if (idp.idp === id) {
      cookie.splice((index - 1), 1);
    }
  });

  savePreviousSelection(cookie, cookieName);

  // Remove deleted item from html
  parent.remove();

  // Announce delete to screenreaders
  announceDeletedIdp(title);
  moveFocus(parentIndex);
};

function announceDeletedIdp(title) {
  const deletedAnnouncement = document.getElementById(deletedAnnouncementId);
  const announcement = getData(deletedAnnouncement, 'announcement');
  deletedAnnouncement.innerHTML = `${title}${announcement}`;
}

function moveFocus(index) {
  const nextAccount = document.querySelector(`${selectedIdpsLiSelector}[data-index="${(index + 1)}"] ${idpDeleteDisabledSelector}`);

  if (!!nextAccount) {
    nextAccount.focus();
    return;
  }

  document.querySelector(toggleButtonSelector).focus();
}
