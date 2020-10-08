/**
 * Reset the index for all idps in the previous selection.
 * Happens after one is deleted from the list & html.
 * Once it's done indexing: set the focus to the first item in the list.
 */
export const reindexAndFocus = () => {
  const idpList = document.querySelectorAll('.wayf__previousSelection .wayf__idp');

  // slice the deleted item, and reindex all remaining ones
  idpList.forEach((idp, index) => {
    idp.setAttribute('data-index', String(index + 1));
  });

  // focus the first item
  idpList[0].focus();
};
