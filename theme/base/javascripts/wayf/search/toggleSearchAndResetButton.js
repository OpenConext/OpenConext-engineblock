import {hideElement} from "../../utility/hideElement";
import {showElement} from "../../utility/showElement";
import {searchAndSortIdps} from "./searchAndSortIdps";

export const toggleSearchAndResetButton = (idpArray, searchTerm) => {
  const searchButton = document.querySelector('.search__submit');
  const resetButton = document.querySelector('.search__reset');
  if (resetButton.classList.contains('visually-hidden')) {
    showElement(resetButton, true);
  }

  if (searchTerm !== '') {
    hideElement(searchButton, true);
    showElement(resetButton, true);
  } else {
    // Reset the list/search results
    searchAndSortIdps(idpArray, searchTerm);
    showElement(searchButton, true);
    hideElement(resetButton, true);
  }
};
