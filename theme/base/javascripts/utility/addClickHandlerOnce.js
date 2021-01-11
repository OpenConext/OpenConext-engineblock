import {getData} from './getData';

/**
 * Ensure a click handler is only added once to the element.
 *
 * @param elementSelector
 * @param callback
 */
export const addClickHandlerOnce = (elementSelector, callback) => {
  const element = document.querySelector(elementSelector);
  const hasClickHandler = getData(element, 'clickhandled');

  // if clickHandler's already been attached, do not attach it again
  if (hasClickHandler) {
    return;
  }

  element.addEventListener('click', callback);
  element.setAttribute('data-clickhandled', true);
};
