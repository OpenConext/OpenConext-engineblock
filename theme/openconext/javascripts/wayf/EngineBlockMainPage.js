export function replaceMetadataCertificateLinkTexts() {
    const $metadataCertificateLinks = document.querySelectorAll('dl.metadata-certificates-list a');

    for (let i = 0; i < $metadataCertificateLinks.length; i++) {
        const link = $metadataCertificateLinks[i];

        if (link.getAttribute('data-external-link') === "true") {
            continue;
        }

        // Set absolute URLs of anchors as display text
        link.textContent = window.location.protocol + '//' + window.location.hostname + link.getAttribute('href');
    }
}


