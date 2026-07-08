import {configurationId} from '../selectors';

export const hideBookmarkableUrl = () => {
  const configEl = document.getElementById(configurationId);
  if (!configEl) {
    return;
  }

  let config;
  try {
    config = JSON.parse(configEl.innerHTML);
  } catch (e) {
    return;
  }

  if (!config.hideBookmarkableUrl) {
    return;
  }

  if (!window.history || !window.history.replaceState) {
    return;
  }

  const url = new URL(window.location.href);
  if (!url.searchParams.has('SAMLRequest')) {
    return;
  }

  const cleanUrl = new URL(window.location.pathname, window.location.origin);
  cleanUrl.searchParams.set('feedback', 'bookmark');
  window.history.replaceState(null, '', cleanUrl.toString());
};
