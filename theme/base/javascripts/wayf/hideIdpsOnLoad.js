import {hasSelectedIdps} from './hasSelectedIdps';
import {toggleVisibility} from '../utility/toggleVisibility';
import {toggleRemaining} from './toggleRemaining';

/**
 * Check if user has any previous selected Idps.  If so: show those, else show the larger list.
 *
 * @param selectedIdps    HTMLElement   the list of user-selected idps
 */
export const hideIdpsOnLoad = (selectedIdps) => {
  if (hasSelectedIdps()) {
    toggleRemaining();
    toggleVisibility(selectedIdps);
  }
};
