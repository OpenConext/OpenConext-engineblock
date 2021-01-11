import {throttle} from '../../../base/javascripts/utility/throttle';

/**
 * Keyup, click and input are registered events for cross-browser compatibility with HTML5 'search' input
 *
 * @param searchBar
 * @param idpPicker
 */
export const searchBarEventListeners = (searchBar, idpPicker) => {
  searchBar.addEventListener('keyup', throttle(event => idpPicker.searchBy(event.target.value), 250));
  searchBar.addEventListener('click', event => idpPicker.searchBy(event.target.value));
  searchBar.addEventListener('input', event => idpPicker.searchBy(event.target.value));
};
