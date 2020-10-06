import {showRemaining} from './showRemaining';
import {fireClickEvent} from '../utility/fireClickEvent';
import {handleDeleteDisable} from './handleDeleteDisable';
import {submitForm} from './submitForm';
import {focusOnNextIdp} from './idpFocus/focusOnNextIdp';
import {focusOnPreviousIdp} from './idpFocus/focusOnPreviousIdp';
import {isFocusOn} from '../utility/isFocusOn';

export const keyboardBehaviour = () => {
  const ENTER      = 13;
  const ARROW_UP   = 38;
  const ARROW_DOWN = 40;
  const searchBar = document.querySelector('.search__field');
  const eduId = document.querySelector('.wayf__eduIdLink');
  const firstIdp = document.querySelector('.wayf__remainingIdps li:first-of-type > .wayf__idp');
  const lastIdp = document.querySelector('.wayf__remainingIdps li:last-of-type > .wayf__idp');

  document.querySelector('body').addEventListener('keydown', function(e) {
    if (e.keyCode === ENTER) {
      var target = e.target;
      switch (target.className) {
        // Show remaining idp section when hitting the add account button
        case 'previousSelection__addAccount':
          showRemaining(); break;
        // handle pressing the edit/done button
        case 'previousSelection__toggleLabel':
          fireClickEvent(e.target); break;
        // handle pressing the disable/delete button
        case 'idp__deleteDisable':
          handleDeleteDisable(e); break;
        // handle selecting an idp
        case 'wayf__idp':
        case 'idp__content':
        case 'idp__title':
        case 'idp__submit':
        case 'idp__form':
        case 'idp__logo':
          submitForm(e); break;
        default:
          console.log(e);
          console.log(target);
      }

      return;
    }

    if (e.keyCode === ARROW_DOWN) {
      if (isFocusOn(searchBar)) {
        eduId.focus();
        return;
      } else if (isFocusOn(eduId)) {
        firstIdp.focus();
        return;
      } else if (isFocusOn(lastIdp)) {
        searchBar.focus();
      }

      focusOnNextIdp();
      return;
    }

    if (e.keyCode === ARROW_UP) {
      if (isFocusOn(searchBar)) {
        lastIdp.focus();
      } else if (isFocusOn(firstIdp)) {
        eduId.focus();
        return;
      } else if (isFocusOn(eduId)) {
        searchBar.focus();
      }

      focusOnPreviousIdp();
      return;
    }
  });
};
