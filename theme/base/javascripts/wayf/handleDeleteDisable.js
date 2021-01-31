import {hasSelectedIdpsInList} from './utility/hasSelectedIdpsInList';
import {handleNoneLeft} from './deleteDisable/handleNoneLeft';
import {deleteIdp} from './deleteDisable/deleteIdp';
import {reindexIdpArray} from './utility/reindexIdpArray';
import {reinsertIdpList} from './utility/reinsertIdpList';
import {sortPrevious, sortRemaining} from './utility/sortIdps';
import {getListSelector} from './utility/getListSelector';
import {hasVisibleDisabledButtonAsTarget} from './utility/hasVisibleDisabledButtonAsTarget';
import {handleClickingDisabledIdp} from './handleClickingDisabledIdp';
import {idpDeleteDisabledSelector, idpSelector} from '../selectors';

/**
 * Handle what happens if a user clicks on either the delete button, or the disabled button in an Idp.
 *
 * @param e
 */
export const handleDeleteDisable = (e) => {
  e.preventDefault();
  e.stopPropagation();
  let element = e.target;

  // in case the origin is the span setting the element needs to be done differently
  if (e.target.tagName === 'SPAN') {
    element = e.target.closest(idpDeleteDisabledSelector);
  }

  // handle clicking disabled button
  if (hasVisibleDisabledButtonAsTarget(element)) {
    handleClickingDisabledIdp(element.closest(idpSelector));
    return;
  }

  // Remove item from previous selection & html
  deleteIdp(element);

  // Reindex & SortRemaining idps by title
  const idpArray = sortRemaining();
  if (idpArray) {
    reindexIdpArray(idpArray);
    reinsertIdpList(idpArray, getListSelector());
  }

  // If no items are left: do what's needed.
  if (!hasSelectedIdpsInList()) {
    handleNoneLeft();
    return;
  }

  // If there are items left: sort, reindex & focus first one
  const previousIdpArray = sortPrevious();
  if (previousIdpArray) {
    reindexIdpArray(previousIdpArray);
    reinsertIdpList(previousIdpArray, getListSelector('previous'));
    previousIdpArray[0].focus();
  }
};
