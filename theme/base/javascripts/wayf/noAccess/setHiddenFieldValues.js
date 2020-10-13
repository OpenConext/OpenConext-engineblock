/**
 * Set the value of the hidden idp entity-id input field & the hidden institution field.
 *
 * @param element
 * @param form
 */
import {getData} from '../../utility/getData';

export const setHiddenFieldValues = (element, form) => {
  const idpEntityIdInput = form.querySelector('input[name="idpEntityId"]');
  const institution = form.querySelector('input[name="institution"]');

  idpEntityIdInput.value = getData(element, 'entityid');
  institution.value = getData(element, 'title');
};
