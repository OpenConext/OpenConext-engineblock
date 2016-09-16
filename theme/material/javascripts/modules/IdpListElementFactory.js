const placeholderImage = '/images/placeholder.png';

export class IdpListElementFactory {
    constructor(messages, maximumIdpResultsPerList) {
        this.messages = messages;
        this.maximumIdpResultsPerList = maximumIdpResultsPerList;
    }

    createIdpListElementWithSelectionButtons(idpList) {
        const idpListElement = document.createElement('div');
        idpListElement.className = 'idp-list';

        for (let i = 0; i < this.maximumIdpResultsPerList && i < idpList.length; i++) {
            const idpElement = document.createElement('a');
            idpElement.className = 'result active access';
            idpElement.setAttribute('href', '#');
            idpElement.setAttribute('tabindex', '-1');

            const logoElement = document.createElement('img');
            logoElement.className = 'logo';
            setLogoAndFallbackUrlOnElement(idpList[i].logo, placeholderImage, logoElement);

            const titleElement = document.createElement('h3');
            titleElement.textContent = idpList[i].displayTitle;

            const actionElement = document.createElement('span');
            actionElement.className = 'c-button white action';
            actionElement.textContent = this.messages['pressEnterToSelect'];

            idpElement.appendChild(logoElement);
            idpElement.appendChild(titleElement);
            idpElement.appendChild(actionElement);

            idpListElement.appendChild(idpElement);
        }

        if (idpList.length > this.maximumIdpResultsPerList) {
            const moreResultsMessage = this.messages['moreIdpResults'].replace(
                '%d',
                idpList.length - this.maximumIdpResultsPerList
            );
            idpListElement.appendChild(createMoreResultsElement(moreResultsMessage));
        }

        return idpListElement;
    }

    createIdpListElementWithDeleteButtons(idpList) {
        const idpListElement = document.createElement('div');
        idpListElement.className = 'idp-list show-buttons';

        for (let i = 0; i < this.maximumIdpResultsPerList && i < idpList.length; i++) {
            const idpElement = document.createElement('a');
            idpElement.className = 'result active access';
            idpElement.setAttribute('href', '#');
            idpElement.setAttribute('tabindex', '-1');

            const logoElement = document.createElement('img');
            logoElement.className = 'logo';
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

        if (idpList.length > this.maximumIdpResultsPerList) {
            const moreResultsMessage = this.messages['moreIdpResults'].replace(
                '%d',
                idpList.length - this.maximumIdpResultsPerList
            );
            idpListElement.appendChild(createMoreResultsElement(moreResultsMessage));
        }

        return idpListElement;
    }
}

function createMoreResultsElement(message) {
    const moreResultsElement = document.createElement('div');
    moreResultsElement.className = 'loading message';

    const indicatorElement = document.createElement('div');
    indicatorElement.className = 'logo';

    const indicatorLetterElement = document.createElement('div');
    indicatorLetterElement.className = 'letter';
    indicatorLetterElement.textContent = '+';
    indicatorElement.appendChild(indicatorLetterElement);

    const messageElement = document.createElement('p');
    messageElement.textContent = message;

    moreResultsElement.appendChild(indicatorElement);
    moreResultsElement.appendChild(messageElement);

    return moreResultsElement;
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
