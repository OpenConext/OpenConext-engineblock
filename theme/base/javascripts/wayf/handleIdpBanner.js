import {
  defaultIdpId,
  remainingIdpSelector,
  searchFieldSelector,
  searchResetSelector,
  searchSubmitSelector
} from '../selectors';
import {getData} from '../utility/getData';
import {hideElement} from '../utility/hideElement';
import {showElement} from '../utility/showElement';

export const handleIdpBanner = async (event) => {
  event.preventDefault();

  const searchField = document.querySelector(searchFieldSelector);
  const defaultIdp = document.getElementById(defaultIdpId);
  const defaultIdpTitle = getData(defaultIdp, 'title');
  const remainingIdps = document.querySelectorAll(remainingIdpSelector);
  const searchButton = document.querySelector(searchSubmitSelector);
  const resetButton = document.querySelector(searchResetSelector);

  for (const idp of remainingIdps) {
    if(getData(idp, 'title').localeCompare(defaultIdpTitle) !== 0) {
      idp.setAttribute('data-weight', '0');
    }
  }
  searchField.value = defaultIdpTitle;
  defaultIdp.focus();
  hideElement(searchButton, true);
  showElement(resetButton, true);
};
