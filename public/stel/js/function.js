/*
 * set type field for columns from model
 */
function set_type_columns(columns, model) {
    for(var i=0; i < columns.length; i++){
        if(columns[i].hasOwnProperty('field')) {
            var field = columns[i]['field'];
            if(model['fields'].hasOwnProperty(field) && model['fields'][field].hasOwnProperty('type')) {
                var type = model['fields'][field]['type'];
                columns[i]['type'] = type;
                switch(type) {
                    case "array":
                        columns[i]['template'] = "#=gridArray("+field+")#";
                        columns[i]['editor'] = readOnly;
                        columns[i]['filterable'] = false;
                        columns[i]['groupable'] = false;
                        break;
                    case "boolean":
                        columns[i]['template'] = "#=gridBoolean("+field+")#";
                    default:
                        break; 
                }
            } else columns[i]['type'] = 'string';
        }
    }
    return columns;
}

function set_values_columns(columns, field, values) {
    for (var i = 0; i < columns.length; i++) {
        if(columns[i].hasOwnProperty('field') && field === columns[i]['field']) {
            columns[i]['values'] = values;
        }
    }
    return columns;
}
/*
 * Usewith column.editor
 */
function readOnly(container, options) {
    container.removeClass("k-edit-cell");
    var value = options.model.get(options.field);
    if(value && value.length)
        container.html(gridArray(value));
    else container.text(value);
}
/*
 * Use with template column and readOnly function
 */
function gridArray(data = []) {
    var bs_color = HELPER.bsColors,
        template = [];
    if(data && data.length) {
        template = $.map($.makeArray(data), function(value, index) {
            return "<span class=\"label label-"+bs_color[index%6]+"\">"+value+"</span>";
        });;
    }
    return template.join(' ');
}

function gridBoolean(data) {
    return data ? `<i class="fa fa-check text-success"></i>` : `<i class="fa fa-times text-danger"></i>`;
}

/*
***
Kendo format
"d"		Renders the day of the month, from 1 through 31.
"dd"	The day of the month, from 01 through 31.
"ddd"	The abbreviated name of the day of the week.
"dddd"	The full name of the day of the week.
"f"		The tenths of a second in a date and time value.
"ff"	The hundredths of a second in a date and time value.
"fff"	The milliseconds in a date and time value.
"M"		The month, from 1 through 12.
"MM"	The month, from 01 through 12.
"MMM"	The abbreviated name of the month.
"MMMM"	The full name of the month.
"h"		The hour, using a 12-hour clock from 1 to 12.
"hh"	The hour, using a 12-hour clock from 01 to 12.
"H"		The hour, using a 24-hour clock from 1 to 23.
"HH"	The hour, using a 24-hour clock from 01 to 23.
"m"		The minute, from 0 through 59.
"mm"	The minute, from 00 through 59.
"s"		The second, from 0 through 59.
"ss"	The second, from 00 through 59.
"tt"	The AM/PM designator.
"yy"	The last two characters from the year value.
"yyyy"	The year full value.
"zzz"	The local timezone when using formats to parse UTC date strings.
**
*/
function gridDate(data, format = "dd/MM/yy H:mm") {
    return data ? kendo.toString(data, format) : "";
}

function gridTimestamp(data, format = "dd/MM/yy H:mm") {
    return data ? kendo.toString(new Date(data * 1000), format) : "";
}

function gridName(name) {
    return `<span class="grid-name">${name}</span>`;
}

function gridInterger(data) {
    return kendo.toString(Number(data), "n0");
}

function gridPhone(data) {
    var html = "<span></span>";
    if(data) {
        if(typeof data == "string") {
            html = `<a href="javascript:void(0)" class="label label-info" onclick="makeCallWithDialog('${data}')" title="Call now" data-role="tooltip" data-position="top">${data}</a>`;
        } else {
            if(data.length) {
                template = $.map($.makeArray(data), function(value, index) {
                    return `<a href="javascript:void(0)" class="label label-default" data-index="${index}" onclick="makeCallWithDialog('${value}')" title="Call now" data-role="tooltip" data-position="top">${value}</a>`;
                });;
                html = template.join(' ');
            }
        }
    }
    return html;
}
/*
 * Dropdownlist for grid cell
 */
