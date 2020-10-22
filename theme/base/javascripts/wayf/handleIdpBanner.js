export const handleIdpBanner = (event) => {
  event.preventDefault();

  const defaultIdp = document.getElementById('defaultIdp');
  defaultIdp.scrollIntoView(true);
  defaultIdp.focus();
};
