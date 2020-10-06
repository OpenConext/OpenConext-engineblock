/**
 * Fire a clickEvent on an element
 *
 *  @param element
 */
export const fireClickEvent = (element) => {
  var clickEvent = new MouseEvent('click');
  element.dispatchEvent(clickEvent);
};
