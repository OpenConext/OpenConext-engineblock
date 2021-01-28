import {
  defaultIdpClass,
  idpClass,
  searchFieldClass,
} from '../../selectors';

/**
 * Checks whether an item from the arrow navigation has focus.
 *
 * @param element
 * @returns {boolean}
 */
export const isFocusOnArrowItem = (element) => {
  const arrowItems = [searchFieldClass, defaultIdpClass, idpClass];

  for (const className of arrowItems) {
    if (element.classList.contains(className)) {
      return true;
    }
  }

  return false;
};
