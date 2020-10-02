import {initializePage} from './page';
import {addAccessibilitySupport} from './consent/addA11ySupport';

export const initializeConsent = () => {
  initializePage('main.consent', addAccessibilitySupport);
};
