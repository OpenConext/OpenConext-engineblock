import {selectedIdpsListSelector} from '../../selectors';

export const hasSelectedIdps = () => {
  return document.querySelector(selectedIdpsListSelector).children.length;
};
