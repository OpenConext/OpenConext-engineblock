import {hideErrorMessage} from './hideErrorMessage';
import {cancelButtonSelector} from '../../selectors';
import {addClickHandlerOnce} from '../../utility/addClickHandlerOnce';
import {cancelButtonClickHandlerCreator} from './cancelButtonClickHandler';

/**
 * Ensure clicking the Cancel button shows the right behaviour:
 * - hide no Access section
 * - hide error message if shown
 * - hide form fields & submit button + show request button
 *
 * @param parentSection
 * @param noAccess
 */
export const attachClickHandlerToCancelButton = (parentSection, noAccess) => {
  addClickHandlerOnce(cancelButtonSelector, cancelButtonClickHandlerCreator(parentSection, noAccess));
  hideErrorMessage(noAccess);
};
