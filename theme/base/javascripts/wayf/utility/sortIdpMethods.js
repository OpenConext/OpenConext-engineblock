/**
 * Determine which of two idp elements alphabetically comes before the other.
 * Sorts in ascending order.
 * Titles are taken from the data-title attribute, which is lowercased in twig.
 *
 * @param firstElement
 * @param secondElement
 * @returns {number}
 */
export const sortByTitle = (firstElement, secondElement) => {
  const titleOne = getTitle(firstElement.children[0]);
  const titleTwo = getTitle(secondElement.children[0]);

  return titleOne.localeCompare(titleTwo);
};

function getTitle(element) {
  return element.getAttribute('data-title');
}
