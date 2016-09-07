var PreviousIdpChoices = function (previousIdpChoices) {
    this.renderIn = function (element) {
        _.chain(previousIdpChoices)
            .map(function (previousIdpChoice) {
                return {
                    idp: $('#selection a[data-idp]').filter(function () {
                        return $(this).data('idp') === previousIdpChoice.idp;
                    }),
                    count: previousIdpChoice.count
                };
            })
            .sortBy('count')
            .reverse()
            .pluck('idp')
            .each(function (idpElement) {
                element.append(idpElement);
            });
    };

    this.getPreviousIdpChoices = function () {
        return previousIdpChoices;
    }
};

PreviousIdpChoices.fromJson = function (json) {
    var previousIdpChoices = JSON.parse(json);

    if (previousIdpChoices.length > 0 && previousIdpChoices[0].idp === void 0) {
        // Clear the array if it does not yet use the new format which includes the count.
        previousIdpChoices = [];
    }

    // Transforms [100, 2, 40] => [2, 40, 100] => { 2: 0, 40: 1, 100: 2 }
    var normalisedIdpCounts = _.chain(previousIdpChoices).pluck('count').uniq().sort().invert().value();

    previousIdpChoices = _.map(previousIdpChoices, function (idp) {
        // Enables IdPs to more easily take over the top position (most-used), ie. they can't get too far apart.
        return {idp: idp.idp, count: parseInt(normalisedIdpCounts[idp.count]) + 1};
    });

    return new PreviousIdpChoices(previousIdpChoices);
};
