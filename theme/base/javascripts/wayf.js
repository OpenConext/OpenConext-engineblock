// Temporary code to get wayf to function. Only submit by clicking on a IdP functions for now
import {handleUnconnectedKeypicker, returnNewIdpPicker} from './modules/IdpPicker';
import {RequestAccessModalHelper} from './modules/RequestAccessModalHelper';
import {KeyboardListener} from './modules/KeyboardListener';
import {toggleVisibility} from './utility/toggleVisibility';
import {searchBarEventListeners} from './wayf/searchBarEventListeners';
import {interactionListeners} from './wayf/interactionListeners';
import {MouseListener} from './modules/MouseListener';
import {searchFormEventListeners} from './wayf/searchFormEventListeners';
import {focusSearchBar} from './wayf/focusSearchBar';
import {initializePage} from './page';
import {hideNoScript} from '../../openconext/javascripts/hideNoScript';
import {showIdpsOnLoad} from './wayf/showIdpsOnLoad';

export function initializeWayf() {
  // const form = document.querySelector('.wayf__search');
  // if (form !== null) {
  //   // Set a quick and dirty click listener on every IdP
  //   document.querySelectorAll('.wayf__idp').forEach((article) => {
  //     article.addEventListener('click', (event) => {
  //       event.preventDefault();
  //       // And set the EnittyId associated with that IdP on the hidden form field. And submit the form
  //       form.elements['form-idp'].value = event.target.closest('article').dataset.entityId;
  //       form.submit();
  //     });
  //   });
  // }

  const callbacksAfterLoad = () => {
    // Initialize variables
    const selectedIdps = document.querySelector('.wayf__previousSelection');
    const remainingIdps = document.querySelector('.wayf__remainingIdps');
    const noResults = document.querySelector('.wayf__noResults');
    const noAccess = document.querySelector('.wayf__noAccess');
    const searchBar = document.querySelector('#wayf_search');
    const searchForm = document.querySelector('.wayf__search');
    const configuration = JSON.parse(document.getElementById('wayf-configuration').innerHTML);

    showIdpsOnLoad(selectedIdps, remainingIdps);
  };

  initializePage('main.wayf', callbacksAfterLoad);
}
