import {setWeight} from './setWeight';
import {getData} from '../../utility/getData';

const PARTIAL_TITLE = 30;
const PARTIAL_ID = 20;
const PARTIAL_KEYWORD = 25;

function checkPartialMatch(searchTerm, stringToSearch, idp, weight) {
  const oldWeight = Number(getData(idp, 'weight'));
  if (stringToSearch.indexOf(searchTerm) > -1 && oldWeight < weight) {
    setWeight(idp, weight);
  }
}

export const checkPartialMatchTitle = (searchTerm, title, idp) => {
  checkPartialMatch(searchTerm, title, idp, PARTIAL_TITLE);
};

export const checkPartialMatchId = (searchTerm, id, idp) => {
  checkPartialMatch(searchTerm, id, idp, PARTIAL_ID);
};

export const checkPartialMatchKeywords = (searchTerm, keywords, idp) => {
  checkPartialMatch(searchTerm, keywords, idp, PARTIAL_KEYWORD);
};
