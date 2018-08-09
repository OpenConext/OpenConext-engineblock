export class MouseListener {
    constructor(idpPicker, searchBarElement, requestAccessModalHelper) {
        this.idpPicker        = idpPicker;
        this.searchBarElement = searchBarElement;
        this.requestAccessModalHelper = requestAccessModalHelper;
    }

    handle(target) {
        if (this.requestAccessModalHelper.modalIsOpen()) {

            // Don't interfere with the IDP selection list when
            // the request-access modal dialog is open.
            return;
        }

        if (document.activeElement === this.searchBarElement) {
            this.searchBarElement.blur();
            return;
        }

        var index = target.getAttribute('data-idp-list-index');
        if (index !== null) {
            this.idpPicker.focusOnIdpByIndex(index);
        }
    }
}
