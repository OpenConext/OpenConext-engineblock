export function initConsentPage() {
    focusAcceptButton();
    initAttributesToggle();

    function focusAcceptButton() {
        document.getElementById('accept_terms_button').focus();
    }

    function initAttributesToggle() {
        document.querySelectorAll('tr.toggle-attributes a').forEach(
            (anchor) => anchor.addEventListener(
                'click',
                () => anchor.closest('tbody').classList.toggle('expanded')
            )
        );
    }
}
