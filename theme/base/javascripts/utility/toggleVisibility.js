/**
 * Toggle the hidden class for an HTML element, thereby showing/hiding it.
 *
 * @param element   HTML Element    the element to toggle the visibility for.
 */
export const toggleVisibility = (element) => {
  element.classList.toggle('hidden');
};
