import {submitForm} from './submitForm';
export const mouseBehaviour = (previouslySelectedIdps) => {
  // allow chosing an idp to login
  const idpLists = document
    .querySelectorAll('.wayf__idpList');
  idpLists.forEach(list => list.addEventListener('click', (e) => {
    submitForm(e, previouslySelectedIdps);
  }));

  // add a11y support for all labels with an aria-pressed attribute.
  const labels = document.querySelectorAll('label[aria-pressed]');
  handleAriaPressed(labels);

  // handle clicking eduId banner
  const eduId = document.querySelector('.wayf__eduIdLink');

  if (!!eduId) {
    eduId.addEventListener('click', handleIdpBanner);
  }
};
import {handleAriaPressed} from '../utility/handleAriaPressed';

import {handleIdpBanner} from './handleIdpBanner';
