import {addSelectedIdp} from './deleteDisable/addSelectedIdp';
import {handleClickingDisabledIdp} from './handleClickingDisabledIdp';
import {hasVisibleDisabledButtonAsTarget} from './utility/hasVisibleDisabledButtonAsTarget';
import {hasVisibleDeleteButtonAsTarget} from './utility/hasVisibleDeleteButtonAsTarget';
import {idpFormSelector, idpSelector} from '../selectors';

/**
 * Submit the form for the selected idp.
 * This ensures you select the idp to log in.
 *
 * Also checks for the remember choice feature and handles it accordingly.
 *
 * @param e
 */
export const submitForm = (e) => {
  e.preventDefault();
  let element = e.target;
  if (hasVisibleDeleteButtonAsTarget(element)) {
    return;
  }

  if (hasVisibleDisabledButtonAsTarget(element)) {
    handleClickingDisabledIdp(element);
    return;
  }

  if (!element.classList.contains(idpSelector)) {
    element = element.closest(idpSelector);
  }

  selectAndSubmit(element);
};

/**
 * Performs the actual remember and sumbit logic. Where the submitForm
 * method both verifies and submits the form. This method only does
 * the latter.
 *
 * @param element
 */
export const selectAndSubmit = (element) => {
  if (hasVisibleDisabledButtonAsTarget(element)) {
    handleClickingDisabledIdp(element);
    return;
  }

  addSelectedIdp(element);
  element.querySelector(idpFormSelector).submit();
};
