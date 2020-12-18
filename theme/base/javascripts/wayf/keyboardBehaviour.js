import {arrowDown} from './idpFocus/arrowDown';
import {arrowUp} from './idpFocus/arrowUp';
import {handleEnter} from './handleEnter';

/**
 * All handlers for the expected keyboard behaviour.
 * Grouped by key.
 */
export const keyboardBehaviour = (previouslySelectedIdps) => {
  const ENTER      = 13;
  const ARROW_UP   = 38;
  const ARROW_DOWN = 40;
  const searchBar = document.querySelector('.search__field');
  const resetButton = document.querySelector('.search__reset');
  const defaultIdp = document.querySelector('.wayf__defaultIdpLink');
  const firstIdp = document.querySelector('.wayf__remainingIdps li:first-of-type > .wayf__idp');
  const lastIdp = document.querySelector('.wayf__remainingIdps li:last-of-type > .wayf__idp');


  document.querySelector('body').addEventListener('keydown', function(e) {
    if (e.keyCode === ENTER) {
      handleEnter(e, previouslySelectedIdps);
      return;
    }

    if (e.keyCode === ARROW_DOWN) {
      arrowDown(searchBar, resetButton, defaultIdp, firstIdp, lastIdp);
      return;
    }

    if (e.keyCode === ARROW_UP) {
      arrowUp(searchBar, resetButton, defaultIdp, firstIdp, lastIdp);
      return;
    }
  });
};
