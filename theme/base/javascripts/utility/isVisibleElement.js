/**
 * Check if an element is visible in the DOM, as far as the display property of css is concerned.
 *
 *  Works because offsetParent returns null whenever the element or any of it's parents are hidden via the display property.
 * See: https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement.offsetParent
 *
 *  Does not work if the element has position: fixed.
 *
 * @param element
 */
export const isVisibleElement = (element) => {
  return !!element.offsetParent;
};
