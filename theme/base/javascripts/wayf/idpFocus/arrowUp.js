import {isFocusOn} from '../../utility/isFocusOn';
import {focusOnPreviousIdp} from './focusOnPreviousIdp';

/**
 * Behaviour expected to happen after a user presses the up arrow.
 *
 * When pressing the arrow up in the idp list we want:
 * - to go to the previous idp (general behaviour)
 * - to go to the eduId notification if we are on the first idp & there is an eduId
 * - to go to the searchbar if we are on the eduId notification
 * - to go to the last idp if we are on the searchbar
 */
export const arrowUp = () => {
  const searchBar = document.querySelector('.search__field');
  const eduId = document.querySelector('.wayf__eduIdLink');
  const firstIdp = document.querySelector('.wayf__remainingIdps li:first-of-type > .wayf__idp');
  const lastIdp = document.querySelector('.wayf__remainingIdps li:last-of-type > .wayf__idp');

  if (isFocusOn(searchBar)) {
    lastIdp.focus();
    return;
  } else if (isFocusOn(firstIdp)) {
    try {
      eduId.focus();
    } catch (e) {
      searchBar.focus();
    }
    return;
  } else if (isFocusOn(eduId)) {
    searchBar.focus();
    return;
  }

  focusOnPreviousIdp();
};
