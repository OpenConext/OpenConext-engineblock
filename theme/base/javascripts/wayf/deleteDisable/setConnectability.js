/**
 * Ensures the right content/texts are shown for the noAccess form, depending on whether or not the clicked Idp is connectable or not.
 *
 * @param noAccess        the noAccess section element
 * @param connectable     whether the clicked idp is connectable
 */
export const setConnectability = (noAccess, connectable) => {
  const currentState = noAccess.classList.contains('wayf__noAccess--unconnectable');

  if (currentState !== connectable) {
    noAccess.classList.replace('wayf__noAccess--unconnectable', 'wayf__noAccess--connectable', );
  }

  if (!currentState && !connectable) {
    noAccess.classList.replace('wayf__noAccess--connectable', 'wayf__noAccess--unconnectable');
  }
};
