import {toggleVisibility} from '../utility/toggleVisibility';
import {attachClickHandlerToCancelButton} from './noAccess/attachClickHandlerToCancelButton';
import {setHiddenFieldValues} from './noAccess/setHiddenFieldValues';
import {cloneIdp} from './noAccess/cloneIdp';
import {hideSuccessMessage} from './noAccess/hideSuccessMessage';
import {attachClickHandlerToRequestButton} from './noAccess/attachClickHandlerToRequestButton';
import {setConnectablity} from './deleteDisable/setConnectability';
import {getData} from '../utility/getData';

export const handleClickingDisabledIdp = (element) => {
  const noAccess = document.querySelector('.wayf__noAccess');
  const li = noAccess.querySelector('.wayf__idpList > li');
  const form = noAccess.querySelector('.noAccess__requestForm');
  const parentSection = element.closest('section');
  const cloneOfIdp = cloneIdp(element);
  const isConnectable = getData(element, 'connectable') === 'true';

  setConnectablity(noAccess, isConnectable);
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

  // attach clickHandler for request access button
  attachClickHandlerToRequestButton(parentSection, noAccess, form);
};
