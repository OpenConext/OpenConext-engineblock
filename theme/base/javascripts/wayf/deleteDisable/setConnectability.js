import {replaceClass} from '../../utility/replaceClass';
import {noAccessConnectableClass, noAccessUnconnectableClass} from '../../selectors';

/**
 * Ensures the right content/texts are shown for the noAccess form, depending on whether or not the clicked Idp is connectable or not.
 *
 * @param noAccess        the noAccess section element
 * @param connectable     whether the clicked idp is connectable
 */
export const setConnectability = (noAccess, connectable) => {
  const currentState = noAccess.classList.contains(noAccessUnconnectableClass);

  if (currentState !== connectable) {
    replaceClass(noAccess, noAccessUnconnectableClass, noAccessConnectableClass);
  }

  if (!currentState && !connectable) {
    replaceClass(noAccess, noAccessConnectableClass, noAccessUnconnectableClass);
  }
};
