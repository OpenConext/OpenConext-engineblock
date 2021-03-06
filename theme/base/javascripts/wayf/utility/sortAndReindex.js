import {sortIdpList} from './sortIdpList';
import {reinsertIdpList} from './reinsertIdpList';
import {idpSelector, remainingIdpListSelector, selectedIdpsListSelector} from '../../selectors';

const PREVIOUS = 'previous';
const REMAINING = 'remaining';

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
export const sortAndReindex = (list = REMAINING, sortBy = 'title', focus = false) => {
  let idpListSelector = remainingIdpListSelector;

  if (list === PREVIOUS) {
    idpListSelector = selectedIdpsListSelector;
  }

  let idpList = document.querySelectorAll(`${idpListSelector} > li`);

  // if there's nothing to sort: return;
  if (idpList.length < 2) {
    return;
  }

  // sort
  const idpArray = sortIdpList(idpList, list);

  // reindex
  idpArray.forEach((idp, index) => {
    idp.querySelector(idpSelector).setAttribute('data-index', String(index + 1));
  });

  // reinsert
  reinsertIdpList(idpArray, idpListSelector);

  if (focus) {
    idpArray[0].focus();
  }
};
