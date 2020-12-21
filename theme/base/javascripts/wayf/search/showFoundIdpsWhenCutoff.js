import {searchingClass} from '../../selectors';

/**
 * When the feature flag "cuttoffPointForShowingUnfilteredIdps" is set: show the search results.
 *
 * @param list    The ul element for the remaining idps.
 */
export const showFoundIdpsWhenCutoff = (list) => {
  if (!!list) {
    list.classList.add(searchingClass);
  }
};
