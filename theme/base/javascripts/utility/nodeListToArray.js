/**
 * Converts a nodeList to an array
 *
 * @param list            a nodelist
 *
 * @returns Node[]        an array of nodes
 */
export const nodeListToArray = (list) => {
  return [].slice.call(list, 0);
};
