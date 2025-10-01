import {hideInvisibleTooltips} from './hideInvisibleTooltips';
import {showInvisibleTooltips} from './showInvisibleTooltips';
import {
  firstInvisibleAttributeSelector,
  invisibleTooltipLabelsSelector,
  openToggleLabelSelector,
  showMoreCheckboxId
} from '../selectors';
import {changeAriaExpanded} from '../utility/changeAriaExpanded';
import {changeAriaPressed} from '../utility/changeAriaPressed';

export const handleInvisibleTooltips = () => {
  const firstInvisibleTooltip = document.querySelector(invisibleTooltipLabelsSelector);
  const firstInvisibleAttribute = document.querySelector(firstInvisibleAttributeSelector);
  const showMoreCheckbox = document.getElementById(showMoreCheckboxId);
  const isHidden = firstInvisibleTooltip.getAttribute('tabindex') === "-1";

  changeAriaExpanded(showMoreCheckbox);
  changeAriaPressed(showMoreCheckbox);

  if (isHidden) {
    showInvisibleTooltips();
    firstInvisibleAttribute.setAttribute('tabindex', "-1");
    firstInvisibleAttribute.focus({preventScroll: true});
    return;
  }

  hideInvisibleTooltips();
  document.querySelector(openToggleLabelSelector).focus({preventScroll: true});
};
