import {idpSelector} from '../../selectors';

export const reindexIdpArray = (idpArray) => {
  idpArray.forEach((idp, index) => {
    idp.querySelector(idpSelector).setAttribute('data-index', String(index + 1));
  });
};
