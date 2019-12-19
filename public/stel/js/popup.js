var flagPopup           = false,
    waitingPopup        = false,
    flagCall            = false,
    popupObservable     = {},
    originalTitle       = document.title,
    currentTitle        = document.title,
    currentFavicon      = $("link[rel='shortcut icon']").attr("href");


function phoneRingMiniPopup(data) {
    var phoneRingButton = $("#phone-ring-button");
    if(window.phoneRingTimeout) clearTimeout(window.phoneRingTimeout);
    else {
        phoneRingSound(1);
        phoneRingButton.show();
        phoneRingButton.find(".phonering-alo-ph-img-circle")
        .attr('title', `${data.customernumber}`)
        .tooltip('fixTitle').tooltip('show');
        var phoneringAloPhone = phoneRingButton.find(".phonering-alo-phone"); 
        if(data.direction == "inbound") phoneringAloPhone.removeClass("phonering-alo-hover").addClass("phonering-alo-red");
        else phoneringAloPhone.removeClass("phonering-alo-red").addClass("phonering-alo-hover");
    }
    window.phoneRingTimeout = setTimeout(() => {
        phoneRingSound(0);
        phoneRingButton.find(".phonering-alo-ph-img-circle").tooltip('hide');
        phoneRingButton.hide();
        window.phoneRingTimeout = 0;
    }, 2000);
}

function phoneRingSound(play = 1) {
    var tabsData    = JSON.parse(localStorage.getItem(TABSCACHE)),
        mainTab     = (tabsData.activeId == sessionStorage.getItem('pingId')) ? 1 : 0;
    if(mainTab && ENV.ringtone) {
        if(play) {
            playNotification.show(`<span>Ringing</span><audio autoplay loop>
                  <source src="${ENV.ringtone}" type="audio/mpeg">
                Your browser does not support the audio element.
                </audio>`, "warning");
        } else playNotification.hide();
    } 
}

async function startPopup(data) {
    window.waitingPopup = true;
    if(!document.getElementById("all-popup")) {
        var HTML = await $.ajax({
            url: ENV.baseUrl + "template/popup",
            type: "GET",
            data: {q: JSON.stringify(data)}
        });
        $("#popup-contain").append(HTML);
    }
    window.waitingPopup = false;
}

function onRingPopup(data) {
    var ele = document.getElementById("popup-window");
    if(ele) {
        var time = (data.currentTime - data.starttime) * 1000;
        var dialog = $(ele).data("kendoWindow");
        var title = ele.dataset.title;
        title += " (" + kendo.toString(new Date(time), "mm:ss") + " - RINGING)";
        dialog.title(title);
        if(typeof window.intervalTimePopup != 'undefined') {
            clearInterval(window.intervalTimePopup);
        }
        window.intervalTimePopup = setInterval(intervalTimePopupFunction, 1000, time, "RINGING");
    }
    window.popupObservable.onRingEvent(data);
}

function onCallPopup(data) {
    var ele = document.getElementById("popup-window");
    if(ele) {
        var time = (data.currentTime - data.answertime) * 1000;
        var dialog = $(ele).data("kendoWindow");
        var title = ele.dataset.title;
        title += " (" + kendo.toString(new Date(time), "mm:ss") + " - ONCALL)";
        dialog.title(title);
        if(typeof window.intervalTimePopup != 'undefined') {
            clearInterval(window.intervalTimePopup);
        }
        window.intervalTimePopup = setInterval(intervalTimePopupFunction, 1000, time, "ONCALL");
    }
    window.popupObservable.onCallEvent(data);
}

function onCompletePopup() {
    if(!window.flagCall && popupObservable.dataCall) {
        var calluuid = popupObservable.dataCall.calluuid;
        $.ajax({
            url: `${ENV.vApi}popup/complete/${calluuid}`,
            success: function(response){
                if(response.doc) {
                    if(response.doc.workstatus == "Complete") {
                        if(ele = document.getElementById("popup-window")) {
                            var title = ele.dataset.title,
                                callduration = response.doc.callduration ? Number(response.doc.callduration) : 0;
                            title += " (" + kendo.toString(new Date(Number(callduration) * 1000), "mm:ss") + " - "+response.doc.disposition + " - "+response.doc.userextension+")";
                            if($(ele).data("kendoWindow")) $(ele).data("kendoWindow").title(title);
                        }
                        if(response.doc.disposition == "ANSWERED")
                            tabTitle(false,  "public/stel/img/call-ico-green.png");
                        else tabTitle(false,  "public/stel/img/call-ico-red.png");
                        if(typeof window.intervalTimePopup != 'undefined') clearInterval(window.intervalTimePopup); 
                    }
                }
            }
        });
    }
}

