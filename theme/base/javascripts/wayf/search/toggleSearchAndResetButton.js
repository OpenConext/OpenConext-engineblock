import {hideElement} from "../../utility/hideElement";
import {showElement} from "../../utility/showElement";

export const toggleSearchAndResetButton = (searchTerm) => {
  const searchButton = document.querySelector('.search__submit');
  const resetButton = document.querySelector('.search__reset');
  if (resetButton.classList.contains('visually-hidden')) {
    resetButton.classList.remove('visually-hidden');
  }

  if (searchTerm !== '') {
    hideElement(searchButton);
    showElement(resetButton);
  } else {
    showElement(searchButton);
    hideElement(resetButton);
  }
};
