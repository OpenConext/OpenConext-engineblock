/**
 * Ensures the right content/texts are shown for the noAccess form, depending on whether or not the clicked Idp is connectable or not.
 *
 * @param noAccess          the noAccess section element
 * @param isConnectable     whether the clicked idp is connectable
 */
export const setConnectablity = (noAccess, isConnectable) => {
  const isUnconnectable = noAccess.classList.contains('wayf__noAccess--unconnectable');

  if (isUnconnectable && isConnectable) {
    noAccess.classList.replace('wayf__noAccess--unconnectable', 'wayf__noAccess--connectable', );
  }

  if (!isConnectable && !isUnconnectable) {
    noAccess.classList.replace('wayf__noAccess--connectable', 'wayf__noAccess--unconnectable');
  }
};
