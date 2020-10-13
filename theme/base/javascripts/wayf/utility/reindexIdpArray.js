export const reindexIdpArray = (idpArray) => {
  idpArray.forEach((idp, index) => {
    idp.querySelector('.wayf__idp').setAttribute('data-index', String(index + 1));
  });
};
