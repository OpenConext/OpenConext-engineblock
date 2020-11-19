import {throttle} from '../utility/throttle';
import {nodeListToArray} from '../utility/nodeListToArray';
import {searchAndSortIdps} from './search/searchAndSortIdps';
import {toggleDefaultIdPLinkVisibility} from "./search/toggleDefaultIdPLinkVisibility";
import {toggleSearchAndResetButton} from "./search/toggleSearchAndResetButton";

export const searchBehaviour = () => {
  const idpList = document.querySelectorAll('.wayf__remainingIdps .wayf__idpList > li');
  const searchBar = document.querySelector('.search__field');
  const resetButton = document.querySelector('.search__reset');
  const idpArray = nodeListToArray(idpList);

  // attach handler to search field
  searchBar.addEventListener('keyup', throttle(event => searchAndSortIdps(idpArray, event.target.value), 250));
  searchBar.addEventListener('keyup', event => toggleDefaultIdPLinkVisibility(event.target.value));
  searchBar.addEventListener('keyup',  event => toggleSearchAndResetButton(idpArray, event.target.value));
  searchBar.addEventListener('click', event => searchAndSortIdps(idpArray, event.target.value));
  searchBar.addEventListener('input', event => searchAndSortIdps(idpArray, event.target.value));

  resetButton.addEventListener('click', event => toggleSearchAndResetButton(idpArray, ''));
  // attach handler to search form
  document.querySelector('.wayf__search').addEventListener('submit', event => {
    event.preventDefault();
    searchAndSortIdps(idpArray, event.target.value);
  });
};
