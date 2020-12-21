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
  if (element.className === idpDeleteDisabledClass) {
    const idpDelete = element.querySelector(idpDeleteSelector);
    if (!Boolean(idpDelete)) {
      return false;
    }

    return isVisibleElement(idpDelete);
  }

  if (element.className === idpDeleteClass) {
    return isVisibleElement(element);
  }

  return false;
};
