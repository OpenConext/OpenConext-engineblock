import {defaultIdpId} from '../selectors';

export const handleIdpBanner = (event) => {
  event.preventDefault();

  const defaultIdp = document.getElementById(defaultIdpId);
  defaultIdp.scrollIntoView(true);
  defaultIdp.focus();
};
