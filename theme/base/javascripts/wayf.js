import {initializePage} from './page';
import {wayfCallbackAfterLoad} from './handlers';
import {wayfPageSelector} from './selectors';
import {hideBookmarkableUrl} from './wayf/hideBookmarkableUrl';

export function initializeWayf() {
  initializePage(wayfPageSelector, wayfCallbackAfterLoad, hideBookmarkableUrl);
}
