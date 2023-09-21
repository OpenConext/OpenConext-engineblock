import Cookies from 'js-cookie';

export class PreviousSelectionStorage {
    constructor(cookieName) {
        this.cookieName = cookieName;
    }

    save(previousSelection) {
        const simplifiedPreviousSelection = previousSelection.map((idp) => {
            return {
                'idp': idp.entityId,
                'count': idp.count
            };
        });

        Cookies.set(this.cookieName, JSON.stringify(simplifiedPreviousSelection), { expires: 365, path: '/' }, );
    }
}
