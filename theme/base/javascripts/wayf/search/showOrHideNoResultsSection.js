import {showElement} from '../../utility/showElement';
import {hideElement} from '../../utility/hideElement';
import {noMatchSelector, noResultSectionSelector} from '../../selectors';

/**
 * Shows / hides the no-results section depending on whether or not there are search results.
 *
 * @param idpArray
 */
export const showOrHideNoResultsSection = (idpArray) => {
  const noResultsSection = document.querySelector(noResultSectionSelector);
  const noMatches = document.querySelectorAll(noMatchSelector);
  if (noMatches.length === idpArray.length) {
    showElement(noResultsSection);
  } else {
    hideElement(noResultsSection);
  }
};
