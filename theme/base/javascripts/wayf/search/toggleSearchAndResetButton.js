import {hideElement} from "../../utility/hideElement";
import {showElement} from "../../utility/showElement";
import {searchAndSortIdps} from "./searchAndSortIdps";

export const toggleSearchAndResetButton = (idpArray, searchTerm) => {
  const searchButton = document.querySelector('.search__submit');
  const resetButton = document.querySelector('.search__reset');

  if (searchTerm !== '') {
    hideElement(searchButton);
    showElement(resetButton);
  } else {
    // Reset the list/search results
    searchAndSortIdps(idpArray, searchTerm);
    showElement(searchButton);
    hideElement(resetButton);
  }
};
