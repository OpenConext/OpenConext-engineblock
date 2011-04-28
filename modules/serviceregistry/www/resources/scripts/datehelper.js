var DateHelper = {
    distanceOfTimeInWords: function(from, to) {
        var distanceInSeconds = ((to - from) / 1000);
        var distanceInMinutes = Math.floor(distanceInSeconds / 60);
        
        var prefix = 'in ';
        var postfix = ' ago';

        if (distanceInMinutes < 0) {
            prefix = '';
            distanceInMinutes *= -1;
        } else {
            postfix = '';
        }

        if (distanceInMinutes == 0) {
            return prefix + 'less than a minute' + postfix; 
        }
        if (distanceInMinutes == 1) {
            return prefix + 'a minute' + postfix; 
        }
        if (distanceInMinutes < 45) {
            return prefix + distanceInMinutes + ' minutes' + postfix;
        }
        if (distanceInMinutes < 90) {
            return prefix + 'about 1 hour' + postfix; 
        }
        if (distanceInMinutes < 1440) {
            return prefix + 'about ' + Math.floor(distanceInMinutes / 60) + ' hours' + postfix;
        }
        if (distanceInMinutes < 2880) {
            return prefix + '1 day' + postfix; 
        }
        if (distanceInMinutes < 43200) {
            return prefix + Math.floor(distanceInMinutes / 1440) + ' days' + postfix;
        }
        if (distanceInMinutes < 86400) {
            return prefix + 'about 1 month' + postfix; 
        }
        if (distanceInMinutes < 525960) {
            return prefix + Math.floor(distanceInMinutes / 43200) + ' months' + postfix;
        }
        if (distanceInMinutes < 1051199) {
            return prefix + 'about 1 year' + postfix; 
        }

        return prefix + Math.floor(distanceInMinutes / 525960) + ' years' + postfix;
    }
};