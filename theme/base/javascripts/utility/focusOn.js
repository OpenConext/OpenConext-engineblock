/**
 * Focus on an element with a certain query selector
 * @param selector
 */
export const focusOn = (selector) => {
  document.querySelector(selector).focus();
};
