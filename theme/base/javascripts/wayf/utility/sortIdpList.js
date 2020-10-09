import {sortByTitle} from './sortIdpMethods';

const TITLE = 'title';

/**
 * Sort an idpList.  By default it's by displayTitle.
 * No other sorts exist atm, but this is anticipated once the search is implemented.
 *
 * @param idpList   NodeList    the list to sort
 * @param sortBy    string      the attribute to sort by
 *
 * @returns   Node[]
 */
export const sortIdpList = (idpList, sortBy = TITLE) => {
  // so we can sort it easily
  const idpArray = nodeListToArray(idpList);
  idpArray.sort(sortByTitle);

  return idpArray;
};

/**
 * Converts a nodeList to an array
 *
 * @param list            a nodelist
 *
 * @returns Node[]        an array of nodes
 */
function nodeListToArray(list) {
  return [].slice.call(list, 0);
}
