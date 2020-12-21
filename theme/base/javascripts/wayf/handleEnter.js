import {showRemaining} from './utility/showRemaining';
import {fireClickEvent} from '../utility/fireClickEvent';
import {handleDeleteDisable} from './handleDeleteDisable';
import {submitForm} from './submitForm';
import {handleIdpBanner} from './handleIdpBanner';
import {selectFirstIdPAndSubmitForm} from "./selectFirstIdPAndSubmitForm";
import {
  addAccountButtonClass, defaultIdpClass, doneButtonClass, editButtonClass, idpClass, idpContentClass,
  idpDeleteClass,
  idpDeleteDisabledClass,
  idpDisabledClass, idpFormClass, idpLogoClass, idpSubmitClass, idpTitleClass, searchFieldClass,
  toggleButtonClass
} from '../selectors';

/**
 * Behaviour expected to happen after a user presses the enter button.
 */
export const handleEnter = (e, previouslySelectedIdps) => {
  const classList = e.target.classList;

  classList.forEach(className => {
    switch (className) {
      // Show remaining idp section when hitting the add account button
      case addAccountButtonClass:
        showRemaining(); break;
      // toggle edit/done button
      case toggleButtonClass:
      case editButtonClass:
      case doneButtonClass:
        fireClickEvent(e.target); break;
      // handle pressing the disable/delete button
      case idpDeleteDisabledClass:
      case idpDeleteClass:
      case idpDisabledClass:
        handleDeleteDisable(e, previouslySelectedIdps);
        break;
      // select an idp
      case idpClass:
      case idpContentClass:
      case idpTitleClass:
      case idpSubmitClass:
      case idpFormClass:
      case idpLogoClass:
        submitForm(e, previouslySelectedIdps); break;
      case searchFieldClass:
        selectFirstIdPAndSubmitForm(previouslySelectedIdps); break;
      case defaultIdpClass:
        handleIdpBanner(e); break;
    }
  });
};
