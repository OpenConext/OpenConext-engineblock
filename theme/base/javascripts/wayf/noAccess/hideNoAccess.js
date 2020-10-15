import {toggleVisibility} from '../../utility/toggleVisibility';

/**
 * Hide the noAccess section & show the Idp list
 *
 * @param parentSection
 * @param noAccessSection
 */
export const hideNoAccess = (parentSection, noAccessSection) => {
  toggleVisibility(noAccessSection);
  toggleVisibility(parentSection);
};
