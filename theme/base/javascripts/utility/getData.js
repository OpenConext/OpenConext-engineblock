/**
 * Get the contents of a data-attribute for a given element.
 *
 * @param     element           the HTMLElement
 * @param     attributeName     the attribute name, without the data-prefix
 * @returns   {number | string}
 */
export const getData = (element, attributeName) => {
  return element.getAttribute(`data-${attributeName}`);
};
