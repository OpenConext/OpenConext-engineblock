import {fireClickEvent} from '../utility/fireClickEvent';
import {modalLabels} from '../selectors';

/**
 * How to handle an enter press for the consent screen.
 *
 * @param target  The targetted element (e.target)
 */
export const enterHandler = (target) => {
  if (target.tabIndex !== 0) return;

  switch(target.tagName) {
    case 'LABEL': fireClickEvent(target); break;
    case 'DIV': fireClickEvent(target.querySelector(modalLabels)); break;
  }
};
