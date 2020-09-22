import {replaceMetadataCertificateLinkTexts} from "./modules/EngineBlockMainPage";

export function initializeIndex() {
    document.body.className = document.body.className.replace('no-js', '');

    if (document.getElementById('engine-main-page') !== null) {
        replaceMetadataCertificateLinkTexts();
        return;
    }
}
