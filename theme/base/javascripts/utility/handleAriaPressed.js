import {changeAriaPressed} from '../utility/changeAriaPressed';

/**
 * Add aria-pressed support to elements with that attribute.
 *
 * @param elements
 */
export const handleAriaPressed = (elements) => {
  for (let i = 0; i < elements.length; i++) {
    const element = elements[i];

    switch (element.tagName) {
      case 'INPUT':
        attachAriaPressedHandler(element, 'change');
        break;
      default:
        attachAriaPressedHandler(element, 'click');
        break;
    }
  }
};

function attachAriaPressedHandler (element, type) {
  element.addEventListener(type, e => {
    changeAriaPressed(e.target);
  });
}
