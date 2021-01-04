import {searchingClass} from '../../selectors';

/**
 * When the feature flag "cuttoffPointForShowingUnfilteredIdps" is set: hide idps when not searching.
 *
 * @param list    The ul element for the remaining idps.
 */
export const hideIdpsWhenCutoffNoSearch = (list) => {
  if (!!list) {
    list.classList.remove(searchingClass);
  }
};
