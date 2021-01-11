import {arrowDown} from './idpFocus/arrowDown';
import {arrowUp} from './idpFocus/arrowUp';
import {handleEnter} from './handleEnter';
import {
  defaultIdpSelector,
  firstRemainingIdpSelector, lastRemainingIdpSelector,
  searchFieldSelector,
  searchResetSelector
} from '../selectors';

/**
 * All handlers for the expected keyboard behaviour.
 * Grouped by key.
 */
export const keyboardBehaviour = (previouslySelectedIdps) => {
  const ENTER      = 13;
  const ARROW_UP   = 38;
  const ARROW_DOWN = 40;
  const searchBar = document.querySelector(searchFieldSelector);
  const resetButton = document.querySelector(searchResetSelector);
  const defaultIdp = document.querySelector(defaultIdpSelector);
  const firstIdp = document.querySelector(firstRemainingIdpSelector);
  const lastIdp = document.querySelector(lastRemainingIdpSelector);


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
