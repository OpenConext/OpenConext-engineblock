export const submitForm = (e) => {
  let element = e.target;
  if (e.target.tagName !== 'ARTICLE') {
    element = e.target.closest('.wayf__idp');
  }

  element.querySelector('.idp__form').submit();
};
