import {hideElement} from './hideElement';

/**
 * Hide an element both visually and from keyboard users (taborder).
 *
 * @param element       HTML Element    the element to hide.
 * @param visuallyOnly  boolean         whether to hide visually only or not
 */
export const hideElementNoTab = (element, visuallyOnly = false) => {
  element.setAttribute('tabindex', '-1');
  hideElement(element, visuallyOnly);
};
