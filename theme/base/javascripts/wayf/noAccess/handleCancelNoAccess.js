import {toggleVisibility} from '../../utility/toggleVisibility';

export const handleCancelNoAccess = (parentSection, noAccessSection) => {
  toggleVisibility(noAccessSection);
  toggleVisibility(parentSection);
  // todo show banner
};
