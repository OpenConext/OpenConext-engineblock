import {showElement} from '../../utility/showElement';
import {hideElement} from '../../utility/hideElement';
import {noMatchSelector, noResultSectionSelector, searchAnnouncementId} from '../../selectors';
import {getData} from '../../utility/getData';

/**
 * Shows / hides the no-results section depending on whether or not there are search results.
 *
 * @param idpArray
 */
export const showOrHideNoResultsSection = (idpArray) => {
  const noResultsSection = document.querySelector(noResultSectionSelector);
  const searchAnnouncementDiv = document.getElementById(searchAnnouncementId);
  const noMatches = document.querySelectorAll(noMatchSelector);
  if (noMatches.length === idpArray.length) {
    showElement(noResultsSection);
    noResultsSection.innerHTML = noResultsSection.innerHTML;
    searchAnnouncementDiv.innerHTML = '';
  } else {
    hideElement(noResultsSection);
    searchAnnouncementDiv.innerHTML = getAnnouncementText(searchAnnouncementDiv, (idpArray.length - noMatches.length));
  }
};

function getAnnouncementText(element, amount) {
  if (amount > 1) {
    return `${amount}${getData(element, 'announcementMultiple')}`;
  }

  return `${amount}${getData(element, 'announcementOne')}`;
}
