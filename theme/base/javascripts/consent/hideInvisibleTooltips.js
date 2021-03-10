import {invisibleTooltipLabelsSelector} from '../selectors';

export const hideInvisibleTooltips = () => {
  const invisibleTooltips = document.querySelectorAll(invisibleTooltipLabelsSelector);
  invisibleTooltips.forEach(label => {
    label.setAttribute('tabindex', "-1");
  });
};
