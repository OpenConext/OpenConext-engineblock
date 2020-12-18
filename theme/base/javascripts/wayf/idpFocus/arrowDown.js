import {isFocusOn} from '../../utility/isFocusOn';

/**
 * Behaviour expected to happen after a user presses the down arrow.
 *
 * When pressing the arrow down in the idp list we want:
 * - to go to the next idp (general behaviour)
 * - to go to the defaultIdp notification if we are on the searchbar & there is an defaultIdp
 * - to go to the first idp if we are on the defaultIdp
 * - to go to the searchbar if we are on the last Idp
 */
import {focusOnNextIdp} from './focusOnNextIdp';

export const arrowDown = (searchBar, resetButton, defaultIdp, firstIdp, lastIdp) => {
  if (isFocusOn(searchBar) || isFocusOn(resetButton)) {
    try {
      defaultIdp.focus();
    } catch (e) {
      firstIdp.focus();
    }
    return;
  } else if (isFocusOn(defaultIdp)) {
    firstIdp.focus();
    return;
  } else if (isFocusOn(lastIdp)) {
    searchBar.focus();
    return;
  }

  focusOnNextIdp();
};
