import { toggleVisibility } from '../utility/toggleVisibility';
import {contentSectionSelector, nokSectionSelector} from '../selectors';

export const switchConsentSection = (e) => {
  const nokSection = document.querySelector(nokSectionSelector);
  const contentSection = document.querySelector(contentSectionSelector);

  if (!!e) {
    e.preventDefault();
  }

  toggleVisibility(nokSection);
  toggleVisibility(contentSection);
};
