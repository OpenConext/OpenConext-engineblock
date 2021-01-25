/**
 * Fire a clickEvent on an element
 *
 *  @param element
 */
export const fireClickEvent = async (element) => {
  const clickEvent = new MouseEvent('click');
  element.dispatchEvent(clickEvent);
};
