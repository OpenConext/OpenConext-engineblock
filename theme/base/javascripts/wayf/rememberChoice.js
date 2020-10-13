import {setCookie} from '../utility/setCookie';

export const rememberChoice = (entityId) => {
  const checkbox = document.getElementById('rememberChoice');
  if (!checkbox) return;
  if (!checkbox.checked) return;

  const cookieName = JSON.parse(document.getElementById('wayf-configuration').innerHTML).rememberChoiceCookieName;
  setCookie(entityId, cookieName);
};
