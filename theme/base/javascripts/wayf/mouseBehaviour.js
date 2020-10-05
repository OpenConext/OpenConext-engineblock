import {showRemaining} from './showRemaining';
import {handleDeleteDisable} from './handleDeleteDisable';

export const mouseBehaviour = () => {
  // Show remaining idp section when hitting the add account button
  document
    .querySelector('.previousSelection__addAccount')
    .addEventListener('click', showRemaining);
  document
    .querySelector('.idp__deleteDisable')
    .addEventListener('click', handleDeleteDisable);
};
