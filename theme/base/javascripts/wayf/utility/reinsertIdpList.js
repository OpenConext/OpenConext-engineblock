import {convertIdpArraytoHtml} from './convertIdpArrayToHtml';

export const reinsertIdpList = (idpArray, listSelector = '.wayf__remainingIdps .wayf__idpList') => {
  const ul = document.querySelector(listSelector);
  ul.innerHTML = convertIdpArraytoHtml(idpArray);
};
