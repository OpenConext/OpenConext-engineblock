import {showRemaining} from './showRemaining';
import {fireClickEvent} from '../utility/fireClickEvent';

export const keyboardBehaviour = () => {
  document.querySelector('body').addEventListener('keydown', function(e) {
    if (e.keyCode === 13) {
      var target = e.target;
      switch (target.className) {
        // Show remaining idp section when hitting the add account button
        case 'previousSelection__addAccount':
          showRemaining(); break;
        // handle pressing the edit/done button
        case 'previousSelection__toggleLabel':
          fireClickEvent(e.target); break;
      }
    }
  });
};
