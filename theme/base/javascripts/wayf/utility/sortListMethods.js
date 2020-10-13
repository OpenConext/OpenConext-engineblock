import {sortAndReindex} from './sortAndReindex';

/**
 * Sorts & then reindexes the remaining idps by display title
 */
export function sortAndReindexRemaining(sortBy = 'title', focus = false) {
  sortAndReindex('remaining', sortBy, focus);
}

/**
 * Sorts & then reindexes the previous idps by display title
 */
export function sortAndReindexPrevious(sortBy = 'title', focus = false) {
  sortAndReindex('previous', sortBy, focus);
}
