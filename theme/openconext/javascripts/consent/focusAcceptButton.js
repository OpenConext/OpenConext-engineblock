// Only focus on the accept terms button if it is visible on the page (to prevent scrolldown)
/**
 * Only focus on the accept terms button if it is visible on the page (to prevent scrolldown)
 *
 * @param querySelector   string    the css selector for the accept button
 */
export const focusAcceptButton = (querySelector) => {
  if (isVisibleOnPage(querySelector)){
    document.querySelector(querySelector).focus();
  }
};
/**
 * Tests if the element (specified by css selector) is visible on the page. Once the element is partly in view the method
 * will start returning true.
 *
 * @param querySelector   string    the css selector for the element
 * @returns {boolean}
 */
const isVisibleOnPage = (querySelector) => {
  const windowHeight = window.innerHeight;
  const elementTopPosition = document.querySelector(querySelector).offsetTop;
  return windowHeight > elementTopPosition;
};
