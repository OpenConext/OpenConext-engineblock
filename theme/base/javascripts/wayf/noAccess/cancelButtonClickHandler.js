import {hideNoAccess} from '../../wayf/noAccess/hideNoAccess';
import {isHiddenElement} from '../../utility/isHiddenElement';
import {showFormSelector} from '../../selectors';
import {toggleFormFieldsAndButton} from '../../wayf/noAccess/toggleFormFieldsAndButton';

/**
 * Creates the click handler for the cancel button.
 *
 * @param parentSection
 * @param noAccess
 * @returns {function(...[*]=)}
 */
export const cancelButtonClickHandlerCreator = (parentSection, noAccess) => {
  return () => {
    hideNoAccess(parentSection, noAccess);
    if (isHiddenElement(noAccess.querySelector(showFormSelector))) {
      toggleFormFieldsAndButton();
    }
  };
};
