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

function getTitle(element) {
  return getData(element, 'title');
}

export const sortByWeight = (firstElement, secondElement) => {
  const weightOne = Number(getData(firstElement.children[0], 'weight'));
  const weightTwo = Number(getData(secondElement.children[0], 'weight'));

  if (weightOne > weightTwo) return -1;
  if (weightOne < weightTwo) return 1;

  // if weights are equal sort them by title
  return sortByTitle(firstElement , secondElement);
};
