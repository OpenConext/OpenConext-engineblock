import {
  defaultIdpClass,
  idpClass,
  searchFieldClass,
} from '../../selectors';

/**
 * Checks whether the user is hovering above an item from the arrow navigation.
 *
 * @param element         the element being hovered
 * @returns {boolean|HTMLElement}   returns closest HTMLElement which is an arrow item if true, false otherways.
 */
export const isHoverOnArrowItem = (element) => {
  const arrowItems = [searchFieldClass, defaultIdpClass, idpClass];

  for (const className of arrowItems) {
    if (element.classList.contains(className)) {
      return element;
    }

    const closestElement = element.closest(`.${className}`);
    if (!!closestElement) {
      return closestElement;
    }
  }

  return false;
};
