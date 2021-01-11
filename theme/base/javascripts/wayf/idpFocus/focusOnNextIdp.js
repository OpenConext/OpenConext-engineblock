export const focusOnNextIdp = () => {
  const nextSibling = document.activeElement.parentElement.nextElementSibling;

  if (!!nextSibling) {
    nextSibling.firstElementChild.focus();
  }
};
