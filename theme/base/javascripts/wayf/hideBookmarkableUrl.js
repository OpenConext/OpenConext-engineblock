export const hideBookmarkableUrl = () => {
  const configEl = document.getElementById('wayf-configuration');
  if (!configEl) {
    return;
  }

  const config = JSON.parse(configEl.innerHTML);
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
