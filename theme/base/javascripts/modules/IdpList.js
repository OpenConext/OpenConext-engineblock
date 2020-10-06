import {filterIdpsByValue} from '../wayf/filterIdpsByValue';

export class IdpList {
    constructor(targetElement, idpList, idpListElementFactory, cutoffPointForShowingUnfilteredIdps) {
        this.targetElement              = targetElement;
        this.idpList                    = idpList;
        this.idpListElementFactory      = idpListElementFactory;

        this.filterValue                 = '';
        this.shouldHideUnfilteredIdpList = this.idpList.length > cutoffPointForShowingUnfilteredIdps;
        this.filteredIdpList             = this.idpList;

        this.render();
    }

    filterBy(filterValue) {
        this.filterValue = filterValue;
        this.filteredIdpList = filterIdpsByValue(this.idpList, filterValue);
        this.render();
    }

    narrowFilterBy(filterValue) {
        this.filterValue = filterValue;
        this.filteredIdpList = filterIdpsByValue(this.filteredIdpList, filterValue);
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

    render() {
        const noResultsElement = this.targetElement.querySelector('.noresults');

        if (this.filteredIdpList.length === 0) {
            noResultsElement.classList.remove('hidden');
        } else if (noResultsElement.className.indexOf('hidden') === -1) {
            noResultsElement.classList.add('hidden');
        }

        if (this.filterValue.trim() === '' && this.shouldHideUnfilteredIdpList) {
            this.targetElement.classList.add('hidden');

            // In order to maintain correct state of the list, render no elements in the page
            const idpListElement = this.idpListElementFactory.createIdpListElementWithSelectionButtons([]);
            this.targetElement.replaceChild(idpListElement, this.targetElement.querySelector('.idp-list'));

            return;
        }

        this.targetElement.classList.remove('hidden');

        const idpListElement = this.idpListElementFactory.createIdpListElementWithSelectionButtons(
            this.filteredIdpList
        );

        this.targetElement.replaceChild(idpListElement, this.targetElement.querySelector('.idp-list'));
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
}


