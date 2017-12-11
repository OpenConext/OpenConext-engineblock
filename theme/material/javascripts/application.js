import {replaceMetadataCertificateLinkTexts} from "./modules/EngineBlockMainPage";
import {initConsentPage} from "./modules/ConsentPage";
import {IdpList} from "./modules/IdpList";
import {ConnectedIdpPicker} from "./modules/ConnectedIdpPicker";
import {UnconnectedIdpPicker} from "./modules/UnconnectedIdpPicker";
import {PreviousSelectionList} from "./modules/PreviousSelectionList";
import {KeyboardListener} from "./modules/KeyboardListener";
import {PreviousSelectionStorage} from "./modules/PreviousSelectionStorage";
import {IdpListElementFactory} from "./modules/IdpListElementFactory";
import {RequestAccessModalHelper} from "./modules/RequestAccessModalHelper";
import {RememberChoiceStorage} from "./modules/RememberChoiceStorage";

function initialize() {
    document.body.className = document.body.className.replace('no-js', '');

    if (document.querySelector('.mod-content.consent') !== null) {
        initConsentPage();
        return;
    }

    if (document.getElementById('engine-main-page') !== null) {
        replaceMetadataCertificateLinkTexts();
        return;
    }

    if (document.getElementById('wayf-configuration') === null) {
        return;
    }

    const $searchBar                  = document.querySelector('.mod-search-input');
    const $connectedIdpPickerTarget   = document.getElementById('connected-idp-picker');
    const $connectedIdpListTarget     = $connectedIdpPickerTarget.querySelector('.selection');
    const $previousSelectionTarget    = $connectedIdpPickerTarget.querySelector('.preselection');
    const $searchForm                 = document.querySelector('form.mod-search');
    const $requestAccessModal         = document.getElementById('request-access');
    const $requestAccessScroller      = document.getElementById('request-access-scroller');
    const $rememberChoiceTarget       = document.querySelector('#rememberChoiceDiv');
 
    const configuration      = JSON.parse(document.getElementById('wayf-configuration').innerHTML);
    const throttleAmountInMs = 250;

    const idpListElementFactory    = new IdpListElementFactory(configuration.messages);
    const connectedIdpList         = new IdpList(
        $connectedIdpListTarget,
        configuration.connectedIdps,
        idpListElementFactory,
        configuration.cutoffPointForShowingUnfilteredIdps
    );
    const previousSelectionList    = new PreviousSelectionList(
        $previousSelectionTarget,
        configuration.previousSelectionList,
        idpListElementFactory
    );
    const previousSelectionStorage = new PreviousSelectionStorage(configuration.previousSelectionCookieName);
    const rememberChoiceStorage = new RememberChoiceStorage(configuration.rememberChoiceCookieName);
    const connectedIdpPicker       = new ConnectedIdpPicker(
        $searchForm,
        $connectedIdpPickerTarget,
        previousSelectionList,
        connectedIdpList,
        previousSelectionStorage,
        configuration.rememberChoiceFeature,
        rememberChoiceStorage
    );
    const requestAccessModalHelper = new RequestAccessModalHelper(
        $requestAccessModal,
        $requestAccessScroller,
        $searchBar,
        configuration.requestAccessUrl
    );
    const keyboardListener         = new KeyboardListener(connectedIdpPicker, $searchBar, requestAccessModalHelper);

    // Keyup, click and input are registered events for cross-browser compatibility with HTML5 'search' input
    $searchBar.addEventListener('keyup', throttle(event => connectedIdpPicker.searchBy(event.target.value), throttleAmountInMs));
    $searchBar.addEventListener('click', event => connectedIdpPicker.searchBy(event.target.value));
    $searchBar.addEventListener('input', event => connectedIdpPicker.searchBy(event.target.value));

    // Only show the search form when javascript is enabled.
    showElement($searchForm);

    document.addEventListener('keyup', event => keyboardListener.handle(event.keyCode));

    $searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        connectedIdpPicker.selectIdpUnderFocus();
    });

    const $unconnectedIdpPickerTarget = document.getElementById('unconnected-idp-picker');
    if ($unconnectedIdpPickerTarget) {
        // Only show the unconnected IdP box when javascript is enabled.
        showElement($unconnectedIdpPickerTarget);

        const $unconnectedIdpListTarget = $unconnectedIdpPickerTarget.querySelector('.selection');
        const unconnectedIdpList        = new IdpList(
            $unconnectedIdpListTarget,
            configuration.unconnectedIdps,
            idpListElementFactory,
            configuration.cutoffPointForShowingUnfilteredIdps
        );
        const unconnectedIdpPicker      = new UnconnectedIdpPicker(
            $searchForm,
            $unconnectedIdpPickerTarget,
            unconnectedIdpList
        );

        $searchBar.addEventListener('keyup', throttle(event => unconnectedIdpPicker.searchBy(event.target.value), throttleAmountInMs));
        $searchBar.addEventListener('click', event => unconnectedIdpPicker.searchBy(event.target.value));
        $searchBar.addEventListener('input', event => unconnectedIdpPicker.searchBy(event.target.value));

        $unconnectedIdpPickerTarget.addEventListener('click', requestAccessModalHelper.requestAccessClickHandler());
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

function showElement(element) {
    var pattern = 'hidden';
    if (element.className.match(/ hidden/)) {
        pattern = ' hidden';
    }

    element.className = element.className.replace(pattern, '');
}

initialize();
