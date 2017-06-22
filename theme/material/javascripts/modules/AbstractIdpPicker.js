export class AbstractIdpPicker {
    constructor(searchForm, targetElement, idpList) {
        this.searchForm = searchForm;
        this.targetElement = targetElement;
        this.idpList = idpList;

        this.previousSearchTerm = '';
    }

    searchBy(searchTerm) {
        searchTerm = searchTerm.trim().toLowerCase();

        if (searchTerm === this.previousSearchTerm) {
            return;
        }

        if (searchTerm.length > this.previousSearchTerm.length) {
            this.narrowFilterBy(searchTerm);
        } else {
            this.filterBy(searchTerm);
        }

        this.updateFocus();

        this.previousSearchTerm = searchTerm;
    }

    narrowFilterBy(searchTerm) {
        this.idpList.narrowFilterBy(searchTerm);
    }

    filterBy(searchTerm) {
        this.idpList.filterBy(searchTerm);
    }

    updateFocus() {}
}
