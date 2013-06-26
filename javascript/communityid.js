/**
 * Classes enforce private and public members through the Module Pattern
 * (the vars outside are private, and what goes inside the return is public)
 * We use classes inside the "SCIRET" namespace
 * @see http://yuiblog.com/blog/2007/06/12/module-pattern/
 */

/**
 * Aliases definitions (functions, namespaces)
 */
YAHOO.namespace("commid");
COMMID = YAHOO.commid;

COMMID.utils = function() {
    return {
        evalScripts: function (el) {
            el = (typeof el =="string")? $(el) : el;
            var scripts = el.getElementsByTagName("script");
            for(var i=0; i < scripts.length;i++) {
                eval(scripts[i].innerHTML);
            }
        },

        replaceContent: function(responseObj, elId) {
            $(elId).innerHTML = responseObj.responseText;
            COMMID.utils.evalScripts(elId);
        },

        removeElement: function(element) {
            element.parentNode.removeChild(element);
        },

        hideElement: function(elName) {
            $(elName).style.visibility = "hidden";
        },

        unHideElement: function(elName) {
            $(elName).style.visibility = "visible";
        },

        asyncFailed: function() {
            alert(COMMID.lang['operation failed']);
        },

        addDatatableTranslations: function(datatableConfig) {
            datatableConfig.MSG_EMPTY = COMMID.lang["No records found."];
            datatableConfig.MSG_LOADING = COMMID.lang["Loading..."];
            datatableConfig.MSG_ERROR = COMMID.lang["Data error."];
            datatableConfig.MSG_SORTASC = COMMID.lang["Click to sort ascending"];
            datatableConfig.MSG_SORTDESC = COMMID.lang["Click to sort descending"];
        }
    }
}();

/**
* This is only to load YUI libs that don't need to be used immediately after
* the page is loaded
*/
COMMID.loader = function() {
    var loader;

    return {
        combine: true,
        base: null,

        insert: function(arrComponents, onSuccess, scope) {
            loader = new YAHOO.util.YUILoader({
                require: arrComponents,
                onSuccess: onSuccess,
                scope: scope,
                base: this.base,

                // uncomment to download debugging libs
                //filter: "DEBUG",

                combine: this.combine
            });
            loader.insert();
        }
    };
}();


/**
* MessageUsers
*/
COMMID.messageUsers = function() {
    return {
        send: function() {
            if (!confirm(COMMID.lang["Are you sure you wish to send this message to ALL users?"])) {
                return false;
            }

            document.messageUsersForm.messageType.value = $('bodyPlainWrapper').style.display == "block"? "plain" : "rich";

            return true;
        },

        switchToPlainText: function() {
            $('linkSwitchToPlain').style.display = "none";
            $('linkSwitchToRich').style.display = "block";

            $('bodyPlainWrapper').style.display = "block";
            $('bodyHTMLWrapper').style.display = "none";
        },

        switchToRichText: function() {
            $('linkSwitchToPlain').style.display = "block";
            $('linkSwitchToRich').style.display = "none";

            $('bodyPlainWrapper').style.display = "none";
            $('bodyHTMLWrapper').style.display = "block";
        }
    };
}();

COMMID.general = function() {

    return {
        editAccountInfo: function() {
            COMMID.utils.unHideElement("loadingAccountInfo");
            var transaction = YAHOO.util.Connect.asyncRequest(
                'GET',
                'profilegeneral/editaccountinfo?userid=' + COMMID.targetUserId,
                {
                    success: function (responseObj) {
                        COMMID.utils.replaceContent(responseObj, "accountInfo")
                        COMMID.utils.hideElement("loadingAccountInfo");
                    },
                    failure: COMMID.utils.asyncFailed
                });
        },

        changePassword: function() {
            COMMID.utils.unHideElement("loadingAccountInfo");
            var transaction = YAHOO.util.Connect.asyncRequest(
                'GET',
                'profilegeneral/changepassword?userid=' + COMMID.targetUserId,
                {
                    success: function (responseObj) {
                        COMMID.utils.replaceContent(responseObj, "accountInfo")
                        COMMID.utils.hideElement("loadingAccountInfo");
                    },
                    failure: COMMID.utils.asyncFailed
                });
        },

        toggleYubikey: function() {
            var authMethod = document.getElementById("authMethod");
            if (typeof authMethod == "undefined" || authMethod.value == 0) {
                $("yubikeyWrapper").style.display = "none";
            } else {
                $("yubikeyWrapper").style.display = "block";
            }
        }
    };
}();


