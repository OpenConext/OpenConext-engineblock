/**
 * Set the data-weight attribute for an idp node
 *
 * @param element
 * @param weight
 */
export const setWeight = (element, weight) => {
  element.setAttribute('data-weight', String(weight));
};
