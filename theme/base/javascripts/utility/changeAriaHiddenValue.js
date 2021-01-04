export const changeAriaHiddenValue = (element) => {
  if(element.getAttribute('aria-hidden')) {
    element.removeAttribute('aria-hidden');
    return;
  }

  element.setAttribute('aria-hidden', 'true');
};