function intervalTimePopupFunction(time, type) {
    var ele = document.getElementById("popup-window");
    if(ele) {
        time += 1000;
        var title = ele.dataset.title;
        title += ` (${kendo.toString(new Date(time), "mm:ss")} - ${type})`;
        if($(ele).data("kendoWindow")) $(ele).data("kendoWindow").title(title);
        onCompletePopup();
    }
}

function executeCall(e) {
    var data = JSON.parse(e.data);
    if(data) {
        window.flagCall = true;
        switch(data.workstatus) {
            case "Ring":
                //if(Math.floor(Date.now() / 1000) % 10 == 0) notification.show("Ringing", "warning");
                // Phone ring function
                phoneRingMiniPopup(data);
                //
                if(sessionStorage.getItem('callPopup') != 'false' && !window.waitingPopup && data.direction == "outbound") {
                    if(!window.flagPopup && typeof startPopup != "undefined") startPopup(data);
                    if(window.flagPopup && typeof onRingPopup != "undefined") onRingPopup(data);
                }
                break;
            case "On-Call":
                if(Math.floor(Date.now() / 1000) % 10 == 0) notification.show("On call", "success");
                if(sessionStorage.getItem('callPopup') != 'false' && !window.waitingPopup) {
                    if(!window.flagPopup && typeof startPopup != "undefined") startPopup(data);
                    if(window.flagPopup && typeof onCallPopup != "undefined") onCallPopup(data);
                }
                break;
            default:
                break;
        }
    } else window.flagCall = false;
}

function tabTitle(title = "", favicon = "") {
    if(title) {
        notifyTitle();
        notifyTitle(title);
        //document.title = title;
    } else if(typeof title == "string")  {
        notifyTitle();
        //document.title = currentTitle;
    }
    if(favicon) $("link[rel='shortcut icon']").attr("href", favicon);
    else $("link[rel='shortcut icon']").attr("href", currentFavicon);
}

function rePopup(id) {
    $.ajax({
        url: ENV.vApi + "cdr/detail/" + id,
        success: function(response) {
            if(response.calluuid) {
                startPopup(response);
                setTimeout(onCompletePopup, 2000);
            }
        }
    })
}

function hangupCall(calluuid) {
    $.ajax({
        url: ENV.vApi + "wfpbx/hangup",
        type: "POST",
        data: JSON.stringify({calluuid: calluuid}),
        contentType: "application/json; charset=utf-8",
        success: function(response) {
            notification.show(response.message, response.status ? "success" : "warning");
        }
    })
}

function transferCall(calluuid, extension) {
    $.ajax({
        url: ENV.vApi + "wfpbx/transfer",
        type: "POST",
        data: JSON.stringify({calluuid: calluuid, extension: extension}),
        contentType: "application/json; charset=utf-8",
        success: function(response) {
            notification.show(response.message, response.status ? "success" : "warning");
        }
    })
}

class Popup {

