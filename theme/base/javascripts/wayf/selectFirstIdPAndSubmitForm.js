import {selectAndSubmit} from "./submitForm";
import {firstRemainingIdpAfterSearchSelector} from '../selectors';

/**
 * Selects the first visible IdP from the WAYF IdP list and submits that IdP
 */
export const selectFirstIdPAndSubmitForm = () => {
  let idp = document.querySelector(firstRemainingIdpAfterSearchSelector);
  // If any visible IdPs where present, choose and submit that one
  if (idp) {
    selectAndSubmit(idp);
  }
};
