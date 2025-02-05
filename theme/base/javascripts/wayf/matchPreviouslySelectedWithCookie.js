import {configurationId, deleteButtonTemplateId, idpContentClass, idpDeleteSelector, idpDeleteDisabledSelector, remainingIdpSelector, selectedIdpsListSelector, selectedIdpsSelector, selectedIdpsLiSelector} from '../selectors';
import Cookies from 'js-cookie';
import {sortIdpList} from './utility/sortIdpList';
import {getData} from '../utility/getData';

export const matchPreviouslySelectedWithCookie = () => {
  try {
    const cookieName = JSON.parse(document.getElementById(configurationId).innerHTML).previousSelectionCookieName;
    const selectedIdps = document.querySelectorAll(selectedIdpsSelector);
    const selectedIdpList = document.querySelector(selectedIdpsListSelector);
    let cookie = Cookies.get(cookieName);
    let listMustBeSorted = false;

    if (cookie) {
      if (selectedIdps) {
        for (const idp of selectedIdps) {
          const id = getData(idp, 'entityid');

          // check if idp is in cookie, if not remove it from the list
          if (cookie && cookie.indexOf(id) === -1) {
            idp.parentElement.remove();
            listMustBeSorted = true;
          }
        }
      }

      cookie = JSON.parse(cookie);
      // check if each idp in the cookie is in the list, if not add it
      cookie.forEach(idp => {
        const id = `[data-idpkey="${idp.idp}"]`;
        const inPreviouslySelected = document.querySelector(`${selectedIdpsSelector}${id}:first-of-type`);

        if (!inPreviouslySelected) {
          const deleteButtonTemplate = document.getElementById(deleteButtonTemplateId);
          const original = document.querySelector(`${remainingIdpSelector}${id}`);
          let clone = null;
          let hasDeleteDisabledButton = null;

          // not all Idps are always in the list
          if (!!original) {
            clone = original.parentElement.cloneNode(true);
            hasDeleteDisabledButton = clone.querySelector(idpDeleteDisabledSelector);
          }

          if (!!clone) {
            clone.classList.remove('remaining');
            // disabled idps
            if (hasDeleteDisabledButton) {
              clone.querySelector(idpDeleteDisabledSelector).appendChild(deleteButtonTemplate.querySelector(idpDeleteSelector).cloneNode(true));
            }

            // non-disabled idps
            if (!hasDeleteDisabledButton) {
              clone.querySelector(`.${idpContentClass}`).appendChild(deleteButtonTemplate.content.cloneNode(true));
            }

            selectedIdpList.appendChild(clone);
            listMustBeSorted = true;
          }
        }

        if (inPreviouslySelected) {
          const count = getData(inPreviouslySelected, 'count');
          if (count !== idp.count) {
            inPreviouslySelected.setAttribute('data-count', idp.count);
          }
        }
      });

      if (listMustBeSorted) {
        const selectedIdpLis = document.querySelectorAll(selectedIdpsLiSelector);
        sortIdpList(selectedIdpLis, 'previous');
      }
    }
  } catch (e) {
    console.log(e);
  }
};
