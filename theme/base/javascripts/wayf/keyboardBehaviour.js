import {showRemaining} from './showRemaining';

export const keyboardBehaviour = () => {
  document.querySelector('body').addEventListener('keydown', function(e) {
    if (e.keyCode === 13) {
      var target = e.target;
      switch (target.className) {
        // Show remaining idp section when hitting the add account button
        case 'previousSelection__addAccount':
          showRemaining(); break;
      }
    }
  });
};
