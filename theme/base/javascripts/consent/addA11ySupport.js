import {fireClickEvent} from '../utility/fireClickEvent';
import {handleAriaPressed} from '../utility/handleAriaPressed';

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

  /**
   * Ensure that for people with a screenreader the aria-pressed status is updated.
   * This ensures content in a tooltip / modal is actually announced to them upon opening the tooltip / modal.
   */
  const labels = document.querySelectorAll('label[aria-pressed]');
  handleAriaPressed(labels);
};
