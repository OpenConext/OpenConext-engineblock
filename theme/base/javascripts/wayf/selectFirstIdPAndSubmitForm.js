import {selectAndSubmit} from "./submitForm";

/**
 * Selects the first visible IdP from the WAYF IdP list and submits that IdP
 *
 * @param previouslySelectedIdPs
 */
export const selectFirstIdPAndSubmitForm = (previouslySelectedIdPs) => {
  let idp = document.querySelector('.wayf__remainingIdps .wayf__idpList > li:first-child > .wayf__idp:not([data-weight="0"])');
  // If any visible IdPs where present, choose and submit that one
  if (idp) {
    selectAndSubmit(idp, previouslySelectedIdPs);
  }
};
