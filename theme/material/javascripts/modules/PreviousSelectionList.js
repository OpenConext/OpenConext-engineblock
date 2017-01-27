import {IdpFilter} from "./IdpFilter";

export class PreviousSelectionList {
    constructor(targetElement, previousSelectionList, idpListElementFactory) {
        this.targetElement = targetElement;
        this.previousSelectionList = previousSelectionList;
        this.idpListElementFactory = idpListElementFactory;

        this.filteredPreviousSelectionList = this.previousSelectionList;
        this.editing = false;

        this.render();
    }

    getLengthOfFilteredList() {
        return this.filteredPreviousSelectionList.length;
    }

    hasElement(element) {
        return this.targetElement.querySelector('.idp-list') === element.parentNode;
    }

    filterBy(filterValue) {
        this.filteredPreviousSelectionList = IdpFilter.filterIdpsByValue(this.previousSelectionList, filterValue);
        this.render();
    }

    narrowFilterBy(filterValue) {
        this.filteredPreviousSelectionList = IdpFilter.filterIdpsByValue(
            this.filteredPreviousSelectionList,
            filterValue
        );
        this.render();
    }

    getFilteredIdpByIndex(index) {
        return this.filteredPreviousSelectionList[index];
    }

    getPreviousSelections() {
        return this.previousSelectionList;
    }

    getListUpdatedWith(idp) {
        const previousSelectionToUpdate = this.previousSelectionList.slice(0);
        const indexInPreviousSelection  = previousSelectionToUpdate.indexOf(idp);

        if (indexInPreviousSelection === -1) {
            idp.count = 1;
            previousSelectionToUpdate.push(idp);
        } else {
            const idpToUpdate = previousSelectionToUpdate[indexInPreviousSelection];
            idpToUpdate.count++;
            previousSelectionToUpdate[indexInPreviousSelection] = idpToUpdate;
        }

        return previousSelectionToUpdate.sort((idpA, idpB) => {
            return idpB.count - idpA.count;
        });
    }

    removeIdpByIndex(index) {
        this.previousSelectionList.splice(index, 1);
        this.filteredPreviousSelectionList = this.previousSelectionList;
        this.render();
    }

    render() {
        if (this.filteredPreviousSelectionList.length > 0) {
            this.targetElement.className = this.targetElement.className.replace(' hidden', '');
        } else if (this.targetElement.className.indexOf('hidden') === -1) {
            this.targetElement.className += ' hidden';
        }

        let idpListElement;
        if (this.editing === true) {
            idpListElement = this.idpListElementFactory.createIdpListElementWithDeleteButtons(this.filteredPreviousSelectionList);
        } else {
            idpListElement = this.idpListElementFactory.createIdpListElementWithSelectionButtons(this.filteredPreviousSelectionList);
        }

        this.targetElement.replaceChild(idpListElement, this.targetElement.querySelector('.idp-list'));
    }
}
