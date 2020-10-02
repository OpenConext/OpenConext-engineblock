import {hasSelectedIdps} from './hasSelectedIdps';
import {toggleVisibility} from '../utility/toggleVisibility';

/**
 * Check if user has any previous selected Idps.  If so: show those, else show the larger list.
 *
 * @param selectedIdps    HTMLElement   the list of user-selected idps
 * @param remainingIdps   HTMLElement   the list of all non-selected idps
 */
export const showIdpsOnLoad = (selectedIdps, remainingIdps) => {
  if (hasSelectedIdps()) {
    toggleVisibility(selectedIdps).parent();
    return;
  }

  toggleVisibility(remainingIdps);
};
