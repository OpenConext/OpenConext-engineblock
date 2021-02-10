import {idpClass, idpTitleClass, idpTemplateSelector, unconnectedIdpClass, unconnectedLiClass} from '../../selectors';
import {getData} from '../../utility/getData';

/**
 * Convert an array of li's containing idps to an html string.
 *
 * @param idpArray
 * @returns {string}
 */

export const convertIdpArraytoHtml = (idpArray) => {
  let idpHtml = '';
  idpArray.forEach((idp, index) => {
    idp.setAttribute('data-index', String(index + 1));
    if (!idp.firstElementChild) {
      try {
        idp.innerHtml = '';
        idp.appendChild(getIdpContent(idp));
      } catch (e) {
        console.log(e);
      }
    }
    idpHtml += idp.outerHTML;
  });

  return idpHtml;
};

function getIdpContent(idp) {
  const template = document.getElementById(idpTemplateSelector).firstElementChild;
  const count = getData(idp, 'count');
  const title = getData(idp, 'title');
  const describedby = getData(idp, 'describedby');
  const weight = getData(idp, 'weight');
  const id = getData(idp, 'entityid');
  const connectable = getData(idp, 'connectable');
  const defaultIdp = getData(idp, 'defaultidp');
  const logoUrl = getData(idp, 'url');
  const className = idp.classList.contains(unconnectedLiClass) ? `${idpClass} ${unconnectedIdpClass}` : idpClass;
  const clone = template.cloneNode(true);

  clone.setAttribute('data-count', count);
  clone.setAttribute('data-weight', weight);
  clone.setAttribute('data-entityid', id);
  clone.setAttribute('data-connectable', connectable);
  clone.setAttribute('class', className);
  clone.setAttribute('aria-describedby', describedby);
  if (defaultIdp) {
    clone.setAttribute('id', 'defaultIdp');
  }
  clone.querySelector(`.${idpTitleClass}`).innerText = title;
  clone.querySelector('[name="idp"]').value = id;
  clone.querySelector('img').setAttribute('src', logoUrl);

  return clone;
}
