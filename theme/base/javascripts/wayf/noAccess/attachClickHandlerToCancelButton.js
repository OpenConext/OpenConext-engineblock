import {handleCancelNoAccess} from './handleCancelNoAccess';
import {getData} from '../../utility/getData';

export const attachClickHandlerToCancelButton = (parentSection, noAccess) => {
  const cancelButton = document.querySelector('.cta__cancel');
  const hasClickHandler = getData(cancelButton, 'clickhandled');

  // if clickHandler's already been attached, do not attach it again
  if (hasClickHandler) {
    return;
  }

  // add clickHandler
  cancelButton.addEventListener('click', (e) => handleCancelNoAccess(parentSection, noAccess));
  cancelButton.setAttribute('data-clickhandled', true);
};
