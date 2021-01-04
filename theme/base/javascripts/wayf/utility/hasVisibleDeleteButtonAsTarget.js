import {isVisibleElement} from '../../utility/isVisibleElement';
import {idpDeleteClass, idpDeleteDisabledClass, idpDeleteSelector} from '../../selectors';

/**
 * Check if an element has a visible delete button.
 * Visible is defined as not hidden via the display property.
 *
 * @param element
 * @returns {boolean}
 */
export const hasVisibleDeleteButtonAsTarget = (element) => {
  if (element.classList.contains(idpDeleteDisabledClass)) {
    const idpDelete = element.querySelector(idpDeleteSelector);
    if (!Boolean(idpDelete)) {
      return false;
    }

    return isVisibleElement(idpDelete);
  }

  if (element.classList.contains(idpDeleteClass)) {
    return isVisibleElement(element);
  }

  return false;
};
