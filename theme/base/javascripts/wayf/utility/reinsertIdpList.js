import {convertIdpArraytoHtml} from './convertIdpArrayToHtml';
import {remainingIdpListSelector} from '../../selectors';

export const reinsertIdpList = (idpArray, listSelector = remainingIdpListSelector) => {
  const ul = document.querySelector(listSelector);
  ul.innerHTML = convertIdpArraytoHtml(idpArray);
};
