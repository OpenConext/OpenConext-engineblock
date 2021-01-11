import {getData} from '../../utility/getData';
import {entityIdInputSelector, entityInstitutionInputSelector} from '../../selectors';

/**
 * Set the value of the hidden idp entity-id input field & the hidden institution field.
 *
 * @param element
 * @param form
 */
export const setHiddenFieldValues = (element, form) => {
  const idpEntityIdInput = form.querySelector(entityIdInputSelector);
  const institution = form.querySelector(entityInstitutionInputSelector);

  idpEntityIdInput.value = getData(element, 'entityid');
  institution.value = getData(element, 'title');
};
