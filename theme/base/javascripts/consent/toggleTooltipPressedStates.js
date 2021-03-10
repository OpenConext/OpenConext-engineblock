import {tooltipLabelSelector} from '../selectors';
import {changeAriaExpanded} from '../utility/changeAriaExpanded';
import {changeAriaPressed} from '../utility/changeAriaPressed';

export const toggleTooltipPressedStates = () => {
  const tooltips = document.querySelectorAll(tooltipLabelSelector);

  tooltips.forEach(tooltip => {

    const id = tooltip.getAttribute('for');
    const checkbox = document.getElementById(id);
    tooltip.addEventListener('click', () => {
      const expanded = checkbox.getAttribute('aria-expanded');
      changeAriaExpanded(checkbox);
      changeAriaPressed(checkbox);
      setTimeout(() => {
        if (expanded === 'false') {
          const tooltipValue = document.querySelector(`[data-for="${id}"]`);
          tooltipValue.focus();
        } else {
          tooltip.focus();
        }
      }, 200);
    });
  });
};
