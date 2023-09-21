import Cookies from 'js-cookie';

export class RememberChoiceStorage {
    constructor(cookieName) {
        this.cookieName = cookieName;
    }

    save(entityId) {
        Cookies.set(this.cookieName, JSON.stringify(entityId), { expires: 365, path: '/' });
    }
}
