import {replaceMetadataCertificateLinkTexts} from "./index/EngineBlockMainPage";
import {initializePage} from './page';

export function initializeIndex() {
  initializePage('#engine-main-page', replaceMetadataCertificateLinkTexts);
}
