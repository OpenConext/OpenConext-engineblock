const PARTIAL_TITLE = 30;
const PARTIAL_ID = 20;
const PARTIAL_KEYWORD = 25;

function checkPartialMatch(searchTerm, stringToSearch, weight) {
  if (stringToSearch.indexOf(searchTerm) > -1) {
    return weight;
  }

  return 0;
}

export const checkPartialMatchTitle = (searchTerm, title) => {
  return checkPartialMatch(searchTerm, title, PARTIAL_TITLE);
};

export const checkPartialMatchId = (searchTerm, id) => {
  return checkPartialMatch(searchTerm, id, PARTIAL_ID);
};

export const checkPartialMatchKeywords = (searchTerm, keywords) => {
  return checkPartialMatch(searchTerm, keywords, PARTIAL_KEYWORD);
};
