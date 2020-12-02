import { switchConsentSection } from './switchConsentSection';
import {getData} from '../utility/getData';
import {addBackListener} from './addBackListener';

export const addNokListener = () => {
  const nokButton = document.querySelector('label[for="cta_consent_nok"]');
  const hasClickHandler = getData(nokButton, 'clickhandled');

  // if clickHandler's already been attached, do not attach it again
  if (hasClickHandler) {
    return;
  }

  nokButton.addEventListener('click', (e) => {
    switchConsentSection(e);
    addBackListener();
  });
  nokButton.setAttribute('data-clickhandled', true);
};
