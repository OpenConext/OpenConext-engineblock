export class IdpFilter {
    static filterIdpsByValue(filterSet, filterValue) {
        return filterSet.filter(idp => {
            return idp.title.indexOf(filterValue) > -1
                || idp.entityId.indexOf(filterValue) > -1
                || idp.keywords.indexOf(filterValue) > -1;
        });
    }
}

