const EXACT_TITLE = 100;
const EXACT_ID = 60;
const EXACT_KEYWORD = 60;
const EXACT_PART_OF_TITLE = 40;

function checkFullStringMatch(searchTerm, stringToSearch, weight) {
  if (stringToSearch === searchTerm) {
    return weight;
  }

  return 0;
}

function checkFullArrayOfStringsMatch(searchTerm, stringToSearch, separator, weight) {
  if (stringToSearch.trim().split(separator).includes(searchTerm)) {
    return weight;
  }

  return 0;
}

export function checkFullTitleMatch(searchTerm, title) {
  return checkFullStringMatch(searchTerm, title, EXACT_TITLE);
}

export function checkFullEntityIdMatch(searchTerm, entityId) {
  return checkFullStringMatch(searchTerm, entityId, EXACT_ID);
}

export function checkFullKeywordMatch(searchTerm, keywords) {
  return checkFullArrayOfStringsMatch(searchTerm, keywords, '|', EXACT_KEYWORD);
}

export function checkFullPartOfTitleMatch(searchTerm, title) {
  return checkFullArrayOfStringsMatch(searchTerm, title, ' ', EXACT_PART_OF_TITLE);
}
