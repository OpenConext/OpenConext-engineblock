import {toggleVisibility} from '../utility/toggleVisibility';
import {attachClickHandlerToCancelButton} from './noAccess/attachClickHandlerToCancelButton';
import {setHiddenFieldValues} from './noAccess/setHiddenFieldValues';
import {cloneIdp} from './noAccess/cloneIdp';
import {hideSuccessMessage} from './noAccess/hideSuccessMessage';
import {attachClickHandlerToRequestButton} from './noAccess/attachClickHandlerToRequestButton';
import {setConnectability} from './deleteDisable/setConnectability';
import {getData} from '../utility/getData';
import {idpClass, idpFormSelector, idpSelector, noAccessFormSelector, noAccessIdpTitleId, noAccessLi, noAccessSectionSelector, noAccessTitle} from '../selectors';

export const handleClickingDisabledIdp = (element) => {
  let target = element;
  const noAccess = document.querySelector(noAccessSectionSelector);
  const li = noAccess.querySelector(noAccessLi);
  const form = noAccess.querySelector(noAccessFormSelector);
  const parentSection = element.closest('section');
  if (!element.classList.contains(idpClass)) {
    target = element.closest(idpSelector);
  }
  const cloneOfIdp = cloneIdp(target);
  // ensure clone is not tabbable as there's no action to be taken there
  cloneOfIdp.setAttribute('tabindex', '-1');
  // change titleText of clone to represent state of clone
  cloneOfIdp.querySelector(`#${noAccessIdpTitleId}`).firstElementChild.innerHTML = getData(li, 'titlestart');
  // remove form so the login button is not there
  cloneOfIdp.querySelector(idpFormSelector).remove();
  const connectable = getData(target, 'connectable') === 'true';

  setConnectability(noAccess, connectable);
  toggleVisibility(parentSection);
  toggleVisibility(noAccess);

  // set focus so the new content is announced to screenreaders
  document.querySelector(noAccessTitle).focus();

  // empty list-item & insert the clone
  li.innerHTML = '';
  li.insertAdjacentElement('afterbegin', cloneOfIdp);

  // if successModal is visible, hide it
  hideSuccessMessage();

  // add idp entityId
  setHiddenFieldValues(target, form);

  // attach clickHandler for cancel button
  attachClickHandlerToCancelButton(parentSection, noAccess);

  // attach clickHandler for request access button
  attachClickHandlerToRequestButton(parentSection, noAccess, form);
};
