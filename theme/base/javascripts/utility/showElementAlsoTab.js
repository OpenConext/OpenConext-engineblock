import {showElement} from './showElement';

/**
 * Show an element by removing the hidden class.
 *
 * @param element       HTML Element    the element to show.
 * @param visuallyOnly  boolean         whether to show visually only or not
 */
export const showElementAlsoTab = (element, visuallyOnly = false) => {
  element.removeAttribute('tabindex');
  showElement(element, visuallyOnly);
};
