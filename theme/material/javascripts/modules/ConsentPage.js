export function initConsentPage() {
    const tippy = require('./../../../node_modules/tippy.js/dist/tippy.all.min.js');

    focusAcceptButton();
    initAttributesToggle();
    initTooltips();

    function focusAcceptButton() {
        document.getElementById('accept_terms_button').focus();
    }

    function initTooltips() {
        tippy(
            document.querySelectorAll('a.tooltip'),
            {
                animation: 'scale',
                arrow: true,
                duration: 200,
                placement: 'top',
                theme: 'light bordered'
            }
        );
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
