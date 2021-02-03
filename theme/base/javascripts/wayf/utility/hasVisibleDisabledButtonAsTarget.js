import {isVisibleElement} from '../../utility/isVisibleElement';
import {idpDisabledClass, idpDisabledSelector} from '../../selectors';

/**
 * Checks if an element has a visible disable button.
 * Visible is defined as not hidden via the display property.
 *
 * @param element
 * @returns {boolean}
 */
export const hasVisibleDisabledButtonAsTarget = (element) => {
  if (element.classList.contains(idpDisabledClass)) {
    return isVisibleElement(element);
  }

  const idpDisabled = element.querySelector(idpDisabledSelector);
  if (!Boolean(idpDisabled)) {
    return false;
  }

  return isVisibleElement(idpDisabled);
};
