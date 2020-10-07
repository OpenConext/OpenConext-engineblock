import {isFocusOn} from '../../utility/isFocusOn';
import {focusOnNextIdp} from './focusOnNextIdp';

/**
 * Behaviour expected to happen after a user presses the down arrow.
 */
export const arrowDown = () => {
  const searchBar = document.querySelector('.search__field');
  const eduId = document.querySelector('.wayf__eduIdLink');
  const firstIdp = document.querySelector('.wayf__remainingIdps li:first-of-type > .wayf__idp');
  const lastIdp = document.querySelector('.wayf__remainingIdps li:last-of-type > .wayf__idp');

  if (isFocusOn(searchBar)) {
    eduId.focus();
    return;
  } else if (isFocusOn(eduId)) {
    firstIdp.focus();
    return;
  } else if (isFocusOn(lastIdp)) {
    searchBar.focus();
    return;
  }

  focusOnNextIdp();
};
