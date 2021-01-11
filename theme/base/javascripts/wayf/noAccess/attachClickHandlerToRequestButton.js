import {attachClickHandlerToForm} from './attachClickHandlerToForm';
import {showFormSelector} from '../../selectors';
import {addClickHandlerOnce} from '../../utility/addClickHandlerOnce';
import {requestButtonHandler} from '../../handlers';

/**
 * Ensure clicking the request button shows the right behaviour:
 * - hide request button
 * - show submit button
 * - show formfields
 * - ensure submitting the form is possible
 *
 * @param parentSection
 * @param noAccess
 * @param form
 */
export const attachClickHandlerToRequestButton = (parentSection, noAccess, form) => {
  const requestButton = document.querySelector(showFormSelector);

  if (!!requestButton) {
    addClickHandlerOnce(showFormSelector, requestButtonHandler);

    // attach clickHandler for form
    attachClickHandlerToForm(form, parentSection, noAccess);
  }
};
