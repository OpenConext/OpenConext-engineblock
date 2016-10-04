import {replaceMetadataCertificateLinkTexts} from "./modules/EngineBlockMainPage";
import {IdpList} from "./modules/IdpList";
import {IdpPicker} from "./modules/IdpPicker";
import {PreviousSelectionList} from "./modules/PreviousSelectionList";
import {KeyboardListener} from "./modules/KeyboardListener";
import {PreviousSelectionStorage} from "./modules/PreviousSelectionStorage";
import {IdpListElementFactory} from "./modules/IdpListElementFactory";
import {RequestAccessModalHelper} from "./modules/RequestAccessModalHelper";

function initialize() {
    document.body.className = document.body.className.replace('no-js', '');

    if (document.getElementById('engine-main-page') !== null) {
        replaceMetadataCertificateLinkTexts();
        return;
    }

    if (document.getElementById('wayf-configuration') === null) {
        return;
    }

    const $idpListTarget           = document.querySelector('#selection');
    const $previousSelectionTarget = document.querySelector('#preselection');
    const $searchBar               = document.querySelector('.mod-search-input');
    const $idpPickerTarget         = document.getElementById('idp-picker');
    const $searchForm              = document.querySelector('form.mod-search');
    const $requestAccessLink       = document.querySelector('a.noaccess');
    const $requestAccessModal      = document.getElementById('request-access');
    const $requestAccessScroller   = document.getElementById('request-access-scroller');

    const configuration      = JSON.parse(document.getElementById('wayf-configuration').innerHTML);
    const throttleAmountInMs = 250;

    const idpListElementFactory    = new IdpListElementFactory(configuration.messages);
    const idpList                  = new IdpList(
        $idpListTarget,
        configuration.idpList,
        idpListElementFactory,
        configuration.cutoffPointForShowingUnfilteredIdps
    );
    const previousSelectionList    = new PreviousSelectionList(
        $previousSelectionTarget,
        configuration.previousSelectionList,
        idpListElementFactory
    );
    const previousSelectionStorage = new PreviousSelectionStorage(configuration.previousSelectionCookieName);
    const idpPicker                = new IdpPicker(
        $searchForm,
        $idpPickerTarget,
        previousSelectionList,
        idpList,
        previousSelectionStorage
    );
    const requestAccessModalHelper = new RequestAccessModalHelper(
        $requestAccessModal,
        $requestAccessScroller,
        $searchBar,
        configuration.requestAccessUrl
    );
    const keyboardListener         = new KeyboardListener(idpPicker, $searchBar, requestAccessModalHelper);

    // Keyup, click and input are registered events for cross-browser compatibility with HTML5 'search' input
    $searchBar.addEventListener('keyup', throttle(event => idpPicker.searchBy(event.target.value), throttleAmountInMs));
    $searchBar.addEventListener('click', event => idpPicker.searchBy(event.target.value));
    $searchBar.addEventListener('input', event => idpPicker.searchBy(event.target.value));

    document.addEventListener('keyup', event => keyboardListener.handle(event.keyCode));

    $searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        idpPicker.selectIdpUnderFocus();
    });

    if ($requestAccessLink !== null) {
        $requestAccessLink.addEventListener('click', (event) => requestAccessModalHelper.openRequestAccessModal());
    }

    if (window.innerWidth > 800) {
        $searchBar.focus();
    }
}

function throttle(action, delay) {
    let timer = null;

    return function () {
        clearTimeout(timer);
        timer = setTimeout(() => action.apply(this, arguments), delay);
    };
}

initialize();
