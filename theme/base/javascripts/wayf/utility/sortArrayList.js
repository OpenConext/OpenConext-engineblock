import {idpSelector, remainingIdpLiSelector, unconnectedIdpClass} from '../../selectors';
import {nodeListToArray} from '../../utility/nodeListToArray';

export const sortArrayList = (passedIdpArray, sortFunction) => {
  let idpArray = passedIdpArray;
  let withAccess;
  let noAccess;
  try {
    withAccess = getWithAccess(idpArray);
    noAccess = getNoAccess(idpArray);
  } catch (e) {
    idpArray = nodeListToArray(document.querySelectorAll(remainingIdpLiSelector));
    withAccess = idpArray.filter(node => !node.querySelector(idpSelector).classList.contains(unconnectedIdpClass));
    noAccess = getNoAccess(idpArray);
  }

  if (withAccess) {
    withAccess.sort(sortFunction);
  }

  if (noAccess) {
    noAccess.sort(sortFunction);
  }

  return [...withAccess, ...noAccess];
};

function getWithAccess(idpArray) {
  return idpArray.filter(node => !node.classList.contains('idpItem--noAccess'));
}

function getNoAccess(idpArray) {
  return idpArray.filter(node => node.classList.contains('idpItem--noAccess'));
}
