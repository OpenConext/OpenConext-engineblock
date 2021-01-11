import {initializePage} from './page';
import {consentCallbackAfterLoad} from './handlers';

export const initializeConsent = () => {
  initializePage('main.consent', consentCallbackAfterLoad);
};
