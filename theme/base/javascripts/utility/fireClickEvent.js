/**
 * Fire a clickEvent on an element
 *
 *  @param element
 */
export const fireClickEvent = (element) => {
  const clickEvent = new MouseEvent('click');
  element.dispatchEvent(clickEvent);
};
