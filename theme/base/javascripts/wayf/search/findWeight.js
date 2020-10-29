import {
  checkPartialMatchId,
  checkPartialMatchKeywords,
  checkPartialMatchTitle
} from './checkPartialMatch';
import {
  checkFullEntityIdMatch,
  checkFullKeywordMatch, checkFullPartOfTitleMatch,
  checkFullTitleMatch
} from './checkFullMatch';
import {getData} from '../../utility/getData';

export const findWeight = (idp, searchTerm) => {
  let weight = 0;

  const title = getData(idp, 'title');
  weight += checkFullTitleMatch(searchTerm, title);
  if (weight > 0) return weight;

  const entityId = getData(idp, 'entityid').toLowerCase();
  weight += checkFullEntityIdMatch(searchTerm, entityId);
  if (weight > 0) return weight;

  const keywords = getData(idp, 'keywords');
  weight += checkFullKeywordMatch(searchTerm, keywords);
  if (weight > 0) return weight;

  weight += checkFullPartOfTitleMatch(searchTerm, title);
  weight += checkPartialMatchTitle(searchTerm, title);
  weight += checkPartialMatchId(searchTerm, entityId);
  weight += checkPartialMatchKeywords(searchTerm, keywords);

  return Math.round(weight / 3);
};