COMMID.personalInfo = function() {
    return {
        erase: function(profileId) {
            if (!confirm(COMMID.lang["Are you sure you wish to delete this profile?"])) {
                return;
            }

            $("deleteprofile_" + profileId).submit();
        },

        cancel: function() {
            location.href = COMMID.baseDir + "/users/personalinfo";
        }
    };
}();

COMMID.changePassword = function() {
    return {
        save: function(userId) {
            YAHOO.util.Connect.setForm("changePasswordForm");
            YAHOO.util.Connect.asyncRequest(
                "POST",
                "profilegeneral/savepassword?userid=" + userId,
                {
                    success: function (responseObj) {COMMID.utils.replaceContent(responseObj, "accountInfo")},
                    failure: COMMID.utils.asyncFailed
                },
                null
            );
        },

        cancel: function(userId) {
            var transaction = YAHOO.util.Connect.asyncRequest(
                'GET',
                'profilegeneral/accountinfo?userid=' + userId,
                {
                    success: function (responseObj) {COMMID.utils.replaceContent(responseObj, "accountInfo")},
                    failure: COMMID.utils.asyncFailed
                }
            );
        }
    }
}();

COMMID.sitesList = function() {
    var myDataSource;
    var myDataSourceURL;
    var myDataTable;
    var myPaginator;
    var myTableConfig;
    var fieldsDialog;

    var buildQueryString = function (state,dt) { 
        return "startIndex=" + state.pagination.recordOffset + 
               "&results=" + state.pagination.rowsPerPage;
    }; 

    var formatOperationsColumn = function(elCell, oRecord, oColumn, oData) {
        var links = new Array();
        var recordId = oRecord.getId();

        if (oRecord.getData("trusted")) {
            links.push("<a href=\"#\" onclick=\"COMMID.sitesList.deny('" + recordId + "')\">" + COMMID.lang["deny"] + "</a>");
        } else {
            links.push("<a href=\"#\" onclick=\"COMMID.sitesList.allow('" + recordId + "')\" >" + COMMID.lang["allow"] + "</a>");
        }

        if (oRecord.getData("infoExchanged")) {
            links.push("<a href=\"#\" onclick=\"COMMID.sitesList.showInfo('" + recordId + "')\" >" + COMMID.lang["view info exchanged"] + "</a>");
        }

        links.push("<a href=\"#\" onclick=\"COMMID.sitesList.deleteSite('" + recordId + "')\">" + COMMID.lang["delete"] + "</a>");

        elCell.innerHTML = links.join("&nbsp;|&nbsp;");
    };

    var myColumnDefs = [
        {key: "site", label: COMMID.lang["Site"]},
        {key: "operations", label: "", formatter: formatOperationsColumn}
    ];

    return {
        init: function() {
            myDataSourceURL = COMMID.baseDir + "/sites/list?";

            fieldsDialog = new YAHOO.widget.Dialog(
                "fieldsDialog",
                {
                    width       : "30em",
                    effect      : {
                                    effect      : YAHOO.widget.ContainerEffect.FADE,
                                    duration    : 0.25
                                  },
                    fixedcenter : false,
                    modal       : true,
                    visible     : false,
                    draggable   : true
                }
            );
            fieldsDialog.render();
        },

        initTable: function() {
            myDataSource = new YAHOO.util.DataSource(myDataSourceURL);
            myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
            myDataSource.responseSchema = {
                resultsList: "records",
                fields: ["id", "site", "trusted", "infoExchanged"],
                metaFields : {
                    totalRecords: 'totalRecords'
                }
            };

            myPaginator = new YAHOO.widget.Paginator({
                alwaysVisible      : false,
                containers         : ['paging'],
                pageLinks          : 5,
                rowsPerPage        : 15,
                rowsPerPageOptions : [15,30,60],
                template           : "<strong>{CurrentPageReport}</strong> {PreviousPageLink} {PageLinks} {NextPageLink} {RowsPerPageDropdown}",
                pageReportTemplate      : "({currentPage} " + COMMID.lang["of"] + " {totalPages})",
                nextPageLinkLabel       : COMMID.lang["next"] + "&nbsp;&gt;",
                previousPageLinkLabel   : "&lt;&nbsp;" + COMMID.lang["prev"]
            });

            myTableConfig = {
                initialRequest         : 'startIndex=0&results=15',
                generateRequest        : buildQueryString,
                paginator              : myPaginator
            };
            COMMID.utils.addDatatableTranslations(myTableConfig);

            myDataTable = new YAHOO.widget.DataTable("dt", myColumnDefs, myDataSource, myTableConfig);
        },

        showInfo: function(recordId) {
            var oRecord = myDataTable.getRecord(recordId);
            var infoExchanged = oRecord.getData("infoExchanged");

            $("fieldsDialogSite").innerHTML = oRecord.getData("site");

            var fields = new Array();
            for (var fieldName in infoExchanged) {
                fields.push("<div class=\"yui-gf\"><div class=\"yui-u first\">" + fieldName + ":</div>\n"
                            +"<div class=\"yui-u\">" + infoExchanged[fieldName] + "</div></div>");
            }
            $("fieldsDialogDl").innerHTML = fields.join("\n");
            $("fieldsDialog").style.display = "block";
            fieldsDialog.show();
        },

        closeDialog: function() {
            fieldsDialog.hide();
        },

        deny: function(recordId) {
            var oRecord = myDataTable.getRecord(recordId);
            var site = oRecord.getData("site");
            if (!confirm(COMMID.lang["Are you sure you wish to deny trust to this site?"] + "\n\n" + site)) {
                return;
            }

            YAHOO.util.Connect.asyncRequest(
                "POST",
                COMMID.baseDir + "/sites/deny",
                {
                    success : function (responseObj) {
                        try {
                            var r = YAHOO.lang.JSON.parse(responseObj.responseText);
                            if (r.code == 200) {
                                alert(COMMID.lang["Trust the following site has been denied:"] + "\n\n" + site);
                                this.initTable();
                            }
                        } catch (e) {
                            alert(COMMID.lang["ERROR. The server returned:"] + "\n\n" + responseObj.responseText);
                        }
                    },
                    failure : COMMID.utils.asyncFailed,
                    scope   : this
                },
                "id=" + oRecord.getData("id")
            );
        },

        allow: function(recordId) {
            var oRecord = myDataTable.getRecord(recordId);
            var site = oRecord.getData("site");
            if (!confirm(COMMID.lang["Are you sure you wish to allow access to this site?"] + "\n\n" + site)) {
                return;
            }

            YAHOO.util.Connect.asyncRequest(
                "POST",
                COMMID.baseDir + "/sites/allow",
                {
                    success : function (responseObj) {
                        try {
                            var r = YAHOO.lang.JSON.parse(responseObj.responseText);
                            if (r.code == 200) {
                                alert(COMMID.lang["Trust to the following site has been granted:"] + "\n\n" + site);
                                this.initTable();
                            }
                        } catch (e) {
                            alert(COMMID.lang["ERROR. The server returned:"] + "\n\n" + responseObj.responseText);
                        }
                    },
                    failure : COMMID.utils.asyncFailed,
                    scope   : this
                },
                "id=" + oRecord.getData("id")
            );
        },

        deleteSite: function(recordId) {
            var oRecord = myDataTable.getRecord(recordId);
            var site = oRecord.getData("site");
            if (!confirm(COMMID.lang["Are you sure you wish to delete your relationship with this site?"] + "\n\n" + site)) {
                return;
            }

            YAHOO.util.Connect.asyncRequest(
                "POST",
                COMMID.baseDir + "/sites/delete",
                {
                    success : function (responseObj) {
                        try {
                            var r = YAHOO.lang.JSON.parse(responseObj.responseText);
                            if (r.code == 200) {
                                alert(COMMID.lang["Your relationship with the following site has been deleted:"] + "\n\n" + site);
                                this.initTable();
                            }
                        } catch (e) {
                            alert(COMMID.lang["ERROR. The server returned:"] + "\n\n" + responseObj.responseText);
                        }
                    },
                    failure : COMMID.utils.asyncFailed,
                    scope   : this
                },
                "id=" + oRecord.getData("id")
            );
        }
    };
}();

