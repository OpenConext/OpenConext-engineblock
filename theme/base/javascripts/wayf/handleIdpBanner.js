export const handleIdpBanner = (event) => {
  event.preventDefault();

  const eduIdIdp = document.getElementById('defaultIdp');
  eduIdIdp.scrollIntoView(true);
  eduIdIdp.focus();
};
