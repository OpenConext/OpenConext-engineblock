import {sortIdpList} from './sortIdpList';
import {getListSelector} from './getListSelector';

/**
 * Sort all idps by title.
 *
 * If no list is given, it's for the remaining idps.
 *
 * @parameter listSelector    string    the css selector for the list
 * @parameter list            string    the list to be sorted
 */
export const sortIdps = (listSelector, list = 'remaining', ) => {
  const idpListSelector = getListSelector(list);
  const idpList = document.querySelectorAll(`${idpListSelector} > li`);

  // if there's nothing to sort: return;
  if (idpList.length < 2) {
    return;
  }

  // sort
  return sortIdpList(idpList);
};

/**
 * Sorts the remaining idps by display title
 */
export function sortRemaining() {
  sortIdps(getListSelector());
}

/**
 * Sorts the previous idps by display title
 */
export function sortPrevious() {
  sortIdps(getListSelector('previous'));
}
