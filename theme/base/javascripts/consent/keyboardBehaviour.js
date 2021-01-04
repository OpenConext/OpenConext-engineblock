import {consentEnterHandler} from '../handlers';

/**
 * Make all behaviour that's possible with a mouse also keyboard accessible.
 * Why => to make the site accessible.  See the WCAG 2.1 requirement: https://www.w3.org/TR/WCAG21/#keyboard
 *
 * Concerns for the consent screen:
 * - tooltips / foldouts
 * - "show more info buttons"
 * - labels
 *
 * Note: many problems were solved with HTML & CSS.  JS should be a last resort, as not everyone has JS.  See https://kryogenix.org/code/browser/everyonehasjs.html as to why to avoid JS.
 */
export const keyboardBehaviour = () => {
  const ENTER      = 13;

  document.querySelector('body').addEventListener('keydown', function(e) {
    if (e.keyCode === ENTER) {
      consentEnterHandler(e.target);
    }
  });
};
