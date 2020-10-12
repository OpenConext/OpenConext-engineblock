/**
 * Reset the index for all idps.  Afterwards sort the list.
 * Optionally also focuses the first list-item.
 *
 * If no list is given, it's for the remaining idps.
 * If no sort parameter is given, it's by display title.
 * If no focus parameter is given, no focus is set by default.
 *
 * @parameter list    string    the list to be sorted
 * @parameter sortBy  string    the attribute to sort by
 * @parameter focus   boolean   whether or not to focus the first item
 */
import {sortIdpList} from './sortIdpList';

const PREVIOUS = 'previous';
const REMAINING = 'remaining';

export const sortAndReindex = (list = REMAINING, sortBy = 'title', focus = false) => {
  const idpListSelector = getListSelector(list);
  const idpList = document.querySelectorAll(`${idpListSelector} > li`);

  // if there's nothing to sort: return;
  if (idpList.length < 2) {
    return;
  }

  // sort
  const idpArray = sortIdpList(idpList);

  // reindex
  idpArray.forEach((idp, index) => {
    idp.querySelector('.wayf__idp').setAttribute('data-index', String(index + 1));
  });

  // reinsert
  const ul = document.querySelector(idpListSelector);
  ul.innerHTML = convertIdpArraytoHtml(idpArray);

  if (focus) {
    idpArray[0].focus();
  }
};

function convertIdpArraytoHtml(idpArray) {
  let idpHtml = '';
  idpArray.forEach((idp, index) => {
    idp.querySelector('.wayf__idp').setAttribute('data-index', String(index + 1));
    idpHtml += idp.outerHTML;
  });

  return idpHtml;
}

function getListSelector(list) {
  if (list === PREVIOUS) {
    return '.wayf__previousSelection .wayf__idpList';
  }

  return '.wayf__remainingIdps .wayf__idpList';
}
