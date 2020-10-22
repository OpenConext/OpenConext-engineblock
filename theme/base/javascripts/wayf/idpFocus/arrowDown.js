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

export const arrowDown = () => {
  const searchBar = document.querySelector('.search__field');
  const defaultIdp = document.querySelector('.wayf__defaultIdpLink');
  const firstIdp = document.querySelector('.wayf__remainingIdps li:first-of-type > .wayf__idp');
  const lastIdp = document.querySelector('.wayf__remainingIdps li:last-of-type > .wayf__idp');

  if (isFocusOn(searchBar)) {
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