COMMID.historyList = function() {
    var myDataSource;
    var myDataSourceURL;
    var myDataTable;
    var myPaginator;
    var myTableConfig;

    var buildQueryString = function (state,dt) { 
        var request = "";
        if (state.sortedBy) {
            request += "sort=" + state.sortedBy.key + "&dir="
              + (state.sortedBy.dir === YAHOO.widget.DataTable.CLASS_ASC? 0 : 1) + "&";
        }

        request += "startIndex=" + state.pagination.recordOffset
                + "&results=" + state.pagination.rowsPerPage;

        return request;
    }; 

    var formatResultsColumn = function(elCell, oRecord, oColumn, oData) {
        switch(oRecord.getData("result")) {
            case 0:
                elCell.innerHTML = COMMID.lang["Denied"];
                break;
            case 1:
                elCell.innerHTML = COMMID.lang["Authorized"];
                break;
        }
    };

    var myColumnDefs = [
        {key: "date", label: COMMID.lang["Date"], sortable: true},
        {key: "site", label: COMMID.lang["Site"], sortable: true},
        {key: "ip", label: COMMID.lang["IP"], sortable: true},
        {key: "result", label: COMMID.lang["Result"], formatter: formatResultsColumn, sortable: true}
    ];

    var handleDataReturnPayload = function(oRequest, oResponse, oPayload) { 
        oPayload.totalRecords = oResponse.meta.totalRecords; 
        return oPayload; 
    };

    return {
        init: function() {
            myDataSourceURL = COMMID.baseDir + "/history/list?";
            myDataSource = new YAHOO.util.DataSource(myDataSourceURL);
            myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
            myDataSource.responseSchema = {
                resultsList: "records",
                fields: ["id", "date", "site", "ip", "result"],
                metaFields : {
                    totalRecords: 'totalRecords'
                }
            };

            myPaginator = new YAHOO.widget.Paginator({
                alwaysVisible      : false,
                containers         : ['paging'],
                pageLinks          : 5,
                rowsPerPage        : 15,
                rowsPerPageOptions : [15,30,60],
                template           : "<strong>{CurrentPageReport}</strong> {PreviousPageLink} {PageLinks} {NextPageLink} {RowsPerPageDropdown}",
                pageReportTemplate      : "({currentPage} " + COMMID.lang["of"] + " {totalPages})",
                nextPageLinkLabel       : COMMID.lang["next"] + "&nbsp;&gt;",
                previousPageLinkLabel   : "&lt;&nbsp;" + COMMID.lang["prev"]
            });

            myTableConfig = {
                initialRequest         : 'startIndex=0&results=15',
                generateRequest        : buildQueryString,
                dynamicData            : true,
                paginator              : myPaginator
            };
            COMMID.utils.addDatatableTranslations(myTableConfig);

            myDataTable = new YAHOO.widget.DataTable("dt", myColumnDefs, myDataSource, myTableConfig);
            myDataTable.handleDataReturnPayload = handleDataReturnPayload;
            myDataTable.subscribe('renderEvent', this.showClearHistoryBtn, this, true);
        },

        showClearHistoryBtn: function() {
            if (myDataTable.getRecordSet().getLength() > 0) {
                $("clearHistory").style.display = "block";
            } else {
                $("clearHistory").style.display = "none";
            }
        },

        clearEntries: function() {
            if (!confirm(COMMID.lang["Are you sure you wish to delete all the History Log?"])) {
                return;
            }

            YAHOO.util.Connect.asyncRequest(
                "POST",
                "history/clear",
                {
                    success : function(responseObj) {
                        try {
                            var r = YAHOO.lang.JSON.parse(responseObj.responseText);
                            if (r.code == 200) {
                                alert(COMMID.lang["The history log has been cleared"]);
                                this.init();
                            }
                        } catch (e) {
                            alert(COMMID.lang["ERROR. The server returned:"] + "\n\n" + responseObj.responseText);
                        }
                    },
                    failure : COMMID.utils.asyncFailed,
                    scope   : this
                }
            );
        }
    };
}();


