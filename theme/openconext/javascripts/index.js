import {replaceMetadataCertificateLinkTexts} from "../../base/javascripts/index/EngineBlockMainPage";
import {initializePage} from '../../base/javascripts/page';
import {hideNoScript} from './hideNoScript';

export function initializeIndex() {
  initializePage('#engine-main-page', replaceMetadataCertificateLinkTexts, hideNoScript);
}
