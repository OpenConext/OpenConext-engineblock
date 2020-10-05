/**
 * Show an element by removing the hidden class.
 *
 * @param element   HTML Element    the element to toggle the visibility for.
 */
export const showElement = (element) => {
  element.classList.remove('hidden');
};
