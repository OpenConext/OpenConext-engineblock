import {nodeListToArray} from '../utility/nodeListToArray';
import {changeAriaHiddenValue} from '../utility/changeAriaHiddenValue';

/**
 * Change aria-hidden value as the element is clicked.
 * This ensures that tooltips and modals are not read on load, but only read when clicked open.
 *
 * @param elementSelectors
 */
export const addTooltipAndModalAriaHandlers = (elementSelectors
) => {
  const elements = nodeListToArray(document.querySelectorAll(elementSelectors));

  elements.forEach(element => {
    const forValue = element.getAttribute('for');
    const alert = document.querySelector(`[data-for="${forValue}"]`);

    if (!!alert) {
      element.addEventListener('click', () => {
        changeAriaHiddenValue(alert);
      });
    }
  });
};
