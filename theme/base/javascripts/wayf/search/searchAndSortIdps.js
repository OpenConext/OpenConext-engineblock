import {sortByTitle, sortByWeight} from '../utility/sortIdpMethods';
import {assignWeight} from './assignWeight';
import {clearWeight} from './clearWeight';
import {showOrHideNoResultsSection} from './showOrHideNoResultsSection';
import {reinsertIdpList} from '../utility/reinsertIdpList';
import {showFoundIdpsWhenCutoff} from './showFoundIdpsWhenCutoff';
import {hideIdpsWhenCutoffNoSearch} from './hideIdpsWhenCutoffNoSearch';
import {sortArrayList} from '../utility/sortArrayList';

/**
 * Searches an array of idps for a given searchTerm.
 * It tries to match the search term to:
 * - title
 * - entityId
 * - keywords
 *
 * A weight is set for each entityId according to how well it matches:
 * - perfect match: title = 100, id & keyword = 60
 * - partial match: title = 30, id = 20, keyword = 25
 *
 * If a perfect match is found, the search stops for that idp.
 * Afterwards the idpArray is sorted according to weight, with title being the secondary attribute to sort on in case of a tie.
 *
 * @param     idpArray        array of idp nodes
 * @param     searchTerm     string
 * @returns   node[]
 */
export const searchAndSortIdps = (idpArray, searchTerm) => {
  const list = document.querySelector('.wayf__remainingIdps .wayf__idpList--cutoffMet');

  // reset weights by removing them
  clearWeight(idpArray);

  if (typeof searchTerm !== 'undefined' && searchTerm.length) {
    assignWeight(idpArray, searchTerm.toLowerCase());
    idpArray = sortArrayList(idpArray, sortByWeight);
    showFoundIdpsWhenCutoff(list);
  } else {
    idpArray = sortArrayList(idpArray, sortByTitle);
    hideIdpsWhenCutoffNoSearch(list);
  }

  // add the sorted items to the dom, replacing the previous ones
  reinsertIdpList(idpArray);

  // show or hide no results section as appropriate
  showOrHideNoResultsSection(idpArray);
};

