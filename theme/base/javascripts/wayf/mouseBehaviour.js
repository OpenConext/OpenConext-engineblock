import {getData} from '../utility/getData';
import {submitForm} from './submitForm';
import {handleAriaPressed} from '../utility/handleAriaPressed';
import {handleIdpBanner} from './handleIdpBanner';

export const mouseBehaviour = (previouslySelectedIdps) => {
  // allow chosing an idp to login
  const idpLists = document
    .querySelectorAll('.wayf__idpList');
  idpLists.forEach(list => {
    const hasClickHandler = getData(list, 'clickhandled');

    if (!hasClickHandler) {
      list.addEventListener('click', (e) => {
        e.preventDefault();
        submitForm(e, previouslySelectedIdps);
      });
      list.setAttribute('data-clickhandled', 'true');
    }
  });

  // add a11y support for all labels with an aria-pressed attribute.
  const labels = document.querySelectorAll('label[aria-pressed]');
  handleAriaPressed(labels);

  // handle clicking eduId banner
  const eduId = document.querySelector('.wayf__eduIdLink');

  if (!!eduId) {
    eduId.addEventListener('click', handleIdpBanner);
  }
};
