$(function(){
    

    $('li.entity .entity-id').each(function(index, element) {
        var appendCertificateChain = function(CertificateChain, element) {
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
            element.append(html + '<br style="clear: both" />');
        };

        var entityEl = $(element).parents('li.entity');
        var entityId = $.trim(this.innerHTML);

        $.getJSON('get-entity-certificate.php?eid=' + encodeURIComponent(entityId), function(data) {
            var entityMessagesTmpl = entityEl.find('.messages-template');
            entityMessagesTmpl = entityMessagesTmpl.tmpl({
                  Errors: data.Errors,
                  Warnings: data.Warnings
            }).appendTo(entityEl.find('.messages'));

            var certInfoEl = entityEl.find('div.entity-certificate-information');

            if (data.CertificateChain.length > 0) {
                appendCertificateChain(data.CertificateChain, entityEl.find('.entity-certificate-representation'));

                var notBeforeDate = new Date();
                notBeforeDate.setTime(data.CertificateChain[0].NotBefore.UnixTime * 1000);
                var notAfterDate  = new Date();
                notAfterDate.setTime(data.CertificateChain[0].NotAfter.UnixTime * 1000);

                var certInfoTmpl = entityEl.find('.entity-certificate-information-template');
                var certInfoTmpl = certInfoTmpl.tmpl({
                    'Subject': data.CertificateChain[0].Subject.DN,
                    'Starts_relative': (notBeforeDate.toUTCString()),
                    'Starts_natural': (DateHelper.distanceOfTimeInWords(new Date, notBeforeDate)),
                    'Ends_relative': (notAfterDate.toUTCString()),
                    'Ends_natural': (DateHelper.distanceOfTimeInWords(new Date, notAfterDate))
                }).appendTo(certInfoEl);
            }

            certInfoEl.find('img.loading-image').remove();
        });

        var endpointsEl         = entityEl.find('.entity-endpoints');
        var endpointsTemplateEl = entityEl.find('.entity-endpoint-template');

        $.getJSON('get-entity-endpoints.php?eid=' + encodeURIComponent(entityId), function(data) {
            for (var endpointName in data) {
                if (data.hasOwnProperty(endpointName)) {
                    var endpointEl = endpointsTemplateEl.tmpl({
                        Name: endpointName,
                        Url: data[endpointName].Url
                    });

                    if (data[endpointName].CertificateChain.length > 0) {
                        appendCertificateChain(
                                data[endpointName].CertificateChain,
                                endpointEl.find('.entity-endpoint-certificate-representation')
                        );

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

                    var entityMessagesTmpl = entityEl.find('.messages-template');
                    entityMessagesTmpl = entityMessagesTmpl.tmpl({
                          Errors: data[endpointName].Errors,
                          Warnings: data[endpointName].Warnings
                    }).appendTo(endpointEl.find('.messages'));

                    endpointEl.appendTo(endpointsEl);

                    //endpointsEl.find()

                }
            }
            endpointsEl.prev('img.loading-image').remove();
        });
    });
});