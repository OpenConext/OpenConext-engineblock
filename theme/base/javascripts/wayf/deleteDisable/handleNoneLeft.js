import {toggleVisibility} from '../../utility/toggleVisibility';
import {showRemaining} from '../utility/showRemaining';

/**
 * When the last idp is deleted from the previous selection list:
 * - hide the previous selection section
 * - show the remaining idp list section
 */
export const handleNoneLeft = () => {
  toggleVisibility(document.querySelector('.wayf__previousSelection'));
  showRemaining();
};
