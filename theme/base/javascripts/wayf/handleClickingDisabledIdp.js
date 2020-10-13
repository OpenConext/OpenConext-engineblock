import {toggleVisibility} from '../utility/toggleVisibility';

export const handleClickingDisabledIdp = (element) => {
  const noAccess = document.querySelector('.wayf__noAccess');
  const li = noAccess.querySelector('.wayf__idpList > li');
  const parentSection = element.closest('section');
  const cloneOfIdp = element.cloneNode(true);

  toggleVisibility(parentSection);
  toggleVisibility(noAccess);
  li.innerHTML = '';
  li.insertAdjacentElement('afterbegin', cloneOfIdp);
};
