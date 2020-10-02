import {focusAcceptButton} from '../../base/javascripts/consent/focusAcceptButton';
import {initTooltips} from './consent/initTooltips';
import {initAttributesToggle} from './consent/initAttributesToggle';
import {initSlideIns} from './consent/initSlideIns';
import {initializePage} from '../../base/javascripts/page';
import {hideNoScript} from './hideNoScript';

export const initializeConsent = () => {
  const callbacksAfterLoad = () => {
    focusAcceptButton('#accept_terms_button');
    initTooltips();
    initAttributesToggle();
    initSlideIns();
  };

  initializePage('.mod-content.consent', callbacksAfterLoad, hideNoScript);
};
