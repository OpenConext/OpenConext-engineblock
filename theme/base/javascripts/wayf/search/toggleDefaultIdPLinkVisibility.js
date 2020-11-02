import {showElement} from '../../utility/showElement';
import {hideElement} from '../../utility/hideElement';

/**
 * Shows / hides the default IdP link section
 *
 * @param searchTerm
 */
export const toggleDefaultIdPLinkVisibility = (searchTerm) => {
  const idpLink = document.querySelector('.remainingIdps__defaultIdp');
  if (searchTerm.length > 0 ) {
    hideElement(idpLink);
  } else {
    showElement(idpLink);
  }
};
