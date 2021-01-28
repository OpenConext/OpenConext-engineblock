import {focusOnNextIdp} from './focusOnNextIdp';
import {
  defaultIdpSelector,
  firstRemainingIdpAfterSearchSelector,
  lastRemainingIdpAfterSearchSelector,
  searchFieldSelector,
  searchResetSelector
} from '../../selectors';
import {isFocusOn} from '../../utility/isFocusOn';
import {focusAndSmoothScroll} from '../../utility/focusAndSmoothScroll';
import {isVisibleElement} from '../../utility/isVisibleElement';

/**
 * Behaviour expected to happen after a user presses the down arrow.
 *
 * When pressing the arrow down in the idp list we want:
 * - to go to the next idp (general behaviour)
 * - to go to the defaultIdp notification if we are on the searchbar & there is an defaultIdp
 * - to go to the first idp if we are on the defaultIdp
 * - to go to the searchbar if we are on the last Idp
 */
export const arrowDown = () => {
  // This can fail when you manually place the focus in the search field and try to navigate with arrows.
  // The failure only happens when we pass the elements as parameters.
  // If you can explain it, please notify koen@ibuildings.nl
  const searchBar = document.querySelector(searchFieldSelector);
  const resetButton = document.querySelector(searchResetSelector);
  const defaultIdp = document.querySelector(defaultIdpSelector);
  const firstIdp = document.querySelector(firstRemainingIdpAfterSearchSelector);
  const lastIdp = document.querySelector(lastRemainingIdpAfterSearchSelector);

  if (isFocusOn(searchBar) || isFocusOn(resetButton)) {
    if(!!defaultIdp && isVisibleElement(defaultIdp)) {
      focusAndSmoothScroll(defaultIdp);
    } else {
      focusAndSmoothScroll(firstIdp);
    }
    return;
  } else if (isFocusOn(defaultIdp)) {
    focusAndSmoothScroll(firstIdp);
    return;
  } else if (isFocusOn(lastIdp)) {
    focusAndSmoothScroll(searchBar);
    return;
  }

  focusOnNextIdp();
};
