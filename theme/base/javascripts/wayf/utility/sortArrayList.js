import {idpTag, unconnectedIdpClass} from '../../selectors';

export const sortArrayList = (idpArray, sortFunction) => {
  const withAccess = idpArray.filter(node => !node.querySelector(idpTag).classList.contains(unconnectedIdpClass));

  const noAccess = idpArray.filter(node => node.querySelector(idpTag).classList.contains(unconnectedIdpClass));
  withAccess.sort(sortFunction);
  noAccess.sort(sortFunction);

  return [...withAccess, ...noAccess];
};
