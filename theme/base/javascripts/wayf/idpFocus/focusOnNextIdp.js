import {focusAndSmoothScroll} from '../../utility/focusAndSmoothScroll';
import {getData} from '../../utility/getData';

export const focusOnNextIdp = () => {
  let nextSibling = document.activeElement.parentElement.nextElementSibling;

  while (nextSibling && getData(nextSibling, 'weight') === '0') {
    nextSibling = nextSibling.nextElementSibling;
  }

  if (!!nextSibling) {
    focusAndSmoothScroll(nextSibling.firstElementChild);
  }
};
