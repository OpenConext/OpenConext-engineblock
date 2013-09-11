/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright Â© 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

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
        setSpEntityId : function(spEntityId) {
            library.spEntityId = spEntityId;
        },
        setSpName : function(spName) {
            library.spName = spName;
        },
        setUri : function(uri) {
            library.uri = uri;
        },
        setSelectedEntityId : function(selectedEntityId) {
            library.selectedEntityId = selectedEntityId;
        },

        show : function() {
            // Restrict the form from submitting unless a valid idp has been chosen
            $('#IdpListForm').submit(function() {
                var selectedIdp = $('#Idp').attr('value');
                if (!selectedIdp) {
                    return false;
                }
                for (var idp in library.idpList) {
                    if (encodeURIComponent(library.idpList[idp].EntityId) === selectedIdp) {
                        return true;
                    }
                }
                return false;
            });

            //Initialize keyboard navigator
            keyboardNavigator.init();

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
                        default:
                            library.loadIdps($('#searchBox').val());
                    }
                }).
                // To support HTML5 search reset (see Chrome)
                bind('search', function(e) {
                    library.loadIdps($('#searchBox').val());
                });

            //Disable or enable keyboardNavigator if search field gets or looses focus
            $('#searchBox').focus(function() {
                keyboardNavigator.enabled = false;
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

            library.initLinks();

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

            if (library.selectedId != '') {
                library.selectSuggestion();
            }
        },

        linkHelp: function() {
            library.initLinks();
        },

        showHelp: function() {
            library.showHelp();
        }
    };

    var library = {
        lang : '',
        searchText : '',
        idpList : '',
        spEntityId : '',
        spName : '',
        selectedId : '',
        selectedEntityId : '',
        uri : '',

        selectIdpJSON : function() {
            for (var idp in this.idpList) {
                if (this.idpList[idp].hasOwnProperty('EntityId') && this.idpList[idp]['EntityId'] == this.selectedEntityId) {
                    return this.idpList[idp];
                }
            }
            return null;
        },

        initLinks : function() {
            $("#help_nav a").live("click", function() {
                library.showHelp();
            });
        },

        showRequestAccess: function(idpEntityId, idpName, spEntityId, spName) {
            keyboardNavigator.enabled = false;
            var speed = 'fast';
            var params = {lang:library.lang, idpEntityId:idpEntityId, idpName:idpName, spEntityId:spEntityId, spName:spName};
            $.get('/authentication/idp/requestAccess?'+ $.param(params), function(data) {
                $("#content").hide(speed);

                var requestAccess = $("#requestAccess");
                requestAccess.html(data);
                requestAccess.show(speed, function() {
                    $('#cancel_request_access, #back_request_access').live("click",function(e){
                        $("#requestAccess").hide(speed);
                        $("#content").show(speed);
                        keyboardNavigator.enabled = true;

                    });
                    $('#request_access_submit').live("click",function(e){
                        e.preventDefault();
                        var formData = $('#request_access_form').serialize();
                        $.post('/authentication/idp/performRequestAccess', formData, function(data) {
                            $("#requestAccess").html(data);
                        });
                        keyboardNavigator.enabled = true;
                        return false;
                    });
                });
            });
        },


        showHelp: function() {
            var speed = 'fast';
            if ($('#help:visible').length > 0 && $.trim($('#help:visible').html()) !== "") {
                return;
            }
            $.get('/authentication/idp/help?lang='+library.lang, function(data) {
                $("#content").hide(speed);
                $("#requestAccess").hide(speed);

                var help = $("#help");
                help.html(data);
                help.show(speed);

                library.prepareFaq();
            });
        },

        prepareFaq : function() {
            //Attach click handler to open and close help items
            $("#faq li").click(function(e) {
                $(this).toggleClass("open");

                //Close all faq items except the clicked one
                $('#faq li').not(this).removeClass('open');
            });

            $("#back_link").live("click", function(e) {
                $("#help").hide('fast');
                $("#content").show('fast');

            });
        },

        fillSuggestion : function() {
            var idp = this.selectIdpJSON();
            if (idp) {
                this.selectedId = idp['ID'];

                idp['Name'] = this.resolveIdPName(idp, this.lang);
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
                    idp['Name'] = library.clipString(idp['Name'], 45); //Clip string to prevent overlap with 'No access' label
                }
                var html = $('#idpListSuggestionTemplate').tmpl(idp);
                $('#IdpSuggestion').append(html).click(function(e) {
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

        resolveIdPName : function(iDp, language) {
            var name = iDp['Name_' + language];
            if (name == undefined || name === '') {
                language = (language == 'en') ? 'nl' : 'en';
                var name = iDp['Name_' + language];
            }
            return name;
        },

        sortIdps : function() {
            this.idpList.sort(function(o1, o2){
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
        loadIdps : function(filter, isSearch) {
            if (filter == this.searchText) {
                filter = '';
            }
            library.displayIdps(library.filterIdps(filter));

            // Return focus on searchbox if it is a search
//            if (filter !== '') {
//                $('#searchBox').focus();
//                $('#searchBox').putCursorAtEnd();
//            }
        },

        filterIdps : function(filter) {
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
        displayIdps : function(results) {

            //Display no results message if needed
            if (results.length == 0) {
                $('#noResultsMessage').show();
                $('#scrollViewport').hide();
            } else {
                if (window.innerWidth>=768) {
                    $('#noResultsMessage').hide();
                    $('#scrollViewport').show();
                }

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
                    idp['Alt'] = encodeURIComponent(result['EntityId']);
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

            keyboardNavigator.setMode(keyboardNavigator.MODE_LIST);
        }

    };

    return module;
};