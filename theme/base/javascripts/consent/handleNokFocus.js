import {nokButtonSelectorForKeyboard, nokCheckboxId, nokSectionSelector, nokTitleSelector} from '../selectors';
import {focusAndSmoothScroll} from '../utility/focusAndSmoothScroll';
import {changeAriaPressed} from '../utility/changeAriaPressed';
import {changeAriaExpanded} from '../utility/changeAriaExpanded';

export const handleNokFocus = () => {
  const nokCheckbox = document.getElementById(nokCheckboxId);
  const nokTitle = document.querySelector(nokTitleSelector);
  const nokButton = document.querySelector(nokButtonSelectorForKeyboard);
  const nokSection = document.querySelector(nokSectionSelector);
  const isChecked = nokSection.classList.contains('hidden');

  if (!isChecked) {
    nokTitle.focus();
  } else {
    focusAndSmoothScroll(nokButton);
  }

  changeAriaPressed(nokCheckbox);
  changeAriaExpanded(nokCheckbox);
};