function gridDropDownEditor(container, options) {
    var field = options.field;
    $('<input required name="' + field + '"/>')
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            dataTextField: 'value', 
            dataValueField: 'value',
            dataSource: dataSourceDropDownList(ENV.collection, field)
        });
}; 

function dataSourceDropDownList(collection, field, match = null, parse = res => res, pageSize = 20) {
    if(typeof match === "function") {
        parse = match;
        match = null;
    }
    return new kendo.data.DataSource({
        serverFiltering: true,
        serverPaging: true,
        pageSize: pageSize, 
        transport: {
            read: {
                url: ENV.vApi + `select/foreign/${collection}`,
                data: {field: field, match: match}
            },
            parameterMap: parameterMap
        },
        schema: {
            data: "data",
            parse: parse
        },
        error: errorDataSource
    })
}

function dataSourceJsonData(tags, parse = res => res) {
    return new kendo.data.DataSource({
        transport: {
            read: {
                url: ENV.vApi + "select/jsondata",
                data: {tags: tags}
            }
        },
        schema: {
            data: "data",
            parse: parse
        },
        error: errorDataSource
    })
}

function dataSourceDistinct(collection, field, match = null, parse = res => res) {
    if(typeof match === "function") {
        parse = match;
        match = null;
    }
    return new kendo.data.DataSource({
        serverFiltering: true,
        serverPaging: true,
        transport: {
            read: {
                url: ENV.vApi + `select/distinct/${collection}`,
                data: {field: field, match: match}
            },
            parameterMap: parameterMap
        },
        schema: {
            data: "data",
            parse: parse
        },
        error: errorDataSource
    })
}

function create_slug(str, split_str){
    str = str.toLowerCase();
    str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g,"a");
    str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g,"e");
    str = str.replace(/ì|í|ị|ỉ|ĩ/g,"i");
    str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g,"o");
    str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g,"u");
    str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g,"y");
    str = str.replace(/đ/g,"d");
    str = str.replace(/!|@|\$|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\'| |\"|\&|\#|\[|\]|~/g,"-");
    str = str.replace(/-+-/g,"-"); //thay thế 2- thành 1-
    str = str.replace(/^\-+|\-+$/g,"");//cắt bỏ ký tự - ở đầu và cuối chuỗi
    if(split_str) str = str.replace("-", split_str);
    return str;
}

function syncDataSource(e) {
    notification.show(` <big>${NOTIFICATION.operationSuccess}</big>`, "success");
}

function errorDataSource(e) {
    var errorText = NOTIFICATION.error + " "+e.status+". " + NOTIFICATION.detail + ": "+ (e.statusText || e.errorThrown).toString();
    swal({
      title: "Sorry!",
      text: errorText,
      icon: "warning",
      timer: 1000,
      button: {
        className: "btn-primary"
      }
    });
    sessionStorage.setItem("ERROR", errorText);
}

// Get params object from url

function getUrlParams(url) {
    var vars = {};
    var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
      vars[key] = decodeURIComponent(value);
    });
    return vars;
}

function httpBuildQuery(params) {
    if (typeof params === 'undefined' || typeof params !== 'object') {
        params = {};
        return params;
    }
    return Object.keys(params).map(key => key + '=' + encodeURIComponent(params[key])).join('&');
}

// Get unique array from array.filter(onlyUnique)

function onlyUnique(value, index, self) { 
    return self.indexOf(value) === index;
}

function changeStatus(code = 1, substatus = null) {
    $.ajax({
        url: ENV.vApi + "wfpbx/change_status",
        data: kendo.stringify({agentState: code, subState: substatus}),
        contentType: "application/json; charset=utf-8",
        type: "POST",
        success: function(e) {
            notification.show(e.message, e.status ? "success" : "error");
        },
        error: errorDataSource
    })
}

async function phoneForm(data = {}) {
    $rightForm = $("#right-form");
    var formHtml = await $.ajax({
        url: ENV.templateApi + "phone/form",
        data: data,
        error: errorDataSource
    });
    kendo.destroy($rightForm);
    $rightForm.empty();
    $rightForm.append(formHtml);
}

async function emailForm(data = {}) {
    $rightForm = $("#right-form");
    var formHtml = await $.ajax({
        url: ENV.templateApi + "email/form",
        data: data,
        error: errorDataSource
    });
    kendo.destroy($rightForm);
    $rightForm.empty();
    $rightForm.append(formHtml);
}

