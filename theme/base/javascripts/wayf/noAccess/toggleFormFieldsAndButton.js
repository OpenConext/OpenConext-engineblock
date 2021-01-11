import {nodeListToArray} from '../../utility/nodeListToArray';
import {toggleVisibility} from '../../utility/toggleVisibility';
import {noAccessFieldsetsSelector, showFormSelector, submitRequestSelector} from '../../selectors';

/**
 * Toggle visibility for:
 * - form fields
 * - request button
 * - submit button
 */
export const toggleFormFieldsAndButton = () => {
  const fieldsets = document.querySelectorAll(noAccessFieldsetsSelector);
  const requestButton = document.querySelector(showFormSelector);
  const submitButton = document.querySelector(submitRequestSelector);

  nodeListToArray(fieldsets).forEach(fieldset => {
    toggleVisibility(fieldset);
  });

  toggleVisibility(requestButton);
  toggleVisibility(submitButton);
};
