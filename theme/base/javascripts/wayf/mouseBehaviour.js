import {showRemaining} from './showRemaining';

export const mouseBehaviour = () => {
  // Show remaining idp section when hitting the add account button
  document
    .querySelector('.previousSelection__addAccount')
    .addEventListener('click', showRemaining);
};
