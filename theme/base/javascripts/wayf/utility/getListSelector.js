import {remainingIdpListSelector, selectedIdpsListSelector} from '../../selectors';

const PREVIOUS = 'previous';
const REMAINING = 'remaining';

/**
 * Return the css selector for the desired idp-list
 *
 * @param list
 * @returns {string}
 */
export const getListSelector = (list = REMAINING) => {
  if (list === PREVIOUS) {
    return selectedIdpsListSelector;
  }

  return remainingIdpListSelector;
};
