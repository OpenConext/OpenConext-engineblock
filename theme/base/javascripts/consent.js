import {initializePage} from './page';
import {addAccessibilitySupport} from './consent/addA11ySupport';
import {addNokListener} from './consent/addNokListener';

export const initializeConsent = () => {
  const callbacksAfterLoad = () => {
    addAccessibilitySupport();
    addNokListener();
  };
  initializePage('main.consent', callbacksAfterLoad);
};
