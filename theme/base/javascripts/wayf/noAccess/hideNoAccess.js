import {hideElement} from '../../utility/hideElement';
import {showElement} from '../../utility/showElement';

/**
 * Hide the noAccess section & show the Idp list
 *
 * @param parentSection
 * @param noAccessSection
 */
export const hideNoAccess = (parentSection, noAccessSection) => {
  hideElement(noAccessSection);
  showElement(parentSection);
};
