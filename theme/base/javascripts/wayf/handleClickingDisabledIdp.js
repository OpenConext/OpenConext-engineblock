import {toggleVisibility} from '../utility/toggleVisibility';
import {attachClickHandlerToCancelButton} from './noAccess/attachClickHandlerToCancelButton';
import {setHiddenFieldValues} from './noAccess/setHiddenFieldValues';
import {attachClickHandlerToForm} from './noAccess/attachClickHandlerToForm';
import {cloneIdp} from './noAccess/cloneIdp';
import {hideSuccessMessage} from './noAccess/hideSuccessMessage';

export const handleClickingDisabledIdp = (element) => {
  const noAccess = document.querySelector('.wayf__noAccess');
  const form = noAccess.querySelector('.noAccess__requestForm');
  const li = noAccess.querySelector('.wayf__idpList > li');
  const parentSection = element.closest('section');
  const cloneOfIdp = cloneIdp(element);

  toggleVisibility(parentSection);
  toggleVisibility(noAccess);

  // empty list-item & insert the clone
  li.innerHTML = '';
  li.insertAdjacentElement('afterbegin', cloneOfIdp);

  // if successModal is visible, hide it
  hideSuccessMessage();

  // add idp entityId
  setHiddenFieldValues(element, form);

  // attach clickHandler for cancel button
  attachClickHandlerToCancelButton(parentSection, noAccess);

  // attach clickHandler for form
  attachClickHandlerToForm(form, parentSection, noAccess);
};
