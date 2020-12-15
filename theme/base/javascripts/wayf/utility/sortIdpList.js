import {sortByCount, sortByTitle} from './sortIdpMethods';
import {nodeListToArray} from '../../utility/nodeListToArray';
import {sortArrayList} from './sortArrayList';

/**
 * Sort an idpList.  By default it's by displayTitle.
 * No other sorts exist atm, but this is anticipated once the search is implemented.
 *
 * @param idpList   NodeList    the list to sort
 * @param list      string      the list to be sorted
 * @returns   Node[]
 */
export const sortIdpList = (idpList, list) => {
  // so we can sort it easily
  const idpArray = nodeListToArray(idpList);

  if (list === 'previous') {
    return sortArrayList(idpArray, sortByCount);
  }

  return sortArrayList(idpArray, sortByTitle);
};