async function smsForm(data = {}) {
    $rightForm = $("#right-form");
    var formHtml = await $.ajax({
        url: ENV.templateApi + "sms/form",
        data: data,
        error: errorDataSource
    });
    kendo.destroy($rightForm);
    $rightForm.empty();
    $rightForm.append(formHtml);
}

function makeCall(phone, dialid = "", type = "") {
    if(ENV.softphone && !dialid) {
        location.href = `callto:${phone}`;
        notification.show(`Call ${phone}`);
    } else {
        $.ajax({
            url: ENV.vApi + "wfpbx/makeCall",
            data: {phone: phone, dialid: dialid, type: type},
            success: function(e) {
                notification.show(e.message, e.status ? "success" : "error");
                //if(typeof actionPhoneRing != "undefined") actionPhoneRing();
            },
            error: errorDataSource
        })
    };
}

function makeCallWithDialog(phone, dialid = "", type = "") {
    swal({
        title: `${NOTIFICATION.checkSure}?`,
        text: `${NOTIFICATION.callFor} ${phone}`,
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
    .then((sure) => {
        if (sure) {
            makeCall(phone, dialid, type);
        }
    });
}

function menuNotification(classString, number = 0) {
    if(!classString) return;
    var classArr = classString.split(" ");
    classArr.unshift("");
    classSelector = classArr.join(".");
    var $selector = $("#sidebar-nav-contain "+classSelector+".sidebar-nav-icon");
    $selector.next("span.label-notification").remove();
    if(number) {
        $selector.after(`<span class="label label-notification animation-floating">${number}</span>`);
    }
}

function showMenuNotifications(e) {
    var data = JSON.parse(e.data);
    if(data.length) {
        data.forEach(function(doc){
            menuNotification(doc.class, doc.count);
        })
    }
}

function copyToClipboard(value) {
    if(typeof value == "object") {
        value = value.textContent
    }
    var input = document.createElement("input");
    document.body.appendChild(input);
    input.value = value;
    input.select();
    document.execCommand("copy");
    document.body.removeChild(input);
    document.body.dataset.clipboard = value;
    notification.show("Copy to clipboard", "success");
}

function templateDirection(data) {
    return `<span class="label label-${data.direction == 'outbound' ? 'success' : (data.callduration ? 'info' : 'danger')}" title="${data.calluuid}">${data.direction}</span>`;
}

function templateDisposition(data) {
    var result = (data.disposition || "").toString();
    switch(result) {
        case "ANSWERED":
            css = "success";
            break;
        case "FAILED":
            css = "danger";
            break;
        default:
            css = "warning";
            break;
    }
    return `<label class="label label-${css}">${result}</label>`;
}

function notifyTitle(title = "", timeInterval = 1000) {
    if(title) {
        window.tempTitle = title;
        clearInterval(window.intervalTitle);
        window.intervalTitle = setInterval(() => {
            window.tempTitle =  window.tempTitle.substr(1) + window.tempTitle.substr(0, 1);
            document.title = window.tempTitle;
        }, timeInterval);
    } else {
        document.title = window.currentTitle;
        clearInterval(window.intervalTitle);
    }
}

function parameterMap(options, operation) {
    if(["create", "update"].indexOf(operation) > -1)
        return kendo.stringify(options);
    else return {q: kendo.stringify(options)};
}

function getRandomColor() {
  var letters = '0123456789ABCDEF';
  var color = '#';
  for (var i = 0; i < 6; i++) {
    color += letters[Math.floor(Math.random() * 16)];
  }
  return color;
}

function notificationAfterRefresh(content, type = "info") {
    sessionStorage.setItem("notificationAfterRefresh", JSON.stringify({content: content, type: type}));
}

function selectFilter(element, collection, field) {
    element.kendoDropDownList({
        dataSource: {
            serverFiltering: true,
            filter: {
                logic: "and",
                filters: [
                    {field: "collection", operator: "eq", value: collection},
                    {field: "field", operator: "eq", value: field}
                ]
            },
            transport: {
                read: ENV.vApi + "model/read",
                parameterMap: parameterMap
            },
            schema: {
                parse: function(response) {
                    var values = [];
                    response.data.map(doc => {
                        if(doc.sub_type) {
                            sub_type = doc.sub_type ? JSON.parse(doc.sub_type) : {};
                            values = values.concat(sub_type.values);
                        }
                    });
                    return values;
                }
            }
        },
        optionLabel: "---- "+NOTIFICATION.select+" ----"
    });
}

async function ticketForm(option = {}) {
    $rightForm = $("#right-form");
    var formHtml = await $.ajax({
        url: ENV.templateApi + "ticket/formAutoFill",
        data: {doc: option},
        error: errorDataSource
    });
    kendo.destroy($rightForm);
    $rightForm.empty();
    $rightForm.append(formHtml);
}

function secondsToTime(secs)
{
    var t = new Date(1970,0,1);
    t.setSeconds(Number(secs || 0));
    var s = t.toTimeString().substr(0,8);
    if(secs > 86399)
        s = Math.floor((t - Date.parse("1/1/70")) / 3600000) + s.substr(2);
    return s;
}

/* Currency format */
function gridNumberFormat(data, format = "##,#") {
    return data ? kendo.toString(data, format) : "0";
}

/*
	https://stackoverflow.com/questions/680929/how-to-extract-extension-from-filename-string-in-javascript
	Explanation
	(?:         # begin non-capturing group
	  \.        #   a dot
	  (         #   begin capturing group (captures the actual extension)
		[^.]+   #     anything except a dot, multiple times
	  )         #   end capturing group
	)?          # end non-capturing group, make it optional
	$           # anchor to the end of the string
*/
function extractExtensionFromFileNameString(filename = '') {
	var re = /(?:\.([^.]+))?$/;
	var ext = re.exec(filename)[1];   // "txt"
	return ext;
}

function getFileNameFromFullUrl(url = '') {
	var filename = url.substring(url.lastIndexOf('/')+1);
	filename = filename.substr(0, filename.lastIndexOf('.'));
	return filename;
}

function arrayJsonData(tags) {
    var result = [];
    $.ajax({
        async: false,
        global: false,
        url: ENV.vApi + "select/jsondata",
        data: {tags: tags},
        success: function (response) {
            result = response;
        },
        error: errorDataSource
    });
    return result;
}

function arrayColumn(inputArray, columnKey, indexKey)
{
    // console.log(inputArray);
    function isArray(inputValue)
    {
        return Object.prototype.toString.call(inputValue) === '[object Array]';
    }

    // If input array is an object instead of an array,
    // convert it to an array.
    if(!isArray(inputArray))
    {
        var newArray = [];
        for(var key in inputArray)
        {
            if(!inputArray.hasOwnProperty(key))
            {
                continue;
            }
            newArray.push(inputArray[key]);
        }
        inputArray = newArray;
    }

    // Process the input array.
    var isReturnArray = (typeof indexKey === 'undefined' || indexKey === null);
    var outputArray = [];
    var outputObject = {};
    for(var inputIndex = 0; inputIndex < inputArray.length; inputIndex++)
    {
        var inputElement = inputArray[inputIndex];

        var outputElement;
        if(columnKey === null)
        {
            outputElement = inputElement;
        }
        else
        {
            if(isArray(inputElement))
            {
                if(columnKey < 0 || columnKey >= inputElement.length)
                {
                    continue;
                }
            }
            else
            {
                if(!inputElement.hasOwnProperty(columnKey))
                {
                    continue;
                }
            }

            outputElement = inputElement[columnKey];
        }

        if(isReturnArray)
        {
            outputArray.push(outputElement);
        }
        else
        {
            outputObject[inputElement[indexKey]] = outputElement;
        }
    }

    return (isReturnArray ? outputArray : outputObject);
}

// Remember current state
function setTableState(collection, Table) {
    var state = {
        page	: Table.dataSource.page(),
        pageSize: Table.dataSource.pageSize(),
        sort	: Table.dataSource.sort(),
        filter	: Table.dataSource.filter(),
        group	: Table.dataSource.group(),
    };
    sessionStorage.setItem(collection, JSON.stringify(state));
    console.log(sessionStorage.getItem(collection));
}
// Remember current state

// Set current state
function setTableState(collection, Table) {
    var option = JSON.parse(sessionStorage.getItem(collection));
    if(typeof option !== 'undefined' && option !== null) {
        Table.dataSource.query(option);
        console.log(Table);
        sessionStorage.removeItem(collection);
    }
}
// Set current state