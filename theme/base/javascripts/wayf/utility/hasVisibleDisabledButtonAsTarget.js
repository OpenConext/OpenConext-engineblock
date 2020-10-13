import {isVisibleElement} from '../../utility/isVisibleElement';

/**
 * Checks if an element has a visible disable button.
 * Visible is defined as not hidden via the display property.
 *
 * @param element
 * @returns {boolean}
 */
export const hasVisibleDisabledButtonAsTarget = (element) => {
  const idpDisabled = element.querySelector('.idp__disabled');
  if (!Boolean(idpDisabled)) {
    return false;
  }

  return isVisibleElement(idpDisabled);
};
