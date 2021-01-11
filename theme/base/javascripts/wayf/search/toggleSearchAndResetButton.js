import {hideElement} from "../../utility/hideElement";
import {showElement} from "../../utility/showElement";
import {searchAndSortIdps} from "./searchAndSortIdps";
import {searchResetSelector, searchSubmitSelector} from '../../selectors';

export const toggleSearchAndResetButton = (idpArray, searchTerm) => {
  const searchButton = document.querySelector(searchSubmitSelector);
  const resetButton = document.querySelector(searchResetSelector);
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
