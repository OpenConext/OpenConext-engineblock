import {isFocusOnArrowItem} from './isFocusOnArrowItem';
import {isHoverOnArrowItem} from './isHoverOnArrowItem';

/**
 * This function is executed when the user hovers above the remaining IDP section.  If that is the case & there is no focus on an arrow item, then the closest arrow item will be focused.
 *
 * @param e
 */

export function checkHover(e) {
  const focusedElement = document.activeElement;

  if (!!focusedElement) {
    const focusOnArrowItem = isFocusOnArrowItem(focusedElement);
    if (focusOnArrowItem) {
      return false;
    }
  }

  const hoveredItem = isHoverOnArrowItem(e.target);
  if (hoveredItem) {
    hoveredItem.focus();
  }
}
