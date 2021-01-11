import {initializePage} from './page';
import {wayfCallbackAfterLoad} from './handlers';
import {wayfPageSelector} from './selectors';

export function initializeWayf() {
  initializePage(wayfPageSelector, wayfCallbackAfterLoad);
}
