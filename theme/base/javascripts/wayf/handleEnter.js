import {showRemaining} from './utility/showRemaining';
import {fireClickEvent} from '../utility/fireClickEvent';
import {handleDeleteDisable} from './handleDeleteDisable';
import {submitForm} from './submitForm';
import {handleIdpBanner} from './handleIdpBanner';
import {toggleFormFieldsAndButton} from './noAccess/toggleFormFieldsAndButton';

/**
 * Behaviour expected to happen after a user presses the enter button.
 */
export const handleEnter = (e, previouslySelectedIdps) => {
  const classList = e.target.classList;

  classList.forEach(className => {
    switch (className) {
      // Show remaining idp section when hitting the add account button
      case 'previousSelection__addAccount':
        showRemaining(); break;
      // toggle edit/done button
      case 'previousSelection__toggleLabel':
      case 'previousSelection__edit':
      case 'previousSelection__done':
        fireClickEvent(e.target); break;
      // handle pressing the disable/delete button
      case 'idp__deleteDisable':
      case 'idp__delete':
      case 'idp__disabled':
        handleDeleteDisable(e, previouslySelectedIdps); break;
      // select an idp
      case 'wayf__idp':
      case 'idp__content':
      case 'idp__title':
      case 'idp__submit':
      case 'idp__form':
      case 'idp__logo':
        submitForm(e, previouslySelectedIdps); break;
      case 'wayf__eduIdLink':
        handleIdpBanner(e); break;
      case 'cta__showForm':
        toggleFormFieldsAndButton(); break;
    }
  });
};
