import {toggleVisibility} from '../utility/toggleVisibility';
import {attachClickHandlerToCancelButton} from './noAccess/attachClickHandlerToCancelButton';

export const handleClickingDisabledIdp = (element) => {
  const noAccess = document.querySelector('.wayf__noAccess');
  const li = noAccess.querySelector('.wayf__idpList > li');
  const parentSection = element.closest('section');
  const cloneOfIdp = element.cloneNode(true);

  toggleVisibility(parentSection);
  toggleVisibility(noAccess);

  // empty list-item & insert the clone
  li.innerHTML = '';
  li.insertAdjacentElement('afterbegin', cloneOfIdp);

  // attach clickHandler for cancel button
  attachClickHandlerToCancelButton(parentSection, noAccess);
};