    constructor (dataCall) {
        Object.assign(this, {
            item : {},
            _dataCall : dataCall,
            openPopup: function(e) {
                e.sender.wrapper.css({ top: 50 });
                window.flagPopup = true;
                var title = ` ★ ${dataCall.direction.toUpperCase()} ★ ${dataCall.customernumber}`;
                title += ` ★ ${kendo.toString(new Date(dataCall.starttime * 1000), "H:mm")}`;
                var favicon = dataCall.callduration ? `public/stel/img/call-ico-${(dataCall.disposition == "ANSWERED") ? "green" : "red"}.png` : "public/stel/img/call-ico.png";
                if(typeof tabTitle != 'undefined') tabTitle(title,  favicon);
            },
            closePopup: function(){
                $("#popup-tabstrip").data("kendoTabStrip").destroy();
                $("#popup-window").data("kendoWindow").destroy();
                kendo.unbind($("#popup-contain"));
                $("#popup-contain").empty();
                window.popupObservable = {};
                window.flagPopup = false;
                if(typeof tabTitle != 'undefined') tabTitle();
            },
            activatePopup: function(e) {
                e.sender.wrapper.find(".k-i-refresh").parent("a").click((ev) => {
                    ev.preventDefault();
                    e.sender.close();
                    startPopup(this._dataCall);
                }).mouseover((mev) => {
                    if(!mev.currentTarget.dataset.originalTitle) {
                        mev.currentTarget.title  = NOTIFICATION.Refresh;
                        $(mev.currentTarget).tooltip('show');
                    }
                });
                e.sender.wrapper.find(".k-i-tri-state-indeterminate").parent("a").click((ev) => {
                    ev.preventDefault();
                    hangupCall(this._dataCall.calluuid);
                }).mouseover((mev) => {
                    if(!mev.currentTarget.dataset.originalTitle) {
                        mev.currentTarget.title  = NOTIFICATION.Hangup;
                        $(mev.currentTarget).tooltip('show');
                    }
                });
                e.sender.wrapper.find(".k-i-save").parent("a").click((ev) => {
                    ev.preventDefault();
                    localStorage.setItem("saved_popup_id", this._dataCall.calluuid);
                    $("#saved-popup-btn").addClass("btn-warning");
                    notification.show(NOTIFICATION.Save + " popup " +NOTIFICATION.success+ "!", "success");
                }).mouseover((mev) => {
                    if(!mev.currentTarget.dataset.originalTitle) {
                        mev.currentTarget.title  = NOTIFICATION.Save + " popup";
                        $(mev.currentTarget).tooltip('show');
                    }
                });
                e.sender.wrapper.find(".k-i-arrows-no-change").parent("a").click((ev) => {
                    ev.preventDefault();

                    $.ajax({
                        url: ENV.vApi + "widget/user_list",
                        success: (response) => {
                            try {
                                if(!response.total) throw "No user to transfer";
                                var buttons = {};
                                var countUser = 0;
                                response.data.forEach(doc => {
                                    if(doc.totalCurrentUser && doc.extension != ENV.extension) {
                                        buttons[doc.extension] = doc.agentname + ` (${doc.extension})`;
                                        countUser++;
                                    }
                                })
                                if(!countUser) {
                                    throw "No user to transfer";
                                }
                                buttons.cancel = true;
                                swal({
                                    title: NOTIFICATION.Transfer,
                                    text: NOTIFICATION.Transfer +" "+ NOTIFICATION.thiscall + "!",
                                    icon: "warning",
                                    buttons: buttons
                                })
                                .then((ext) => {
                                    if (ext !== null && ext !== false) {
                                        transferCall(this._dataCall.calluuid, ext);
                                    }
                                });
                            } catch(err) {
                                notification.show(err, "error");
                            }
                        }
                    }) 
                }).mouseover((mev) => {
                    if(!mev.currentTarget.dataset.originalTitle) {
                        mev.currentTarget.title  = NOTIFICATION.Transfer;
                        $(mev.currentTarget).tooltip('show');
                    }
                });
                e.sender.wrapper.find(".k-i-window-minimize").parent("a").mouseover((mev) => {
                    if(!mev.currentTarget.dataset.originalTitle) {
                        mev.currentTarget.title  = NOTIFICATION.Minimize;
                        $(mev.currentTarget).tooltip('show');
                    }
                });
                e.sender.wrapper.find(".k-i-window-maximize").parent("a").mouseover((mev) => {
                    if(!mev.currentTarget.dataset.originalTitle) {
                        mev.currentTarget.title  = NOTIFICATION.Maximize;
                        $(mev.currentTarget).tooltip('show');
                    }
                });
                e.sender.wrapper.find(".k-i-close").parent("a").mouseover((mev) => {
                    if(!mev.currentTarget.dataset.originalTitle) {
                        mev.currentTarget.title  = NOTIFICATION.Close;
                        $(mev.currentTarget).tooltip('show');
                    }
                });
                if(typeof this.afterActivePopup != "undefined") this.afterActivePopup();
            }
        });
    }

    get dataCall() {
        return this._dataCall;
    }

    assign(observable) {
        return Object.assign(this, observable);
    }

    open() {
        kendo.bind($("#popup-contain"), kendo.observable(this));
        var dialog = $("#popup-window").data("kendoWindow");
        if(dialog) dialog.center().open();
    }

    onRingEvent(data = {}) {
        if(this._dataCall.direction == "outbound" && !this._dataCall.calluuid && data.calluuid) {
            this._dataCall = data;
        }
    }

    onCallEvent(data = {}) {
        if(this._dataCall.direction == "outbound" && !this._dataCall.calluuid && data.calluuid) {
            this._dataCall = data;
        }
        tabTitle(false,  "public/stel/img/call-ico-blue.png");
    }
}