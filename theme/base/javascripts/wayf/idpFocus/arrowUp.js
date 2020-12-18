import {isFocusOn} from '../../utility/isFocusOn';
import {focusOnPreviousIdp} from './focusOnPreviousIdp';

/**
 * Behaviour expected to happen after a user presses the up arrow.
 *
 * When pressing the arrow up in the idp list we want:
 * - to go to the previous idp (general behaviour)
 * - to go to the defaultIdp notification if we are on the first idp & there is a defaultIdp
 * - to go to the searchbar if we are on the defaultIdp notification
 * - to go to the last idp if we are on the searchbar
 */
export const arrowUp = (searchBar, resetButton, defaultIdp, firstIdp, lastIdp) => {
  if (isFocusOn(searchBar) || isFocusOn(resetButton)) {
    lastIdp.focus();
    return;
  } else if (isFocusOn(firstIdp)) {
    try {
      defaultIdp.focus();
    } catch (e) {
      searchBar.focus();
    }
    return;
  } else if (isFocusOn(defaultIdp)) {
    searchBar.focus();
    return;
  }

  focusOnPreviousIdp();
};
