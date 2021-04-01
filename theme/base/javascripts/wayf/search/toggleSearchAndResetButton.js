import {searchAndSortIdps} from "./searchAndSortIdps";
import {searchAnnouncementId, searchResetSelector, searchSubmitSelector} from '../../selectors';
import {toggleDefaultIdPLinkVisibility} from './toggleDefaultIdPLinkVisibility';
import {hideElementNoTab} from '../../utility/hideElementNoTab';
import {showElementAlsoTab} from '../../utility/showElementAlsoTab';

export const toggleSearchAndResetButton = (idpArray, searchTerm) => {
  const searchButton = document.querySelector(searchSubmitSelector);
  const resetButton = document.querySelector(searchResetSelector);
  const searchAnnouncementDiv = document.getElementById(searchAnnouncementId);

  if (resetButton.classList.contains('visually-hidden')) {
    showElementAlsoTab(resetButton, true);
  }

  if (searchTerm !== '') {
    hideElementNoTab(searchButton, true);
    showElementAlsoTab(resetButton, true);
  } else {
    toggleDefaultIdPLinkVisibility('');
    showElementAlsoTab(searchButton, true);
    hideElementNoTab(resetButton, true);
    // Reset the list/search results
    searchAndSortIdps(idpArray, searchTerm);
    searchAnnouncementDiv.innerHTML = '';
  }
};
