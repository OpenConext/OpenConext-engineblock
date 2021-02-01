import {selectedIdpsListSelector} from '../../selectors';

export const hasSelectedIdpsInList = () => {
  return document.querySelector(selectedIdpsListSelector).children.length;
};
