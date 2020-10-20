import {fireClickEvent} from '../utility/fireClickEvent';
import {animateInteractiveSections} from '../utility/animateInteractiveSections';

export const addAccessibilitySupport = () => {
  const ENTER      = 13;

  /**
   * Ensure hitting enter on a label will trigger the same interaction as it does when clicking it with a mouse.
   * This makes the tooltips / modals reachable by keyboard.
   * It also ensures the "show more info" button is reachable by keyboard.
   */
  document.querySelector('body').addEventListener('keydown', function(e) {
    if (e.keyCode === ENTER) {
      const target = e.target;
      console.log(e.target);
      if (target.tagName === 'LABEL' && target.tabIndex === 0) {
        fireClickEvent(target);
      }

      if (target.tagName === 'DIV' && target.tabIndex === 0) {
        fireClickEvent(target.querySelector('label.modal'));
      }
    }
  });

  // make animations work for tooltips/modals/toggle
  animateInteractiveSections('.tooltip__value, .modal__value, .consent__attributes--nested');
};
