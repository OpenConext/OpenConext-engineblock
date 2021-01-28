import {setWeight} from './setWeight';
import {findWeight} from './findWeight';

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
 * If a perfect match is found, the search stops for that idp.
 *
 * NOTE: please also adjust the tests when changing the weights.
 *
 * @param     idpArray        array of idp nodes
 * @param     searchTerm     string
 */
export const assignWeight = (idpArray, searchTerm) => {
  idpArray.forEach(li => {
    const idp = li.children[0];
    const searchTerms = searchTerm.trim().split(' ');
    let weight = 0;

    // first search on the entire string in one go
    weight += findWeight(idp, searchTerm.trim());

    // second search on each space-separated substring
    if(searchTerms.length > 1) {
      searchTerms.forEach(searchTerm => {
        weight += findWeight(idp, searchTerm);
      });
    }

    setWeight(li, weight);
    setWeight(idp, weight);
  });
};
