import {nodeListToArray} from '../utility/nodeListToArray';

/** TODO figure out if this is still needed **/
export const addInteractiveAriaHandlers = (elementType) => {
  const elements = nodeListToArray(document.querySelectorAll(`.${elementType}__value`));

  elements.forEach(element => {
    const input = element.parentElement.querySelector(`input.${elementType}`);

    // set aria-hidden as the element is hidden on startup
    element.setAttribute('aria-hidden', 'true');

    // change aria-hidden value each time the input is clicked
    input.addEventListener('change', () => {
      if (element.getAttribute('aria-hidden')) {
        element.removeAttribute('aria-hidden');
        return;
      }

      element.setAttribute('aria-hidden', 'true');
    });
  });
};
