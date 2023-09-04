import Cookies from 'js-cookie';
import {switchIdpSection} from '../utility/switchIdpSection';

/**
 * When the last idp is deleted from the previous selection list:
 * - hide the previous selection section
 * - show the remaining idp list section
 */
export const handleNoneLeft = () => {
  const configuration = JSON.parse(document.getElementById('wayf-configuration').innerHTML);
  const cookieName = configuration.previousSelectionCookieName;

  Cookies.remove(cookieName);
  switchIdpSection();
};
