import { toggleVisibility } from '../utility/toggleVisibility';

export const switchConsentSection = (e) => {
  const nokSection = document.querySelector('.consent__nok');
  const contentSection = document.querySelector('.consent__content');
  if (e) {
    e.preventDefault();
  }

  toggleVisibility(nokSection);
  toggleVisibility(contentSection);
};
