import {getData} from '../../utility/getData';

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
  const lang = document.querySelector('html').getAttribute('lang') || 'nl';

  return titleOne.localeCompare(titleTwo, lang);
};

/**
 * Helper function for sortByTitle
 * @param element
 * @returns {number|string}
 */
function getTitle(element) {
  return getData(element, 'title');
}

/**
 * Determine which of two li elements has a number attribute that is greater than the other.
 * Sorts in descending order
 * Numbers are taken from data-attributes
 * @param firstElement
 * @param secondElement
 * @param attributeName
 * @returns {number}
 */
export const sortByNumber = (firstElement, secondElement, attributeName) => {
  const numberOne = getNumberAttribute(firstElement.children[0], attributeName);
  const numberTwo = getNumberAttribute(secondElement.children[0], attributeName);

  if (numberOne > numberTwo) return -1;
  if (numberOne < numberTwo) return 1;

  // if weights are equal sort them by title
  return sortByTitle(firstElement , secondElement);
};

/**
 * Helper function for sortByNumber
 * @param element
 * @param attributeName
 * @returns {number}
 */
function getNumberAttribute(element, attributeName) {
  return Number(getData(element, attributeName));
}

/**
 * Sort li elements by how well the idps in it match the current search query.
 * @param firstElement
 * @param secondElement
 * @returns {number}
 */
export const sortByWeight = (firstElement, secondElement) => {
  return sortByNumber(firstElement, secondElement, 'weight');
};

/**
 * Sort li elements by the amount of times the idps in them have been clicked.
 * @param firstElement
 * @param secondElement
 * @returns {number}
 */
export const sortByCount = (firstElement, secondElement) => {
  return sortByNumber(firstElement, secondElement, 'count');
};
