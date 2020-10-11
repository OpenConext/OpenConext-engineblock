/**
 * Set the data-weight attribute for an idp node, if it's bigger than the previous weight
 *
 * @param element
 * @param weight
 */
export const setWeight = (element, weight) => {
  element.setAttribute('data-weight', String(weight));
};
