import {throttle} from '../utility/throttle';
import {nodeListToArray} from '../utility/nodeListToArray';
import {searchAndSortIdps} from './search/searchAndSortIdps';
import {toggleDefaultIdPLinkVisibility} from "./search/toggleDefaultIdPLinkVisibility";
import {toggleSearchAndResetButton} from "./search/toggleSearchAndResetButton";
import {remainingIdpLiSelector, searchFieldSelector, searchResetSelector} from '../selectors';
import {focusAndSmoothScroll} from '../utility/focusAndSmoothScroll';

export const searchBehaviour = () => {
  const idpList = document.querySelectorAll(remainingIdpLiSelector);
  const searchBar = document.querySelector(searchFieldSelector);
  const resetButton = document.querySelector(searchResetSelector);
  const idpArray = nodeListToArray(idpList);
  let previousSearchTerm = '';

  // attach handler to search field
  searchBar.addEventListener('keyup', throttle(event => searchHandler(idpArray, event.target.value), 250));
  searchBar.addEventListener('keyup', event => toggleDefaultIdPLinkVisibility(event.target.value));
  searchBar.addEventListener('keyup',  event => toggleSearchAndResetButton(idpArray, event.target.value));
  searchBar.addEventListener('click', event => searchHandler(idpArray, event.target.value));
  searchBar.addEventListener('input', event => searchHandler(idpArray, event.target.value));

  resetButton.addEventListener('click', () => {
    toggleSearchAndResetButton(idpArray, '');
    focusAndSmoothScroll(searchBar);
    previousSearchTerm = '';
  });
  // attach handler to search form
  document.querySelector('.wayf__search').addEventListener('submit', event => {
    event.preventDefault();
  });

  function searchHandler(idpArray, searchTerm) {
    if (searchTerm === previousSearchTerm) {
      return;
    }

    searchAndSortIdps(idpArray, event.target.value);
    previousSearchTerm = searchTerm;
  }
};

