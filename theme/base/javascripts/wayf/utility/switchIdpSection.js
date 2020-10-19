import {toggleVisibility} from '../../utility/toggleVisibility';

export const switchIdpSection = () => {
  const remainingIdps = document.querySelector('.wayf__remainingIdps');
  const previousIdps = document.querySelector('.wayf__previousSelection');

  toggleVisibility(remainingIdps);
  toggleVisibility(previousIdps);
};
