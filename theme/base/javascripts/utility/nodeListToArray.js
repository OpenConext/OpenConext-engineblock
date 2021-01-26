/**
 * Converts a nodeList to an array
 * Note: we used to use the [].slice.call() hack, but removed it after reading this: https://ultimatecourses.com/blog/ditch-the-array-foreach-call-nodelist-hack
 *
 * @param list            a nodelist
 *
 * @returns Node[]        an array of nodes
 */
export const nodeListToArray = (list) => {
  const myArrayFromNodeList = [];
  for (let i = 0; i < list.length; i++) {
    myArrayFromNodeList.push(list[i]);
  }
  return myArrayFromNodeList;
};
