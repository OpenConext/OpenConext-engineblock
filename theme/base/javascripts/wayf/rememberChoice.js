import {setCookie} from '../utility/setCookie';
import {configurationId, rememberChoiceId} from '../selectors';

export const rememberChoice = (entityId) => {
  const checkbox = document.getElementById(rememberChoiceId);
  if (!checkbox) return;
  if (!checkbox.checked) return;

  const cookieName = JSON.parse(document.getElementById(configurationId).innerHTML).rememberChoiceCookieName;
  setCookie(entityId, cookieName);
};
