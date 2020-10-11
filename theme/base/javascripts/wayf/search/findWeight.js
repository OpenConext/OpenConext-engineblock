import {getData} from '../../utility/getData';
import {
  checkPartialMatchId,
  checkPartialMatchKeywords,
  checkPartialMatchTitle
} from './checkPartialMatch';

const EXACT_TITLE = 100;
const EXACT_ID = 60;
const EXACT_KEYWORD = 60;

export const findWeight = (idp, searchTerm) => {
  const title = getData(idp, 'title');
  let weight = 0;

  if (title === searchTerm) {
    return EXACT_TITLE;
  }

  const entityId = getData(idp, 'entityid');
  if (entityId === searchTerm) {
    return EXACT_ID;
  }

  const keywords = getData(idp, 'keywords');
  if (keywords.split('|').includes(searchTerm)) {
    return EXACT_KEYWORD;
  }

  weight += checkPartialMatchTitle(searchTerm, title);
  weight += checkPartialMatchId(searchTerm, entityId);
  weight += checkPartialMatchKeywords(searchTerm, keywords);

  return Math.round(weight / 3);
};
