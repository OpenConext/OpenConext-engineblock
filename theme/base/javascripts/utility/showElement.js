/**
 * Show an element by removing the hidden class.
 *
 * @param element       HTML Element    the element to show.
 * @param visuallyOnly  boolean         whether to show visually only or not
 */
export const showElement = (element, visuallyOnly = false) => {
  if (visuallyOnly) {
    element.classList.remove('visually-hidden');
    return;
  }

  element.classList.remove('hidden');
};
