import {removeWeight} from './removeWeight';

export const clearWeight = (idpArray) => {
  idpArray.forEach(li => {
    removeWeight(li.children[0]);
  });
};
