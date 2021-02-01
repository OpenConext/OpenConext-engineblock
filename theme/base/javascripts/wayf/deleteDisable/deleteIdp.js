import {savePreviousSelection} from './savePreviousSelection';
import {configurationId, idpSelector} from '../../selectors';
import * as Cookies from 'js-cookie';
import {getData} from '../../utility/getData';

/**
 * Delete an idp from the previous selection in both html & the previous selection list
 *
 * @param element               HTMLElement   the deleted idp
 */
export const deleteIdp = (element) => {
  const cookieName = JSON.parse(document.getElementById(configurationId).innerHTML).previousSelectionCookieName;
  const idp = element.closest(idpSelector);
  const id = getData(idp, 'entityid');
  const cookie = JSON.parse(Cookies.get(cookieName));

  cookie.forEach((idp, index) => {
    if (idp.idp === id) {
      cookie.splice((index - 1), 1);
    }
  });

  savePreviousSelection(cookie, cookieName);

  // Remove deleted item from html
  idp.closest('li').remove();
};
