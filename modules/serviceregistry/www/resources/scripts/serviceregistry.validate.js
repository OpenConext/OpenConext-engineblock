$(function(){
    $('li.entity .entity-id').each(function(index, element) {
        var getCertificateVisualRepresentationHtml = function(CertificateChain) {
            var html = ''
            for (var i = 0; i < CertificateChain.length; i++) {
                var certificate = CertificateChain[i];
                html += '<div class="certificate-image-container"><img ' +
                            'src="resources/images/icons/certificate.png" ' +
                            'class="certificate"' +
                            'alt="" ' +
                            'title="' + certificate.Subject.DN + '" />' +
                    (certificate.SelfSigned?'<span class="selfsigned-overlay" title="Self Signed Certificate">SS</span>':'') +
                    (certificate.RootCa?'<span class="rootca-overlay" title="Root Certificate Authority">RootCA</span>':'') +
                    '</div>';

                if (i < CertificateChain.length-1) { // not at the end of the chain
                    html += '<img class="certificate-chain" src="resources/images/icons/chain.gif" alt="" title="" />';
                }
            }
            return html + '<br style="clear: both" />';
        };

        var entityEl = $(element).parents('li.entity');

        // Get the Entity ID from the current element
        var entityId = $.trim(this.innerHTML);

        $.getJSON('get-entity-certificate.php?eid=' + encodeURIComponent(entityId), function(data) {
            entityEl.find('.messages-template').tmpl({
                  Errors: data.Errors,
                  Warnings: data.Warnings
            }).appendTo(entityEl.find('.entity-messages'));

            var certInfoEl = entityEl.find('div.entity-certificate-information');

            if (data.CertificateChain.length > 0) {
                entityEl.find('.entity-certificate-representation').append(
                        getCertificateVisualRepresentationHtml(data.CertificateChain)
                );

                var notBeforeDate = new Date();
                notBeforeDate.setTime(data.CertificateChain[0].NotBefore.UnixTime * 1000);
                var notAfterDate  = new Date();
                notAfterDate.setTime(data.CertificateChain[0].NotAfter.UnixTime * 1000);

                entityEl.find('.entity-certificate-information-template').tmpl({
                    'Subject': data.CertificateChain[0].Subject.DN,
                    'Starts_relative': (notBeforeDate.toUTCString()),
                    'Starts_natural': (DateHelper.distanceOfTimeInWords(new Date, notBeforeDate)),
                    'Ends_relative': (notAfterDate.toUTCString()),
                    'Ends_natural': (DateHelper.distanceOfTimeInWords(new Date, notAfterDate))
                }).appendTo(certInfoEl);
            }

            certInfoEl.find('img.loading-image').remove();
        });

        $.getJSON('get-entity-endpoints.php?eid=' + encodeURIComponent(entityId), function(data) {
            var endpointsEl         = entityEl.find('.entity-endpoints');
            var endpointsTemplateEl = entityEl.find('.entity-endpoint-template');
            for (var endpointName in data) {
                if (data.hasOwnProperty(endpointName)) {
                    // Create endpoint node from template
                    var endpointEl = endpointsTemplateEl.tmpl({
                        Name: endpointName,
                        Url: data[endpointName].Url
                    });

                    // Add errors and warnings as messages
                    entityEl.find('.messages-template').tmpl({
                          Errors: data[endpointName].Errors,
                          Warnings: data[endpointName].Warnings
                    }).appendTo(endpointEl.find('.entity-endpoint-messages'));

                    // If we have certificates
                    if (data[endpointName].CertificateChain.length > 0) {
                        // Add the visual representation
                        endpointEl.find('.entity-endpoint-certificate-representation').append(
                            getCertificateVisualRepresentationHtml(data[endpointName].CertificateChain)
                        );

                        // Add the tabular data
                        var notBeforeDate = new Date();
                        notBeforeDate.setTime(data[endpointName].CertificateChain[0].NotBefore.UnixTime * 1000);
                        var notAfterDate  = new Date();
                        notAfterDate.setTime(data[endpointName].CertificateChain[0].NotAfter.UnixTime * 1000);

                        var certInfoTmpl = entityEl.find('.entity-certificate-information-template');
                        var certInfoTmpl = certInfoTmpl.tmpl({
                            'Subject': data[endpointName].CertificateChain[0].Subject.DN,
                            'Starts_relative': (notBeforeDate.toUTCString()),
                            'Starts_natural': (DateHelper.distanceOfTimeInWords(new Date, notBeforeDate)),
                            'Ends_relative': (notAfterDate.toUTCString()),
                            'Ends_natural': (DateHelper.distanceOfTimeInWords(new Date, notAfterDate))
                        }).appendTo(endpointEl.find('.entity-endpoint-certificate-information'));
                    }

                    endpointEl.appendTo(endpointsEl);
                }
            }
            endpointsEl.prev('img.loading-image').remove();
        });
    });
});