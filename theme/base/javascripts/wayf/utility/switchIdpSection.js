import {toggleVisibility} from '../../utility/toggleVisibility';
import {focusOn} from "../../utility/focusOn";

export const switchIdpSection = () => {
  const remainingIdps = document.querySelector('.wayf__remainingIdps');
  const previousIdps = document.querySelector('.wayf__previousSelection');

  toggleVisibility(previousIdps);
  toggleVisibility(remainingIdps);

  if (remainingIdps.getAttribute('aria-hidden')) {
    remainingIdps.removeAttribute('aria-hidden');
    previousIdps.setAttribute('aria-hidden', 'true');
    return;
  }

  previousIdps.removeAttribute('aria-hidden');
  remainingIdps.setAttribute('aria-hidden', 'true');

  focusOn('.search__field');
};
