import {showElement} from '../../utility/showElement';
import {hideElement} from '../../utility/hideElement';
import {defaultIdpInformational} from '../../selectors';

/**
 * Shows / hides the default IdP link section
 *
 * @param searchTerm
 */
export const toggleDefaultIdPLinkVisibility = (searchTerm) => {
  const idpLink = document.querySelector(defaultIdpInformational);
  if (searchTerm.length > 0 ) {
    hideElement(idpLink);
  } else {
    showElement(idpLink);
  }
};
