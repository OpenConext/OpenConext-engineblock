export const sortArrayList = (idpArray, sortFunction) => {
  const withAccess = idpArray.filter(node => !node.querySelector('article').classList.contains('wayf__idp--noAccess'));

  const noAccess = idpArray.filter(node => node.querySelector('article').classList.contains('wayf__idp--noAccess'));
  withAccess.sort(sortFunction);
  noAccess.sort(sortFunction);

  return [...withAccess, ...noAccess];
};
