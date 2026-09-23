import {setCookie} from '../utility/setCookie';
import {configurationId, rememberChoiceId, idpFormSelector} from '../selectors';

export const rememberChoice = (element, entityId) => {
  const checkbox = document.getElementById(rememberChoiceId);
  if (!checkbox) return;
  if (!checkbox.checked) return;

  const config = JSON.parse(document.getElementById(configurationId).innerHTML);

  if (config.rememberChoicePerIdp) {
    const form = element.querySelector(idpFormSelector);
    if (!form) return;

    const rememberChoiceInput = form.elements['rememberChoice'];
    if (!rememberChoiceInput) return;

    rememberChoiceInput.value = '1';
    return;
  }

  setCookie(entityId, config.rememberChoiceCookieName, 365, '/', true);
};
