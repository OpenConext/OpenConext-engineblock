import {replaceMetadataCertificateLinkTexts} from "../../base/javascripts/modules/EngineBlockMainPage";
import {initializePage} from '../../base/javascripts/page';
import {hideNoScript} from './hideNoScript';

export function initializeIndex() {
  initializePage('#engine-main-page', replaceMetadataCertificateLinkTexts, hideNoScript);
}
