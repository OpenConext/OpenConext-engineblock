import {IdpFilter} from "./IdpFilter";

export class IdpList {
    constructor(targetElement, idpList, idpListElementFactory) {
        this.targetElement         = targetElement;
        this.idpList               = idpList;
        this.idpListElementFactory = idpListElementFactory;

        this.filteredIdpList  = this.idpList;

        this.render();
    }

    filterBy(filterValue) {
        this.filteredIdpList = IdpFilter.filterIdpsByValue(this.idpList, filterValue);
        this.render();
    }

    narrowFilterBy(filterValue) {
        this.filteredIdpList = IdpFilter.filterIdpsByValue(this.filteredIdpList, filterValue);
        this.render();
    }

    getFilteredIdpByIndex(index) {
        return this.filteredIdpList[index];
    }

    addIdp(idp) {
        this.idpList.push(idp);
        this.sortIdpListAlphabetically();
        this.filteredIdpList = this.idpList;
        this.render();
    }

    sortIdpListAlphabetically() {
        this.idpList = this.idpList.sort((idpA, idpB) => {
            if (idpA.title < idpB.title) {
                return -1;
            } else if (idpA.title > idpB.title) {
                return 1;
            }

            return 0;
        });
    }

    render() {
        const $noResultsElement = this.targetElement.querySelector('.noresults');

        if (this.filteredIdpList.length === 0) {
            $noResultsElement.className = $noResultsElement.className.replace(' hidden', '');
        } else if ($noResultsElement.className.indexOf('hidden') === -1) {
            $noResultsElement.className += ' hidden';
        }

        const idpListElement = this.idpListElementFactory.createIdpListElementWithSelectionButtons(
            this.filteredIdpList
        );

        this.targetElement.replaceChild(idpListElement, this.targetElement.querySelector('.idp-list'));
    }
}


