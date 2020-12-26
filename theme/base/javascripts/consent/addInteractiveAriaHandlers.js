import {nodeListToArray} from '../utility/nodeListToArray';

/**
 * Change aria-hidden value as the element is clicked.
 * This ensures that tooltips and modals are not read on load, but only read when clicked open.
 *
 * @param elementSelectors
 */
export const addInteractiveAriaHandlers = (elementSelectors
) => {
  const elements = nodeListToArray(document.querySelectorAll(elementSelectors));

  elements.forEach(element => {
    const forValue = element.getAttribute('for');
    const alert = document.querySelector(`[data-for="${forValue}"]`);

    element.addEventListener('click', () => {
      if(alert.getAttribute('aria-hidden')) {
        alert.removeAttribute('aria-hidden');
        return;
      }

      alert.setAttribute('aria-hidden', 'true');
    });
  });
};
