import {setCookie} from '../../utility/setCookie';

/**
 * Save the previous selection in a cookie for 365 days
 *
 * @param previousSelection   array of previously selected Idps
 * @param cookieName          string
 */
export const savePreviousSelection = (previousSelection, cookieName) => {
  const simplifiedPreviousSelection = previousSelection.map((idp) => {
    return {
      'idp': idp.entityId,
      'count': idp.count
    };
  });

  setCookie(simplifiedPreviousSelection, cookieName);
};
