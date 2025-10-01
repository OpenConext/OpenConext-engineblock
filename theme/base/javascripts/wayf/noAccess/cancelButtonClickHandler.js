import {hideNoAccess} from './hideNoAccess';
import {isHiddenElement} from '../../utility/isHiddenElement';
import {searchFieldSelector, showFormSelector} from '../../selectors';
import {toggleFormFieldsAndButton} from './toggleFormFieldsAndButton';

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
    document.querySelector(searchFieldSelector).focus();
  };
};
