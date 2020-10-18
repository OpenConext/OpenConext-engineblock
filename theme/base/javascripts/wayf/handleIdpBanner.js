export const handleIdpBanner = () => {
  // todo: ensure this is put on there in twig
  // todo: adjust showBanner twig file to add a link to the id of the banner idp
  // todo: ensure the banner idp gets a special property isBannerIdp in the backend so it can be filtered out in the twig
  const eduIdIdp = document.getElementById('idp__bannerIdp');
  eduIdIdp.scrollIntoView(); // todo: should not be necessary if it's a link + id
  eduIdIdp.focus();
};
