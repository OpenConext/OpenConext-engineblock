import {toggleErrorMessage} from './toggleErrorMessage';
import {isHiddenElement} from '../../utility/isHiddenElement';

/**
 * Hide the error message if it's currently shown
 *
 *  @param noAccess
 */
export const hideErrorMessage = (noAccess) => {
  if (!isHiddenElement(noAccess.querySelector('.notification__critical'))) {
    toggleErrorMessage();
  }
};
