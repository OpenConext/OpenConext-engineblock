export const focusOnNextIdp = () => {
  const element = document.activeElement;
  const index = Number(element.getAttribute('data-index'));

  try {
    element
      .closest('.wayf__idpList')
      .querySelector(`.wayf__idp[data-index="${index + 1}"]`).focus();
  } catch(e) {
  }
};
