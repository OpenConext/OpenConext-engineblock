import {toggleVisibility} from '../../utility/toggleVisibility';

export const hideNoAccess = (parentSection, noAccessSection) => {
  toggleVisibility(noAccessSection);
  toggleVisibility(parentSection);
};
