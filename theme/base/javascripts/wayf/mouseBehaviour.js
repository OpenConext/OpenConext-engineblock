import {getData} from '../utility/getData';
import {handleAriaPressed} from '../utility/handleAriaPressed';
import {handleIdpBanner} from './handleIdpBanner';
import {defaultIdpSelector, idpListSelector} from '../selectors';
import {idpSubmitHandler} from '../handlers';

export const mouseBehaviour = () => {
  // allow chosing an idp to login
  const idpLists = document
    .querySelectorAll(idpListSelector);
  idpLists.forEach(list => {
    const hasClickHandler = getData(list, 'clickhandled');

    if (!hasClickHandler) {
      list.addEventListener('click', (e) => {
        e.preventDefault();
        idpSubmitHandler(e);
      });
      list.setAttribute('data-clickhandled', 'true');
    }
  });

  // add a11y support for all labels with an aria-pressed attribute.
  const labels = document.querySelectorAll('label[aria-pressed]');
  handleAriaPressed(labels);

  // handle clicking defaultIdp banner
  const defaultIdpLink = document.querySelector(defaultIdpSelector);

  if (!! defaultIdpLink) {
    defaultIdpLink.addEventListener('click', handleIdpBanner);
  }
};
