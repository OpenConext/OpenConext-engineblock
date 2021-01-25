import {focusAndSmoothScroll} from '../../utility/focusAndSmoothScroll';
import {getData} from '../../utility/getData';

export const focusOnPreviousIdp = () => {
  let previousSibling = document.activeElement.parentElement.previousElementSibling;

  while (previousSibling && getData(previousSibling, 'weight') === '0') {
    previousSibling = previousSibling.previousElementSibling;
  }

  if (!!previousSibling) {
    focusAndSmoothScroll(previousSibling.firstElementChild);
  }
};