COMMID.usersList = function() {
    var myDataSource;
    var myDataSourceURL;
    var myDataTable;
    var myPaginator;
    var myTableConfig;
    var currentFilter;
    var clickedOnSearchString = false;
    var searchString = "";

    var buildQueryString = function (state,dt) { 
        var request = "";
        if (state.sortedBy) {
            request += "sort=" + state.sortedBy.key + "&dir="
              + (state.sortedBy.dir === YAHOO.widget.DataTable.CLASS_ASC? 0 : 1) + "&";
        }

        request += "startIndex=" + state.pagination.recordOffset
                + "&results=" + state.pagination.rowsPerPage;

        return request;
    }; 

    var formatOperationsColumn = function(elCell, oRecord, oColumn, oData) {
        var links = new Array();

        links.push("<a href=\"" + COMMID.baseDir + "/users/profile?userid=" + oRecord.getData("id") + "\">"
                  + COMMID.lang["profile"] +    "</a>");

        if (COMMID.userRole == "admin" && COMMID.userId != oRecord.getData("id")) {
            links.push("<a href=\"javascript:void(0)\" onclick=\"COMMID.usersList.deleteUser('"+oRecord.getId()+"')\">" + COMMID.lang["delete"] + "</a>");
        }

        if (links.length > 0) {
            elCell.innerHTML = links.join("&nbsp;|&nbsp;");
        } else {
            elCell.innerHTML = "";
        }
    };

    var formatNameColumn = function(elCell, oRecord, oColumn, oData) {
        if (oRecord.getData("role") == "admin") {
            elCell.innerHTML = "<b>" + oRecord.getData("name") + "</b>";
        } else {
            elCell.innerHTML = oRecord.getData("name");
        }
    };

    var formatStatusColumn = function(elCell, oRecord, oColumn, oData) {
        if (oRecord.getData("role") == "admin") {
            elCell.innerHTML = "<b>" + oRecord.getData("status") + "</b>";
        } else {
            elCell.innerHTML = oRecord.getData("status");
        }

        if (oRecord.getData("reminders") == 1) {
            elCell.innerHTML += "<br />1 " + COMMID.lang["reminder"];
        } else if (oRecord.getData("reminders") > 1) {
            elCell.innerHTML += "<br />" + oRecord.getData("reminders") + " " + COMMID.lang["reminders"];
        }
    };

    var handleDataReturnPayload = function(oRequest, oResponse, oPayload) { 
        oPayload.totalRecords = oResponse.meta.totalRecords; 
        $("totalUsers").innerHTML = oResponse.meta.totalUsers;
        $("totalUnconfirmedUsers").innerHTML = oResponse.meta.totalUnconfirmedUsers;
        $("totalConfirmedUsers").innerHTML = oResponse.meta.totalUsers - oResponse.meta.totalUnconfirmedUsers;
        return oPayload; 
    };

    var deleteUserCompleted = function(oRecord, responseObj) {
        alert(responseObj.responseText);
        myDataTable.deleteRow(oRecord);
    };
    
    var deleteUnconfirmedCompleted = function(responseObj) {
        this.init("all");
    };

    return {
        init: function(filter) {
            currentFilter = filter;
            myDataSourceURL = COMMID.baseDir + "/users/userslist?filter=" + filter + "&";
            if (searchString != "") {
                myDataSourceURL += "search=" + encodeURIComponent(searchString) + "&";
            }
            myDataSource = new YAHOO.util.DataSource(myDataSourceURL);
            myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
            myDataSource.responseSchema = {
                resultsList: "records",
                fields: ["id", "name", "registration", "status", "reminders", "role"],
                metaFields : {
                    totalRecords            : 'totalRecords',
                    totalUsers              : 'totalUsers',
                    totalUnconfirmedUsers   : 'totalUnconfirmedUsers'
                }
            };

            myPaginator = new YAHOO.widget.Paginator({
                alwaysVisible      : false,
                containers         : ['paging'],
                pageLinks          : 5,
                rowsPerPage        : 15,
                rowsPerPageOptions : [15,30,60],
                template           : "<strong>{CurrentPageReport}</strong> {PreviousPageLink} {PageLinks} {NextPageLink} {RowsPerPageDropdown}",
                pageReportTemplate      : "({currentPage} " + COMMID.lang["of"] + " {totalPages})",
                nextPageLinkLabel       : COMMID.lang["next"] + "&nbsp;&gt;",
                previousPageLinkLabel   : "&lt;&nbsp;" + COMMID.lang["prev"]
            });

            myTableConfig = {
                initialRequest         : 'startIndex=0&results=15',
                generateRequest        : buildQueryString,
                dynamicData            : true,
                paginator              : myPaginator
            };
            COMMID.utils.addDatatableTranslations(myTableConfig);

            var myColumnDefs = [
                {key: "name", label: COMMID.lang["Name"], sortable: true, formatter: formatNameColumn},
                {key: "registration", label: COMMID.lang["Registration"], formatter: 'date', sortable: true},
                {key: "status", label: COMMID.lang["Status"], sortable: true, hidden: (filter == 'confirmed'), formatter: formatStatusColumn},
                {key: "operations", label: "", formatter: formatOperationsColumn}
            ];

            myDataTable = new YAHOO.widget.DataTable("dt", myColumnDefs, myDataSource, myTableConfig);
            myDataTable.handleDataReturnPayload = handleDataReturnPayload;

            switch (filter) {
                case 'all': 
                    $("links_topleft_all").className = "disabledLink";
                    $("links_topleft_confirmed").className = "enabledLink";
                    $("links_topleft_unconfirmed").className = "enabledLink";
                    $("deleteUnconfirmedSpan").style.display = "none";
                    $("sendReminderSpan").style.display = "none";
                    break;
                case 'confirmed':
                    $("links_topleft_all").className = "enabledLink";
                    $("links_topleft_confirmed").className = "disabledLink";
                    $("links_topleft_unconfirmed").className = "enabledLink";
                    $("deleteUnconfirmedSpan").style.display = "none";
                    $("sendReminderSpan").style.display = "none";
                    break;
                case 'unconfirmed':
                    $("links_topleft_all").className = "enabledLink";
                    $("links_topleft_confirmed").className = "enabledLink";
                    $("links_topleft_unconfirmed").className = "disabledLink";
                    $("deleteUnconfirmedSpan").style.display = "inline";
                    $("sendReminderSpan").style.display = "inline";
                    break;
            }
        },

        deleteUser: function(recordId) {
            var oRecord = myDataTable.getRecord(recordId);
            if (confirm(COMMID.lang["Are you sure you wish to delete the user"] + " " + oRecord.getData("name") + "?")) {
                var transaction = YAHOO.util.Connect.asyncRequest(
                    "POST",
                    COMMID.baseDir + "/users/manageusers/delete",
                    {
                        success: function (responseObj) {deleteUserCompleted(oRecord, responseObj);},
                        failure: function() {alert(COMMID.lang['operation failed'])}
                    },
                    "userid=" + oRecord.getData("id"));
            }
        },

        deleteUnconfirmed: function() {
            var olderThan = prompt(COMMID.lang["Delete unconfirmed accounts older than how many days?"], "5");
            if (olderThan === null) {
                return;
            }
            olderThan = parseInt(olderThan);
            if (isNaN(olderThan)) {
                alert(COMMID.lang["The value entered is incorrect"]);
            }

            YAHOO.util.Connect.asyncRequest(
                "POST",
                COMMID.baseDir + "/users/manageusers/deleteunconfirmed",
                {
                    success : deleteUnconfirmedCompleted,
                    failure : function() {alert(COMMID.lang['operation failed'])},
                    scope   : this
                },
                "olderthan=" + olderThan);
        },

        sendReminder: function() {
            var olderThan = prompt(COMMID.lang["Send reminder to accounts older than how many days?"], "5");
            if (olderThan === null) {
                return;
            }
            olderThan = parseInt(olderThan);
            if (isNaN(olderThan)) {
                alert(COMMID.lang["The value entered is incorrect"]);
            }

            YAHOO.util.Connect.asyncRequest(
                "POST",
                COMMID.baseDir + "/users/manageusers/sendreminder",
                {
                    success : deleteUnconfirmedCompleted,
                    failure : function() {alert(COMMID.lang['operation failed'])},
                    scope   : this
                },
                "olderthan=" + olderThan);
        },

        clickOnSearch: function () {
            if (!clickedOnSearchString) {
                // only erase field when first clicked
                $("search").value = "";
                clickedOnSearchString = true;
            }
        },

        submitSearch: function () {
            searchString = $("search").value;
            this.init(currentFilter);
        },

        clearSearch: function () {
            $("search").value = "";
            searchString = "";
            this.init(currentFilter);
        }
    };
}();


