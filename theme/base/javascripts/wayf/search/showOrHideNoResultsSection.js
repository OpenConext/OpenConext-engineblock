import {showElement} from '../../utility/showElement';
import {hideElement} from '../../utility/hideElement';

/**
 * Shows / hides the no-results section depending on whether or not there are search results.
 *
 * @param idpArray
 */
export const showOrHideNoResultsSection = (idpArray) => {
  const noResultsSection = document.querySelector('.wayf__noResults');
  const noMatches = document.querySelectorAll('.wayf__remainingIdps .wayf__idp[data-weight="0"]');
  if (noMatches.length === idpArray.length) {
    showElement(noResultsSection);
  } else {
    hideElement(noResultsSection);
  }
};
