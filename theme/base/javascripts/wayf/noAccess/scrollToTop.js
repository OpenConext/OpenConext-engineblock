import {topId} from '../../selectors';

export const scrollToTop = () => {
  document.getElementById(topId).scrollIntoView();
};
