import {initConsentPage} from "./modules/ConsentPage";

export function initializeConsent() {
    document.body.className = document.body.className.replace('no-js', '');
    if (document.querySelector('.mod-content.consent') !== null) {
        initConsentPage();
        return;
    }
}
