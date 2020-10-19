/**
 * Add aria-pressed support to elements with that attribute.
 *
 * @param elements
 */
export const handleAriaPressed = (elements) => {
  for (let i = 0; i < elements.length; i++) {
    elements[i].addEventListener('click', function(e) {
      const element = e.target;
      const ariaPressed = element.getAttribute('aria-pressed');
      const newValue = {
        false: true,
        true: false,
      };

      // We use the newValue object because ariaPressed is a string containing either "true" or "false".
      // Casting that to a boolean will, in both instances, give us true.
      // By using an object we can use array notation to access the right value based on what we get back from the attribute.
      element.setAttribute('aria-pressed', newValue[ariaPressed]);
    });
  }
};
