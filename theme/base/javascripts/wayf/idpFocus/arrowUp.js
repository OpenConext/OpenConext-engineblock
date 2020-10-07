import {isFocusOn} from '../../utility/isFocusOn';
import {focusOnPreviousIdp} from './focusOnPreviousIdp';

/**
 * Behaviour expected to happen after a user presses the up arrow.
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
    eduId.focus();
    return;
  } else if (isFocusOn(eduId)) {
    searchBar.focus();
    return;
  }

  focusOnPreviousIdp();
};
