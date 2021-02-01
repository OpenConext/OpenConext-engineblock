import {setCookie} from '../../utility/setCookie';

/**
 * Save the previous selection in a cookie for 365 days
 *
 * @param previousSelection   array of previously selected Idps
 * @param cookieName          string
 */
export const savePreviousSelection = (previousSelection, cookieName) => {
  if (previousSelection.length === 0) {
    setCookie(previousSelection, cookieName, -1, '/', true);
    return;
  }

  setCookie(previousSelection, cookieName, 365, '/', true);
};
