import {getData} from '../utility/getData';
import {switchConsentSection} from './switchConsentSection';

export const addBackListener = () => {
  const backButton = document.querySelector('.consent__nok-back');
  const hasClickHandler = getData(backButton, 'clickhandled');

  // if clickHandler's already been attached, do not attach it again
  if (hasClickHandler) {
    return;
  }

  backButton.addEventListener('click', switchConsentSection);
  backButton.setAttribute('data-clickhandled', true);
};
