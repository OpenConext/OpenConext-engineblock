export class RequestAccessModalHelper {
    constructor(requestAccessElement, scrollerElement, searchBarElement, requestAccessUrl) {
        this.requestAccessElement = requestAccessElement;
        this.scrollerElement      = scrollerElement;
        this.searchBarElement     = searchBarElement;
        this.requestAccessUrl     = requestAccessUrl;
    }

    openRequestAccessModal() {
        sendGetRequest(this.requestAccessUrl, (responseText) => this.renderRequestAccessModal(responseText));
    }

    renderRequestAccessModal(responseText) {
        document.body.style.overflowY = 'auto';
        this.requestAccessElement.innerHTML = responseText;

        this.scrollerElement.style.display = 'block';

        const $container        = this.scrollerElement.querySelector('#request-access-container');
        const $closeModalButton = this.requestAccessElement.querySelector('.close-modal');
        const $submitButton     = this.requestAccessElement.querySelector('#request_access_submit');
        const $nameField        = this.requestAccessElement.querySelector('#name');

        $container.removeEventListener('click', this.containerClickHandler());
        $container.addEventListener('click', this.containerClickHandler($container));

        if ($submitButton !== null) {
            $submitButton.removeEventListener('click', this.submitRequestAccessClickHandler());
            $submitButton.addEventListener('click', this.submitRequestAccessClickHandler());
        }

        if ($closeModalButton !== null) {
            $closeModalButton.removeEventListener('click', this.closeModalClickHandler());
            $closeModalButton.addEventListener('click', this.closeModalClickHandler());
        }

        $nameField.focus();
    }

    containerClickHandler(containerElement) {
        return () => {
            if (event.target === containerElement) {
                this.closeRequestAccessModal();
            }
        }
    }

    submitRequestAccessClickHandler() {
        return (event) => {
            event.preventDefault();
            const $requestAccessForm = this.requestAccessElement.querySelector('#request_access_form');

            const formData = {
                name: $requestAccessForm.name.value,
                email: $requestAccessForm.email.value,
                institution: $requestAccessForm.institution.value,
                comment: $requestAccessForm.comment.value
            };

            sendPostRequest('/authentication/idp/performRequestAccess', formData, (responseText) => {
                this.renderRequestAccessModal(responseText);
            });
        }
    }

    closeModalClickHandler() {
        return (event) => {
            event.preventDefault();
            this.closeRequestAccessModal();
        }
    }

    closeRequestAccessModal() {
        document.body.style.overflowY = null;
        this.scrollerElement.style.display = 'none';
        this.requestAccessElement.innerHTML = '';
        this.searchBarElement.focus();

        const $container = this.scrollerElement.querySelector('#request-access-container');
        $container.removeEventListener('click', this.containerClickHandler());
    }
}

function sendGetRequest(url, callback) {
    const request = new XMLHttpRequest;

    request.onreadystatechange = () => {
        if (request.readyState === 4 && request.status == 200) {
            callback(request.responseText);
        }
    };

    request.open('GET', url, true);
    request.send(null);
}

function sendPostRequest(url, jsonData, callback) {
    const request = new XMLHttpRequest;

    request.onreadystatechange = () => {
        if (request.readyState === 4 && request.status === 200) {
            callback(request.responseText);
        }
    };

    request.open('POST', url, true);
    request.setRequestHeader('Content-type', 'application/json');
    request.send(JSON.stringify(jsonData));
}
