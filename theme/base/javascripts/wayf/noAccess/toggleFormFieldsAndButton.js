import {nodeListToArray} from '../../utility/nodeListToArray';
import {toggleVisibility} from '../../utility/toggleVisibility';

/**
 * Toggle visibility for:
 * - form fields
 * - request button
 * - submit button
 */
export const toggleFormFieldsAndButton = () => {
  const fieldsets = document.querySelectorAll('.noAccess__requestForm fieldset');
  const requestButton = document.querySelector('.cta__showForm');
  const submitButton = document.querySelector('.cta__request');

  nodeListToArray(fieldsets).forEach(fieldset => {
    toggleVisibility(fieldset);
  });

  toggleVisibility(requestButton);
  toggleVisibility(submitButton);
};
