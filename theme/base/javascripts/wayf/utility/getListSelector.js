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
    return '.wayf__previousSelection .wayf__idpList';
  }

  return '.wayf__remainingIdps .wayf__idpList';
};
