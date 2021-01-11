export const focusOnPreviousIdp = () => {
  const previousSibling = document.activeElement.parentElement.previousElementSibling;

  if (!!previousSibling) {
    previousSibling.firstElementChild.focus();
  }
};
