// Temporary code to get wayf to function. Only submit by clicking on a IdP functions for now
export function initializeWayf() {
  const form = document.querySelector('.wayf__search');
  if (form !== null) {
    // Set a quick and dirty click listener on every IdP
    document.querySelectorAll('.wayf__idp').forEach((article) => {
      article.addEventListener('click', (event) => {
        event.preventDefault();
        // And set the EnittyId associated with that IdP on the hidden form field. And submit the form
        form.elements['form-idp'].value = event.target.closest('article').dataset.entityId;
        form.submit();
      });
    });
  }
}
