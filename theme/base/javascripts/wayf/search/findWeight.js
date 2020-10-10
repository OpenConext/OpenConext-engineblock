import {getData} from '../../utility/getData';
import {setWeight} from './setWeight';
import {
  checkPartialMatchId,
  checkPartialMatchKeywords,
  checkPartialMatchTitle
} from './checkPartialMatch';

const EXACT_TITLE = 100;
const EXACT_ID = 60;
const EXACT_KEYWORD = 60;

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
 * @param     idpArray        array of idp nodes
 * @param     searchTerm     string
 */
export const findWeight = (idpArray, searchTerm) => {
  idpArray.forEach(li => {
    const idp = li.children[0];
    const title = getData(idp, 'title');
    if (title === searchTerm) {
      console.log('exact match on title');
      setWeight(idp, EXACT_TITLE);
      return;
    }

    const entityId = getData(idp, 'entityid');
    if (entityId === searchTerm) {
      console.log('exact match on entityId');
      setWeight(idp, EXACT_ID);
      return;
    }

    const keywords = getData(idp, 'keywords');
    if (keywords.split('|').includes(searchTerm)) {
      console.log('exact match on keywords');
      setWeight(idp, EXACT_KEYWORD);
      return;
    }

    checkPartialMatchTitle(searchTerm, title, idp);
    checkPartialMatchId(searchTerm, entityId, idp);
    checkPartialMatchKeywords(searchTerm, keywords, idp);

    const weight = getData(idp, 'weight');
    if (typeof weight !== 'string') setWeight(idp, 0);
  });
};
