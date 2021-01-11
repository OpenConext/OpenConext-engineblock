export const removeWeight = (element) => {
  try {
    element.removeAttribute('data-weight');
  } catch (e) {
    // We are now in IE11 territory
    if (!!element) {
      element.setAttribute('data-weight', null);
    }
  }
};
