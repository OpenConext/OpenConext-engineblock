import {isVisibleElement} from '../../utility/isVisibleElement';

/**
 * Check if an element has a visible delete button.
 * Visible is defined as not hidden via the display property.
 *
 * @param element
 * @returns {boolean}
 */
export const hasVisibleDeleteButtonAsTarget = (element) => {
  if (element.className === 'idp__deleteDisabled') {
    const idpDelete = element.querySelector('.idp__delete');
    if (!Boolean(idpDelete)) {
      return false;
    }

    return isVisibleElement(idpDelete);
  }

  if (element.className === 'idp__delete') {
    return isVisibleElement(element);
  }

  return false;
};
