const placeholderImage = '/images/placeholder.png';

export class IdpListElementFactory {
    constructor(messages) {
        this.messages = messages;
    }

    createIdpListElementWithSelectionButtons(idpList) {
        const idpListElement = document.createElement('div');
        idpListElement.className = 'idp-list';

        for (let i = 0; i < idpList.length; i++) {
            const idpElement = document.createElement('a');
            idpElement.className = 'result active';

            if (idpList[i].connected) {
                idpElement.className += ' access';
            } else {
                idpElement.className += ' noaccess';
            }

            idpElement.setAttribute('href', '#');
            idpElement.setAttribute('tabindex', '-1');

            const logoElement = document.createElement('img');
            logoElement.className = 'logo';
            setLogoAndFallbackUrlOnElement(idpList[i].logo, placeholderImage, logoElement);

            const titleElement = document.createElement('h3');
            titleElement.textContent = idpList[i].displayTitle;

            const actionElement = document.createElement('span');
            actionElement.className = 'c-button white action';

            if (idpList[i].connected) {
                actionElement.textContent = this.messages['pressEnterToSelect'];
            } else {
                actionElement.textContent = this.messages['requestAccess'];
            }

            idpElement.appendChild(logoElement);
            idpElement.appendChild(titleElement);
            idpElement.appendChild(actionElement);

            idpListElement.appendChild(idpElement);
        }

        return idpListElement;
    }

    createIdpListElementWithDeleteButtons(idpList) {
        const idpListElement = document.createElement('div');
        idpListElement.className = 'idp-list show-buttons';

        for (let i = 0; i < idpList.length; i++) {
            const idpElement = document.createElement('a');
            idpElement.className = 'result active access';
            idpElement.setAttribute('href', '#');
            idpElement.setAttribute('tabindex', '-1');

            const logoElement = document.createElement('img');
            logoElement.className = 'logo';
            logoElement.setAttribute('alt', '');
            setLogoAndFallbackUrlOnElement(idpList[i].logo, placeholderImage, logoElement);

            const titleElement = document.createElement('h3');
            titleElement.textContent = idpList[i].displayTitle;

            const actionElement = document.createElement('span');
            actionElement.className = 'c-button action outline deleteable img';

            const imageElement = document.createElement('img');
            imageElement.className = 'deleteable';
            imageElement.setAttribute('src', '/images/cross.svg');
            imageElement.setAttribute('alt', 'delete');
            actionElement.appendChild(imageElement);

            idpElement.appendChild(logoElement);
            idpElement.appendChild(titleElement);
            idpElement.appendChild(actionElement);

            idpListElement.appendChild(idpElement);
        }

        return idpListElement;
    }
}

let failedLogos = {};
function setLogoAndFallbackUrlOnElement(logoUrl, fallbackUrl, logoElement) {
    if (failedLogos[logoUrl] === true) {
        logoElement.setAttribute('src', fallbackUrl);
        return;
    }

    const logoErrorListener = () => {
        logoElement.removeEventListener('error', logoErrorListener);
        logoElement.setAttribute('src', fallbackUrl);
        failedLogos[logoUrl] = true;
    };
    logoElement.addEventListener('error', logoErrorListener);
    logoElement.setAttribute('src', logoUrl);
}
