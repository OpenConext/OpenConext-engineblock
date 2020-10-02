/**
 * Function which executes necessary JS on the Consent Page
 *
 * @param querySelector         string  the query selector which can determine if it's the consent page or not
 * @param callbackAfterLoad     function   function to be executed after the page has fully loaded
 * @param callbackAllways       function   function to be executed before the page is fully loaded.
 */
export const initializePage = (querySelector, callbackAfterLoad, callbackAllways) => {
  if (callbackAllways) {
    callbackAllways();
  }

  if (document.querySelector(querySelector) !== null && callbackAfterLoad) {
    window.addEventListener('load', () => {
      callbackAfterLoad();
    });
  }
};
