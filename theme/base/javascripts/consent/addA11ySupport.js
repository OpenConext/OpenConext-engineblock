import {fireClickEvent} from '../utility/fireClickEvent';
import {animateInteractiveSections} from '../utility/animateInteractiveSections';

export const addAccessibilitySupport = () => {
  /**
   * Ensure hitting enter on a label will trigger the same interaction as it does when clicking it with a mouse.
   * This makes the tooltips / modals reachable by keyboard.
   * It also ensures the "show more info" button is reachable by keyboard.
   */
  document.querySelector('body').addEventListener('keydown', function(e) {
    if (e.keyCode === 13) {
      const label = e.target;
      if (label.tagName === 'LABEL' && label.tabIndex === 0) {
        fireClickEvent(label);
      }
    }
  });

  // make animations work for tooltips/modals/toggle
  animateInteractiveSections('.tooltip__value, .modal__value, .consent__attributes--nested');
};
