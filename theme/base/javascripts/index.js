import {replaceMetadataCertificateLinkTexts} from "./modules/EngineBlockMainPage";
import {initializePage} from './page';

export function initializeIndex() {
  initializePage('#engine-main-page', replaceMetadataCertificateLinkTexts);
}
