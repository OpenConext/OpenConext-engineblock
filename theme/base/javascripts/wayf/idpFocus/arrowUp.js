import {isFocusOn} from '../../utility/isFocusOn';
import {focusOnPreviousIdp} from './focusOnPreviousIdp';
import {
  defaultIdpSelector,
  remainingIdpAfterSearchSelector,
  searchFieldSelector,
  searchResetSelector
} from '../../selectors';
import {focusAndSmoothScroll} from '../../utility/focusAndSmoothScroll';
import {isVisibleElement} from '../../utility/isVisibleElement';

/**
 * Behaviour expected to happen after a user presses the up arrow.
 *
 * When pressing the arrow up in the idp list we want:
 * - to go to the previous idp (general behaviour)
 * - to go to the defaultIdp notification if we are on the first idp & there is a defaultIdp
 * - to go to the searchbar if we are on the defaultIdp notification
 * - to go to the last idp if we are on the searchbar
 */
export const arrowUp = () => {
  // This can fail when you manually place the focus in the search field and try to navigate with arrows.
  // The failure only happens when we pass the elements as parameters.
  // If you can explain it, please notify koen@ibuildings.nl
  const searchBar = document.querySelector(searchFieldSelector);
  const resetButton = document.querySelector(searchResetSelector);
  const defaultIdp = document.querySelector(defaultIdpSelector);
  const remainingIdps = document.querySelectorAll(remainingIdpAfterSearchSelector);
  const firstIdp = remainingIdps[0];
  const lastIdp = remainingIdps[remainingIdps.length - 1];

  if (isFocusOn(searchBar) || isFocusOn(resetButton)) {
    console.log({lastIdp});
    focusAndSmoothScroll(lastIdp);
    return;
  } else if (isFocusOn(firstIdp)) {
    if (!!defaultIdp && isVisibleElement(defaultIdp)) {
      focusAndSmoothScroll(defaultIdp);
    } else {
      focusAndSmoothScroll(searchBar);
    }
    return;
  } else if (isFocusOn(defaultIdp)) {
    focusAndSmoothScroll(searchBar);
    return;
  }

  focusOnPreviousIdp();
};
