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

  document.querySelector('body').addEventListener('keydown', function(e) {
    if (e.keyCode === ENTER) {
      handleEnter(e, previouslySelectedIdps);
      return;
    }

    if (e.keyCode === ARROW_DOWN) {
      arrowDown();
      return;
    }

    if (e.keyCode === ARROW_UP) {
      arrowUp();
      return;
    }
  });
};
