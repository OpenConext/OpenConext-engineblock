import {getData} from '../utility/getData';
import {handleAriaPressed} from '../utility/handleAriaPressed';
import {handleIdpBanner} from './handleIdpBanner';
import {
  ariaPressedCheckboxSelector,
  defaultIdpSelector,
  idpDeleteDisabledSelector,
  idpListSelector,
  remainingIdpSectionSelector,
  selectedIdpsFirstIdp,
  toggleButtonSelector,
} from '../selectors';
import {idpSubmitHandler} from '../handlers';
import {checkHover} from './idpFocus/checkHover';
import {isVisibleElement} from '../utility/isVisibleElement';

export const mouseBehaviour = () => {
  // allow chosing an idp to login
  const idpLists = document
    .querySelectorAll(idpListSelector);
  idpLists.forEach(list => {
    const hasClickHandler = getData(list, 'clickhandled');

    if (!hasClickHandler) {
      list.addEventListener('click', (e) => {
        e.preventDefault();
        idpSubmitHandler(e);
      });
      list.setAttribute('data-clickhandled', 'true');
    }
  });

  // add a11y support for all checkboxes with an aria-pressed attribute.
  const checkBoxes = document.querySelectorAll(ariaPressedCheckboxSelector);
  handleAriaPressed(checkBoxes);

  // add a11y support for toggleButton
  const toggleButton = document.querySelector(toggleButtonSelector);
  toggleButton.addEventListener('click', () => {
    const firstIdp = document.querySelector(selectedIdpsFirstIdp);
    setTimeout(() => {
      const deleteButton = firstIdp.querySelector(idpDeleteDisabledSelector);
      if (isVisibleElement(deleteButton)) {
        deleteButton.focus();
        return;
      }

      toggleButton.focus();
    }, 100);
  });

  // handle clicking defaultIdp banner
  const defaultIdpLink = document.querySelector(defaultIdpSelector);

  if (!! defaultIdpLink) {
    defaultIdpLink.addEventListener('click', handleIdpBanner);
  }

  // handle hover above idp / default idp informational / searchbar
  const remainingIdpSection = document.querySelector(remainingIdpSectionSelector);
  remainingIdpSection.addEventListener('mousemove', checkHover);
};
