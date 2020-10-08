export const moveIdpToRemaining = (element) => {
  const clone = element.closest('li').cloneNode(true);
  clone.querySelector('.idp__deleteDisable').remove();
  const dad = document.querySelector('.wayf__remainingIdps .wayf__idpList');

  dad.prepend(clone);
};
