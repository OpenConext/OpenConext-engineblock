import {toggleVisibility} from '../../utility/toggleVisibility';
import {focusOn} from "../../utility/focusOn";
import {remainingIdpSelector, searchFieldSelector, selectedIdpsSelector} from '../../selectors';

export const switchIdpSection = () => {
  const remainingIdps = document.querySelector(remainingIdpSelector);
  const previousIdps = document.querySelector(selectedIdpsSelector);
  const ariaHidden = 'aria-hidden';

  toggleVisibility(previousIdps);
  toggleVisibility(remainingIdps);

  if (remainingIdps.getAttribute(ariaHidden)) {
    remainingIdps.removeAttribute(ariaHidden);
    previousIdps.setAttribute(ariaHidden, 'true');
    return;
  }

  previousIdps.removeAttribute(ariaHidden);
  remainingIdps.setAttribute(ariaHidden, 'true');

  focusOn(searchFieldSelector);
};
