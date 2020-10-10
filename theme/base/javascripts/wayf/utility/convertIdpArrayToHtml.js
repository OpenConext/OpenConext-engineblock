/**
 * Convert an array of li's containing idps to an html string.
 *
 * @param idpArray
 * @returns {string}
 */
export const convertIdpArraytoHtml = (idpArray) => {
  let idpHtml = '';
  idpArray.forEach((idp, index) => {
    idp.querySelector('.wayf__idp').setAttribute('data-index', String(index + 1));
    idpHtml += idp.outerHTML;
  });

  return idpHtml;
};
