import {toggleVisibility} from '../../utility/toggleVisibility';
import {focusOn} from "../../utility/focusOn";
import {noAccessSectionSelector, noResultSectionSelector, remainingIdpSelector, searchFieldSelector, selectedIdpsSelector} from '../../selectors';
import {isVisibleElement} from '../../utility/isVisibleElement';

export const switchIdpSection = () => {
  const remainingIdps = document.querySelector(remainingIdpSelector);
  const previousIdps = document.querySelector(selectedIdpsSelector);
  const noResults = document.querySelector(noResultSectionSelector);
  const noAccess = document.querySelector(noAccessSectionSelector);
  const ariaHidden = 'aria-hidden';

  toggleVisibility(previousIdps);
  toggleVisibility(remainingIdps);

  remainingIdps.removeAttribute(ariaHidden);

  if(isVisibleElement(noAccess) || isVisibleElement(noResults)) {
    remainingIdps.setAttribute(ariaHidden, 'true');
  }

  if (!remainingIdps.classList.contains('hidden')) {
    focusOn(searchFieldSelector);
  }
};
