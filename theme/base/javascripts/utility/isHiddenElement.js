/**
 * Determine whether an element has the visible class
 *
 * @param element
 * @returns {boolean}
 */
export const isHiddenElement = (element) => {
  return element.classList.contains('hidden');
};
