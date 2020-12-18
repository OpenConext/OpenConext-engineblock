/**
 * Hide an element by adding the hidden class.
 *
 * @param element       HTML Element    the element to hide.
 * @param visuallyOnly  boolean         whether to hide visually only or not
 */
export const hideElement = (element, visuallyOnly = false) => {
  if (visuallyOnly) {
    element.classList.add('visually-hidden');
    return;
  }

  element.classList.add('hidden');
};
