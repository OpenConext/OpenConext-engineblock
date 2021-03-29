import {consentEnterHandler} from '../handlers';
import {fireClickEvent} from '../utility/fireClickEvent';
import {getData} from '../utility/getData';

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
  const SPACE      = 32;
  const TAB        = 9;

  document.querySelector('body').addEventListener('keydown', function(e) {
    const classList = e.target.classList;
    switch (e.keyCode) {
      case ENTER:
        consentEnterHandler(e.target);
        break;
      case SPACE:
        classList.forEach(className => {
          switch (className) {
            case 'openToggle__label':
            case 'showLess':
            case 'showMore':
              fireClickEvent(e.target);
              break;
          }
        });
        break;
      case TAB:
        classList.forEach(className => {
          switch (className) {
            case 'tooltip__value':
              const dataFor = getData(e.target, 'for');
              document.querySelector(`[for=${dataFor}]`).focus();
              break;
          }
        });
        break;
    }
  });
};
