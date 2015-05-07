var Discover = function () {

    var module = {

        setLanguage: function (lang) {
            library.lang = lang;
        },
        setSearchText: function (text) {
            library.searchText = text;
        },
        setIdpList: function (idpList) {
            library.idpList = idpList;
        },
        setSpEntityId: function (spEntityId) {
            library.spEntityId = spEntityId;
        },
        setSpName: function (spName) {
            library.spName = spName;
        },
        setSelectedEntityId: function (selectedEntityId) {
            library.selectedEntityId = selectedEntityId;
        },

        show: function () {
            // Restrict the form from submitting unless a valid idp has been chosen
            $('#IdpListForm').submit(function () {
                var selectedIdp = $('#Idp').attr('value');
                if (!selectedIdp) {
                    return false;
                }
                for (var idp in library.idpList) {
                    if (encodeURIComponent(library.idpList[idp].EntityID) === selectedIdp) {
                        return true;
                    }
                }
                return false;
            });

            //Get start organisations
            library.sortIdps();
            library.loadIdps($('#searchBox').val());

            $('#searchBox').focus();

            //Hook up searchbox event
            $('#searchBox').
                keyup(function (e) {
                    switch (e.which) {
                        case 40: // ignore the arrow keys
                        case 38:
                        case 37:
                        case 39:
                            break;
                        case 13: //return
                            $('#organisationsContainer li.selected').click();
                            break;
                        default:
                            library.loadIdps($(this).val());
                    }
                }).
                // To support HTML5 search reset (see Chrome)
                bind('search', function (e) {
                    library.loadIdps($('#searchBox').val());
                });

            library.initLinks();

            // In case of a preselected IdP fill the suggestion
            if (library.selectedEntityId !== '') {
                library.fillSuggestion();
            }

            if (library.selectedId != '') {
                library.selectSuggestion();
            }
        },

// help moved to template
        linkHelp: function () {
            library.initLinks();
        },
// help moved to template
        showHelp: function () {
            library.showHelp();
        }
    };

    var library = {
        lang: '',
        searchText: '',
        idpList: '',
        spEntityId: '',
        spName: '',
        selectedId: '',
        selectedEntityId: '',

        selectIdpJSON: function () {
            for (var idp in this.idpList) {
                if (this.idpList[idp].hasOwnProperty('EntityID') && this.idpList[idp]['EntityID'] == this.selectedEntityId) {
                    return this.idpList[idp];
                }
            }
            return null;
        },
// help moved to template
        initLinks: function () {
            $("#help_nav a").on("click", function () {
                library.showHelp();
            });
        },

        showRequestAccess: function (idpEntityId, idpName, spEntityId, spName) {
            var speed = 'fast';
            var params = {lang: library.lang, idpEntityId: idpEntityId, idpName: idpName, spEntityId: spEntityId, spName: spName};
            $.get('/authentication/idp/requestAccess?' + $.param(params), function (data) {
                $("#content").hide(speed);

                var requestAccess = $("#requestAccess");
                requestAccess.html(data);
                requestAccess.show(speed, function () {
                    $('#name').focus();
                    requestAccess.on('click','#cancel_request_access, #back_request_access', function () {
                        $("#requestAccess").hide(speed);
                        $("#content").show(speed);
                        $("#searchBox").focus();
                    });
                    requestAccess.on('click','#request_access_submit', function (e) {
                        e.preventDefault();
                        var formData = $('#request_access_form').serialize();
                        $.post('/authentication/idp/performRequestAccess', formData)
                            .done(function (data) {
                                $("#requestAccess").html(data);
                            });
                        return false;
                    });
                });
            });
        },

// help moved to template
        showHelp: function () {
            var speed = 'fast';
            if ($('#help:visible').length > 0 && $.trim($('#help:visible').html()) !== "") {
                return;
            }
            var helpType = $('#help_nav a').attr('data-help-type');
            $.get('/authentication/idp/help-' + helpType + '?lang=' + library.lang, function (data) {
                $("#content").hide(speed);
                $("#requestAccess").hide(speed);

                var help = $("#help");
                help.html(data);
                help.show(speed);

                library.prepareFaq();
            });
        },

// help moved to template
        prepareFaq: function () {
            //Attach click handler to open and close help items
            $("#faq li").click(function (e) {
                $(this).toggleClass("open");

                //Close all faq items except the clicked one
                $('#faq li').not(this).removeClass('open');
            });

            $("#back_link").on("click", function (e) {
                $("#help").hide('fast');
                $("#content").show('fast');

            });
        },

        fillSuggestion: function () {
            var idp = this.selectIdpJSON();
            if (idp) {
                this.selectedId = idp['ID'];

                idp['Name'] = this.resolveIdPName(idp, this.lang);
                idp['Alt'] = encodeURIComponent(idp['EntityID']);

                idp['Suggestion'] = 'Onze Suggestie:';
                if ((this.lang == 'en')) {
                    idp['Suggestion'] = 'Our Suggestion:';
                }

                if (idp['Access'] == 0) {
                    idp['noAccess'] = '<em>Geen toegang. &raquo;</em>';
                    if (this.lang == 'en') {
                        idp['noAccess'] = '<em>No access. &raquo;</em>';
                    }
                    idp['NoAccessClass'] = 'noAccess';
                    idp['Name'] = library.clipString(idp['Name'], 45); //Clip string to prevent overlap with 'No access' label
                }
                var html = $('#idpListSuggestionTemplate').tmpl(idp);
                $('#IdpSuggestion').append(html).click(function (e) {
                    e.preventDefault();
                    //action no access or access
                    if (idp['Access'] == 0) {
                        var idpEntityId = $(this).find("a").attr("alt");
                        var idpName = $(this).find("span").html();
                        library.showRequestAccess(idpEntityId, idpName, library.spEntityId, library.spName);
                    } else {
                        $('#Idp').attr('value', idp['Alt']);
                        $('#IdpListForm').submit();
                    }
                    return false;
                });

            } else {
                //apparantly there is a cookie for a selected IDP, but it's not allowed for this SP
                $('#IdpSuggestion').remove();
            }
        },

        /**
         * Clips a string and appends an ellipsis so that the resulting length equals the given max length
         *
         * @param string        input string
         * @param maxLength        desired string length
         * @return string        clipped string with ellipsis appended
         */
        clipString: function (string, maxLength) {
            var appendString = '...';

            if (string.length <= maxLength) {
                return string;
            } else {
                var clippedString = string.substring(0, maxLength - appendString.length);

                //If last character is a space, we remove that as well to avoid gaps between the string and the ellipsis
                if (clippedString.substring(clippedString.length - 1) == ' ') {
                    clippedString = clippedString.substring(0, clippedString.length - 1);
                }

                return clippedString + appendString;
            }
        },

        resolveIdPName: function (iDp, language) {
            var name = iDp['Name_' + language];
            if (name == undefined || name === '') {
                language = (language == 'en') ? 'nl' : 'en';
                var name = iDp['Name_' + language];
            }
            return name;
        },

        sortIdps: function () {
            this.idpList.sort(function (o1, o2) {
                var name1 = library.resolveIdPName(o1, library.lang);
                var name2 = library.resolveIdPName(o2, library.lang);

                var access1 = o1['Access'];
                var access2 = o2['Access'];

                if (access1 !== access2) {
                    return access2 - access1;
                } else {
                    return name1.localeCompare(name2);
                }

            });
        },

        /**
         * Loads idps, optionally filtered
         *
         * @param filter    string used to filter idps
         */
        loadIdps: function (filter, isSearch) {
            library.displayIdps(library.filterIdps(filter));
        },

        filterIdps: function (filter) {
            var filteredResults = [];

            // empty filter
            if (filter === '') {
                return this.idpList;
            }

            filter = filter.toLowerCase();

            // filter idps based on keywords
            for (var idp in this.idpList) {
                var inKeywords = false;
                // Filter first on keywords
                if (this.idpList[idp].hasOwnProperty('Keywords')) {
                    for (var keyword in this.idpList[idp]['Keywords']) {
                        if (this.idpList[idp]['Keywords'][keyword].toLowerCase().indexOf(filter) >= 0) {
                            inKeywords = true;
                        }
                    }
                    // Filter based on IdP Name
                }
                var nameProp = 'Name_' + this.lang;
                if (this.idpList[idp].hasOwnProperty(nameProp) && this.idpList[idp][nameProp].toLowerCase().indexOf(filter) >= 0) {
                    inKeywords = true;
                }

                if (inKeywords) {
                    filteredResults.push(this.idpList[idp]);
                }
            }
            return filteredResults;
        },

        /**
         * Takes a multidimensional array of idps and displays them in the results box
         *
         * @param results    array of idps
         */
        displayIdps: function (results) {
            $('#organisationsContainer li').removeClass('selected');
            this.selectSuggestion();

            //Display no results message if needed
            if (results.length == 0) {
                $('#noResultsMessage').show();
                $('#scrollViewport').hide();
            } else {
                $('#noResultsMessage').hide();
                $('#scrollViewport').show();

                //Clear results box
                $('#organisationsContainer').html('');

                //Loop through every idp, create a html snippet for it, and append it to the container
                for (var i = 0; i < results.length; i++) {
                    var result = results[i];

                    // create a new object for the idp
                    var idp = {};

                    idp['ID'] = result['ID'];
                    idp['Logo'] = result['Logo'];
                    idp['Name'] = library.resolveIdPName(result, this.lang);
                    idp['Alt'] = encodeURIComponent(result['EntityID']);
                    idp['NoAccess'] = '';
                    idp['NoAccessClass'] = '';

                    if (result['Access'] == 0) {
                        idp['NoAccess'] = 'Geen toegang. &raquo;';
                        if (this.lang == 'en') {
                            idp['NoAccess'] = 'No access. &raquo;';
                        }
                        idp['NoAccessClass'] = 'noAccess';
                        idp['Name'] = library.clipString(idp['Name'], 45); //Clip string to prevent overlap with 'No access' label
                    }

                    // Use jquery template to create html
                    if (matchMedia('only screen and (min-width: 768px)').matches) {
                        var html = $('#idpListTemplate').tmpl(idp);
                    }
                    else {
                        var html = $('#idpListTemplateMobile').tmpl(idp);
                    }

                    $('#organisationsContainer').append(html);
                }

                // Check whether this is a successful search and a selection has to be made
                if ($('#searchBox').val() !== '' && $('#searchBox').val() !== this.searchText) {
                    // search, select first in list
                    $('#organisationsContainer li:first').addClass('selected', '');
                }

                //Hook up for clicking a IdP
                $('#organisationsContainer li').click(function () {
                    var org = $(this).find('a').attr('alt');
                    //if no select suggestion
                    if (org == undefined) {
                        library.selectSuggestion();
                    }

                    //action no access or access
                    if ($(this).hasClass('noAccess')) {
                        var idpEntityId = $(this).find("a").attr("alt");
                        var idpName = $(this).find("span").html();
                        library.showRequestAccess(idpEntityId, idpName, library.spEntityId, library.spName);
                    } else {
                        $('#Idp').attr('value', org);
                        $('#IdpListForm').submit();
                    }

                    return false;
                });
            }
        },

        // set selection of suggestion in list
        selectSuggestion: function () {
            id = $('#organisationsContainer li#c' + this.selectedId).index();
            $('#organisationsContainer li#c' + this.selectedId).addClass('selected', '');
        }
    };

    return module;
};
