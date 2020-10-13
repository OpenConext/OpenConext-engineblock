import {initializePage} from '../../base/javascripts/page';
import {hideNoScript} from './hideNoScript';
import {
  handleUnconnectedKeypicker,
  returnNewIdpPicker,
} from './wayf/IdpPicker';
import {RequestAccessModalHelper} from './wayf/RequestAccessModalHelper';
import {KeyboardListener} from './wayf/KeyboardListener';
import {MouseListener} from './wayf/MouseListener';
import {toggleVisibility} from '../../base/javascripts/utility/toggleVisibility';
import {searchBarEventListeners} from './wayf/searchBarEventListeners';
import {searchFormEventListeners} from './wayf/searchFormEventListeners';
import {interactionListeners} from '../../base/javascripts/wayf/interactionListeners';
import {focusSearchBar} from '../../base/javascripts/wayf/focusSearchBar';

export function initializeWayf() {
  const callbacksAfterLoad = () => {
    // Initialize variables
    const searchBar = document.querySelector('.mod-search-input');
    const searchForm = document.querySelector('form.mod-search');
    const configuration = JSON.parse(document.getElementById('wayf-configuration').innerHTML);
    const idpPicker = returnNewIdpPicker(configuration, searchForm);
    const requestAccessModalHelper = new RequestAccessModalHelper(
      document.getElementById('request-access'),
      document.getElementById('request-access-scroller'),
      searchBar,
      configuration.requestAccessUrl
    );
    const keyboardListener = new KeyboardListener(idpPicker, searchBar, requestAccessModalHelper);

    // Only show the search form when javascript is enabled.
    toggleVisibility(searchForm);

    searchBarEventListeners(searchBar, idpPicker);
    interactionListeners(keyboardListener, new MouseListener(idpPicker, requestAccessModalHelper));
    searchFormEventListeners(searchForm, idpPicker);
    handleUnconnectedKeypicker(requestAccessModalHelper, configuration, searchForm, searchBar, idpPicker, keyboardListener);

    // Put focus on the searchbar if width is big enough
    focusSearchBar(searchBar);
  };

  initializePage('#wayf-configuration', callbacksAfterLoad, hideNoScript);
}
