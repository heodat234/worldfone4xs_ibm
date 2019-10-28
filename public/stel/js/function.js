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
        });
    }
    return template.join(' ');
}

function gridBoolean(data) {
    return data ? `<i class="fa fa-check text-success"></i>` : `<i class="fa fa-times text-danger"></i>`;
}

function gridDate(data, format = "dd/MM/yy H:mm") {
    return data ? kendo.toString(data, format) : "";
}

function gridTimestamp(data, format = "dd/MM/yy H:mm") {
    return data ? kendo.toString(new Date(data * 1000), format) : "";
}

function gridName(name, href = "javascript:void(0)") {
    return `<a href="${href}"><span class="grid-name">${name}</span></a>`;
}

function gridInterger(data, format = "n0") {
    return kendo.toString(Number(data), format);
}

function gridPhone(data, id = '', type = '') {
    var html = "<span></span>";
    if(data) {
        if(typeof data == "string") {
            html = `<a href="javascript:void(0)" class="label label-info" onclick="makeCallWithDialog('${data}','${id}','${type}')" title="Call now" data-role="tooltip" data-position="top">${data}</a>`;
        } else {
            if(data.length) {
                template = $.map($.makeArray(data), function(value, index) {
                    return `<a href="javascript:void(0)" class="label label-default" data-index="${index}" onclick="makeCallWithDialog('${value}','${id}','${type}')" title="Call now" data-role="tooltip" data-position="top">${value}</a>`;
                });;
                html = template.join(' ');
            }
        }
    }
    return html;
}

function gridLongText(data, leng = 30) {
    var content = (data || '').toString();
    var html = '';
    if(content.length > leng)
        html = content.slice(0, (leng - 3)) + '...' + `<a href='javascript:void(0)' data-role='tooltip' title='${content}' onclick='return $(this).parent().html($(this).attr("title"));'>See more</a>`
    else html = content;
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
      vars[key] = value;
    });
    return vars;
}

function httpBuildQuery(params) {
    if (typeof params === 'undefined' || typeof params !== 'object') {
        params = {};
        return params;
    }
    return Object.keys(params).map(key => key + '=' + params[key]).join('&');
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
                if(typeof actionPhoneRing != "undefined" && e.status) actionPhoneRing();
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
        url: ENV.templateApi + "ticket_solve/formAutoFill",
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

function bodauTiengViet(str) {
    str = str.toLowerCase();
    str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, 'a');
    str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, 'e');
    str = str.replace(/ì|í|ị|ỉ|ĩ/g, 'i');
    str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, 'o');
    str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, 'u');
    str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, 'y');
    str = str.replace(/đ/g, 'd');
    str = str.replace(/ /g, '');
    // str = str.replace(/\W+/g, ' ');
    // str = str.replace(/\s/g, '-');
    return str;
}

function humanFileSize(bytes, si) {
    var thresh = si ? 1000 : 1024;
    if(Math.abs(bytes) < thresh) {
        return bytes + ' B';
    }
    var units = si
        ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
        : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
    var u = -1;
    do {
        bytes /= thresh;
        ++u;
    } while(Math.abs(bytes) >= thresh && u < units.length - 1);
    return bytes.toFixed(1)+' '+units[u];
}

function arrayColumn(inputArray, columnKey, indexKey)
{
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

// Steven Moseley
// https://stackoverflow.com/questions/17415579/how-to-iso-8601-format-a-date-with-timezone-offset-in-javascript
Date.prototype.toIsoLocalString = function() {
    var tzo = -this.getTimezoneOffset(),
        dif = tzo >= 0 ? '+' : '-',
        pad = function(num) {
            var norm = Math.floor(Math.abs(num));
            return (norm < 10 ? '0' : '') + norm;
        };
    return this.getFullYear() +
        '-' + pad(this.getMonth() + 1) +
        '-' + pad(this.getDate()) +
        'T' + pad(this.getHours()) +
        ':' + pad(this.getMinutes()) +
        ':' + pad(this.getSeconds()) +
        dif + pad(tzo / 60) +
        ':' + pad(tzo % 60);
}