COMMID.editAccountInfo = function() {

    return {
        save: function() {
            YAHOO.util.Connect.setForm("accountInfoForm", true);
            YAHOO.util.Connect.asyncRequest(
                'POST',
                'profilegeneral/saveaccountinfo?userid=' + COMMID.targetUserId,
                {
                    upload: function (responseObj) {COMMID.utils.replaceContent(responseObj, "accountInfo")}
                },
                null
            );
        },

        cancel: function() {
            var transaction = YAHOO.util.Connect.asyncRequest(
                'GET',
                'profilegeneral/accountinfo?userid=' + COMMID.targetUserId,
                {
                    success: function (responseObj) {COMMID.utils.replaceContent(responseObj, "accountInfo")},
                    failure: COMMID.utils.asyncFailed
                }
            );
        }
    };
}();

COMMID.stats = function() {
    return {
        loadReport: function(report, div, params) {
            params = params || '';

            YAHOO.util.Connect.asyncRequest(
                "GET",
                "stats/reports/index/report/" + report + params,
                {
                    success: function (responseObj) {
                        COMMID.utils.replaceContent(responseObj, div)
                    },
                    failure: COMMID.utils.asyncFailed
                });
        }
    }
}();

COMMID.editArticle = function () {
    return {
        cancel: function (articleId) {
            if (articleId) {
                location.href = COMMID.baseDir + "/news/" + articleId;
            } else {
                location.href = COMMID.baseDir + "/news";
            }
        },

        remove: function (articleId) {
            if (!confirm(COMMID.lang['Are you sure you wish to delete this article?'])) {
                return;
            }

            location.href = COMMID.baseDir + "/news/edit/delete/id/" + articleId;
        }
    };
}();

COMMID.profileForm = function() {
    return {
        fetch: function(queryStr, profileId, showLoader) {
            if (showLoader) {
                COMMID.utils.unHideElement("loadingPersonalInfo");
            }
            var transaction = YAHOO.util.Connect.asyncRequest(
                'GET',
                COMMID.baseDir + '/profile' + queryStr + '&profile=' + profileId,
                {
                    success: function (responseObj) {
                        COMMID.utils.replaceContent(responseObj, "profileForm");
                    },
                    failure: COMMID.utils.asyncFailed
                });
        }
    };
}();
