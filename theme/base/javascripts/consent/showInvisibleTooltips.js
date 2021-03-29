import {invisibleTooltipLabelsSelector} from '../selectors';

export const showInvisibleTooltips = () => {
  const invisibleTooltips = document.querySelectorAll(invisibleTooltipLabelsSelector);

  invisibleTooltips.forEach(label => {
    label.setAttribute('tabindex', "0");
  });
};
