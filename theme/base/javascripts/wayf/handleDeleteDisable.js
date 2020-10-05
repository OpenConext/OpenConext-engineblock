export const handleDeleteDisable = (e) => {
  console.log(e);
  let element = e.target;

  // in case of click the origin is the span, so setting the element needs to be done differently
  if (e.target.tagName === 'SPAN') {
    element = e.target.closest('.idp__deleteDisable');
  }

  // handle clicking disabled button
  if (element.querySelector('.idp__delete').style.display === 'none') {
    return;
  }

  const article = element.closest('article.wayf__idp');
  // todo: add logic here to remove element from selected items
  article.closest('li').remove();
};
