/**
 * Replace SAML query parameters in the URL bar with ?feedback=bookmark to prevent broken bookmarks
 */
export const hideBookmarkableUrl = () => {
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
