import {replaceMetadataCertificateLinkTexts} from "./index/EngineBlockMainPage";
import {initializePage} from './page';
import {indexPageSelector} from './selectors';

export function initializeIndex() {
  initializePage(indexPageSelector, replaceMetadataCertificateLinkTexts);
}
