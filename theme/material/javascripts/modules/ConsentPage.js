export function initConsentPage() {
    focusAcceptButton();
    initAttributesToggle();

    function focusAcceptButton() {
        document.getElementById('accept_terms_button').focus();
    }

    function initAttributesToggle() {
        Array.prototype.forEach.call(
            document.querySelectorAll('tr.toggle-attributes a'),
            (anchor) => anchor.addEventListener(
                'click',
                () => anchor.closest('tbody').classList.toggle('expanded')
            )
        );
    }
}
