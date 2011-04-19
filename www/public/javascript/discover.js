
var Discover = function() {

    var module = {

        setLanguage : function(lang) {
            library.lang = lang;
        },

        setSearchText : function(text) {
            library.searchText = text;
        },

        setIdpList : function(idpList) {
            library.idpList = idpList;
        },

        setSelectedEntityId : function(selectedEntityId) {
            library.selectedEntityId = selectedEntityId;
        },

        show : function() {
            //Get current page to determine which functions to call
            var currentPage = $('body').attr('class');

            if (currentPage == 'index') {

                //Initialize keyboard navigator
                keyboardNavigator.init();

                //Create scrollbar
                $('#scrollViewport').jScrollPane({
                    maintainPosition: false,
                    enableKeyboardNavigation: false
                });

                //Get start organisations
                library.loadIdps($('#searchBox').val());

                //Hook up search box event
                $('#searchBox').typeWatch({
                    callback: library.loadIdps,
                    wait: 300,
                    captureLength: -1 //Capture empty value as well
                });

                //Disable or enable keyboardNavigator if search field gets or looses focus
                $('#searchBox').focus(function() {
                    // clear searchbox text on focus
                    if ($('#searchBox').val() == library.searchText) {
                        $('#searchBox').val('');
                    }
                });

                $('#searchBox').blur(function() {
                    keyboardNavigator.enabled = true;
                });

                $('#tabThumbs').click(library.showThumbs);
                $('#tabList').click(library.showList);

                // set thums/list view based on cookie
                if ($.cookie("tabs") == 'thumbs') {
                    library.showThumbs();
                }
                if ($.cookie("tabs") == 'list') {
                    library.showList();
                }

                // In case of a preselected IdP fill the suggestion
                if (library.selectedEntityId !== '') {
                    library.fillSuggestion();
                }

                if (library.selectedId != '') library.selectSuggestion();

            } else if (currentPage == 'help') {

                //Create scrollbar
                $('#scrollViewport').jScrollPane();

                //Attach click handler to open and close help items
                $("#faq li").click(function () {
                    $(this).toggleClass("open");

                    //Close all faq items except the clicked one
                    $('#faq li').not(this).removeClass('open');

                    //Reinitialise scrollbar
                    $('#scrollViewport').data('jsp').reinitialise();
                });
            }
        }
    };

    var library = {
        lang : '',
        searchText : '',
        idpList : '',
        selectedId : '',
        selectedEntityId : '',

        selectIdpJSON : function() {
            for (var idp in this.idpList) {
                if (this.idpList[idp].hasOwnProperty('EntityId') && this.idpList[idp]['EntityId'] == this.selectedEntityId) {
                    return this.idpList[idp];
                }
            }
            return null;
        },

        fillSuggestion : function() {
            var idp = this.selectIdpJSON();
            this.selectedId = idp['ID'];

            idp['Name'] = idp['Name_nl'];
            if ((this.lang == 'en') & (idp['Name_en']!=undefined)) {
                idp['Name'] = idp['Name_en'];
            }
            idp['Alt'] = encodeURIComponent(idp['EntityId']);

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
                idp['Name'] = this.clipString(idp['Name'], 45); //Clip string to prevent overlap with 'No access' label
            }
            var html = $('#idpListSuggestionTemplate').tmpl(idp);
            $('#IdpSuggestion').append(html).click(function() {
                //action no access or access
                if (idp['Access'] == 0) {
                    //TODO implemented action on no access
                } else {
                    $('#Idp').attr('value', decodeURIComponent(idp['EntityId']));
                    $('#IdpListForm').submit();
                }                
            });
        },

        /**
         * Clips a string and appends an ellipsis so that the resulting length equals the given max length
         *
         * @param string        input string
         * @param maxLength        desired string length
         * @return string        clipped string with ellipsis appended
         */
        clipString : function (string, maxLength) {
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

        /**
         * Loads idps, optionally filtered
         *
         * @param filter    string used to filter idps
         */
        loadIdps : function(filter) {
            if (filter == this.searchText) {
                filter = '';
            }
            library.displayIdps(library.filterIdps(filter));
        },

        filterIdps : function(filter) {
            var filteredResults = [];

            // empty filter
            if (filter === '') {
                return this.idpList;
            }

            // filter idps based on keywords
            for (var idp in this.idpList) {
                if (this.idpList[idp].hasOwnProperty('Keywords')) {
                    var inKeywords = false;
                    for (var keyword in this.idpList[idp]['Keywords']) {
                        if (this.idpList[idp]['Keywords'][keyword].toLowerCase().indexOf(filter) >= 0) {
                            inKeywords = true;
                        }
                    }
                    if (inKeywords) {
                        filteredResults.push(this.idpList[idp]);
                    }
                }
            }
            return filteredResults;
        },

        /**
         * Takes a multidimensional array of idps and displays them in the results box
         *
         * @param results    array of idps
         */
        displayIdps : function(results) {

            //Display no results message if needed
            if (results.length == 0) {
                $('#noResultsMessage').show();
                $('#scrollViewport').hide();
                $('#resultTabs').hide();
            } else {
                $('#noResultsMessage').hide();
                $('#scrollViewport').show();
                $('#resultTabs').show();

                //Clear results box
                $('#organisationsContainer').html('');

                //Loop through every idp, create a html snippet for it, and append it to the container
                for (i = 0; i < results.length; i++) {
                    var result = results[i];

                    // create a new object for the idp
                    var idp = {};

                    idp['ID'] = result['ID'];
                    idp['Logo'] = result['Logo'];

                    idp['Name'] = result['Name_nl'];
                    if ((this.lang == 'en') & (result['Name_en'] != undefined)) {
                        idp['Name'] = result['Name_en'];
                    }

                    idp['Alt'] = encodeURIComponent(result['EntityId']);
                    idp['NoAccess'] = '';
                    idp['NoAccessClass'] = '';

                    if (result['Access'] == 0) {
                        idp['NoAccess'] = '<em>Geen toegang. &raquo;</em>';
                        if (this.lang == 'en') {
                            idp['NoAccess'] = '<em>No access. &raquo;</em>';
                        }
                        idp['NoAccessClass'] = 'noAccess';
                        idp['Name'] = clipString(idp['Name'], 45); //Clip string to prevent overlap with 'No access' label
                    }

                    // Use jquery template to create html
                    var html = $('#idpListTemplate').tmpl(idp);
                    $('#organisationsContainer').append(html);

                }

                // Check whether there is a search and a selection has to be made
                if (($('#searchBox').val() == "") || ($('#searchBox').val() == this.searchText)) {
                    // no search no selection
                    keyboardNavigator.setSelectedIndex(-1);
                    $('#organisationsContainer li').removeClass('selected');
                } else {
                    // search, select first in list
                    keyboardNavigator.setSelectedIndex(0);
                    $('#organisationsContainer li:first').addClass('selected', '');
                }

                //Hook up onclick handler for keynavigator
                $('#organisationsContainer li').click(function() {

                    //check if there is a selected item
                    var org = $('ul#organisationsContainer li.selected a').attr('alt');
                    //if no select suggestion
                    if (org == undefined) {
                        library.selectSuggestion();
                    }

                    //action no access or access
                    if ($(this).hasClass('noAccess')) {
                        //TODO implemented action on no access
                    } else {
                        $('#Idp').attr('value', decodeURIComponent(org));
                        $('#IdpListForm').submit();
                    }

                    return false;
                });

                //Reinitialise scrollbar
                $('#scrollViewport').data('jsp').reinitialise();
            }
        },

        // set selection of suggestion in list
        selectSuggestion : function() {
            id = $('#organisationsContainer li#c' + this.selectedId).index();
            keyboardNavigator.setSelectedIndex(id);
            $('#organisationsContainer li#c' + this.selectedId).addClass('selected', '');
        },

        showThumbs : function() {
            // set cookie for tabs thums view
            $.cookie("tabs", "thumbs", { expires: 7 });

            //Toggle tab active class
            $('#tabThumbs').addClass('active');
            $('#tabList').removeClass('active');

            $('#organisationsContainer').addClass('thumbs');
            $('#organisationsContainer').removeClass('list');

            //Reinitialise scrollbar and keynavigator
            $('#scrollViewport').data('jsp').reinitialise();
            keyboardNavigator.setMode(keyboardNavigator.MODE_3COLUMN_GRID);
        },

        showList : function() {
            // set cookie for tabs list view
            $.cookie("tabs", "list", { expires: 7 });

            //Toggle tab active class
            $('#tabList').addClass('active');
            $('#tabThumbs').removeClass('active');

            $('#organisationsContainer').removeClass('thumbs');
            $('#organisationsContainer').addClass('list');

            //Reinitialise scrollbar and keynavigator
            $('#scrollViewport').data('jsp').reinitialise();
            keyboardNavigator.setMode(keyboardNavigator.MODE_LIST);
        }
    };

    return module;
}
