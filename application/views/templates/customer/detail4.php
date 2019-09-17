<div id="cdr-action-menu" class="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="action/play" onclick="playAction(this)"><li><i class="fa fa-play text-info" style="padding-left: 3px"></i><span>Play</span></li></a>
        <a href="javascript:void(0)" data-type="action/download" onclick="downloadAction(this)"><li><i class="fa fa-cloud-download text-danger"></i><span>Download</span></li></a>
        <a href="javascript:void(0)" data-type="action/repopup" onclick="repopupAction(this)"><li><i class="hi hi-new_window text-warning"></i><span>Repopup</span></li></a>
    </ul>
</div>
<div id="detail" data-role="splitter"
             data-panes="[
                { collapsible: true, min: '700px'},
                { collapsible: true, min: '200px', size: '280px' },
             ]"
             data-orientation="horizontal" class="after-breadcrumb" style="overflow-y: auto;">
    <div class="col-sm-9" id="left-detail">
		<div class="row">
			<div class="col-sm-6">
				<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@PERSONAL INFO@</span></h4>
				<form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Customer ID@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static" data-bind="text: item.CustomerNumber"></p>
<!--                            <p class="form-control-static" data-bind="text: item.PersonID">00007</p>-->
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Last Name@</label>
                        <div class="col-sm-7">
<!--                            <p class="form-control-static" data-bind="text: item.LastName">Ngọc Oanh</p>-->
                            <p class="form-control-static" data-bind="text: item.LastName"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@First Name@</label>
                        <div class="col-sm-7">
<!--                            <p class="form-control-static" data-bind="text: item.FirstName">Lê Thị</p>-->
                            <p class="form-control-static" data-bind="text: item.FirstName"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Gender@</label>
                        <div class="col-sm-7">
<!--                            <p class="form-control-static" data-bind="text: item.Gender">Nữ</p>-->
                            <p class="form-control-static" data-bind="text: item.Gender"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Birthday@</label>
                        <div class="col-sm-7">
<!--                            <p class="form-control-static" data-bind="text: item.DOB">04/04/1993</p>-->
                            <p class="form-control-static" data-bind="text: item.DOB"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Country@</label>
                        <div class="col-sm-7">
<!--                            <p class="form-control-static" data-bind="text: item.CountryCode">Việt Nam</p>-->
                            <p class="form-control-static" data-bind="text: item.CountryCode"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Language@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static" data-bind="text: item.CultureCode"></p>
                        </div>
                    </div>
                </form>
			</div>
			<div class="col-sm-6">
				<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@CONTACT INFO@</span></h4>
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Address@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static" data-bind="text: item.AddressLine"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Phone Number@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static" data-bind="text: item.listPhone"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Email Address@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static" data-bind="text: item.listEmail"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Facebook URL@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static" data-bind="text: item.facebook"></p>
                        </div>
                    </div>
                </form>
            </div>
		</div>
        <div class="row">
            <div class="col-sm-12">
                <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@NOTE@</span></h4>
                <p>Pé Oanh xin đẹp nhất trần đời đang đến đây... ^^</p>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@PNR HISTORY@</span></h4>
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@PNR [Number of pax] - status of PNR@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static">Sort by time</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-12 control-label">@RESERVATION DETAILS@</label>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Creation Date@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static">04/04/2019</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Modified Date@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static">04/04/2019</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Check in Date@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static">04/04/2019</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Seat No.@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static">G25</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Booking By@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static">Nguyễn Thị Ngọc Yến</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 control-label">@Payment Date@</label>
                        <div class="col-sm-7">
                            <p class="form-control-static">04/04/2019</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-sm-6">
                <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@ANCILLARY HISTORY@</span></h4>
                <p>Type of SSRs - [Number of buying]: sort by time [view more PNR]</p>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@ROUTE HISTORY@</span></h4>
                <p>[Route] - [Number of buying]: View more [PNR]</p>
            </div>
            <div class="col-sm-6">
                <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@SC HISTORY@</span></h4>
                <p>Number of SC [View more PNR]</p>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@CASE HISTORY@</span></h4>
            </div>
            <div class="col-sm-6">
                <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@COMMENT HISTORY@</span></h4>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@RELATED ACC@</span></h4>
            </div>
        </div>
    </div>
    <div class="col-sm-3" style="height: 80vh; overflow-y: auto; padding: 0">
        <!-- Default Tabs -->
        <div data-role="tabstrip">
            <ul>
                <li class="k-state-active">
                    @Timeline@
                </li>
                <li>
                	@Note@
                </li>
                <li>
                	@Log@
                </li>
            </ul>
            <div>
                <div class="timeline block-content-full">
                    <h3 class="timeline-header">@Interactive history@</h3>
                    <!-- You can remove the class .timeline-hover if you don't want each event to be highlighted on mouse hover -->
                    <ul class="timeline-list timeline-hover" data-template="timeline-template" data-bind="source: interactiveDataSource">
                    </ul>
                </div>
            </div>
            <div>
            	<textarea class="k-textbox" style="width: 100%" rows="5" data-bind="events: {keyup: addNote}" placeholder="@Type then enter to note@"></textarea>
                <div class="container-fluid" style="margin-top: 10px" data-template="note-template" data-bind="source: noteData">
                </div>
    		</div>
    		<div>
                <p>
                    <i>@Create by@: </i>
                    <b data-bind="text: item.createdBy"></b><br>
                    <i>@Create at@: </i>
                    <span data-bind="text: item.createdAtText"></span>
                </p>
                <p>
                    <i>@Last update by@: </i>
                    <b data-bind="text: item.updatedBy"></b><br>
                    <i>@Last update at@: </i>
                    <span data-bind="text: item.updatedAtText"></span>
                </p>
    		</div>
    	</div>
        <!-- END Default Tabs -->
    </div>
</div>

<link rel="stylesheet" href="<?= STEL_PATH.'css/detail.css' ?>" type="text/css">

<script type="text/x-kendo-template" id="case-timeline-template">
    <li data-bind="css: {active: active}">
        <a href="javascript:void(0)" data-bind="attr: {data-id: id}, click: openTicketDetail">
            <div class="timeline-icon">
                <i class="fa fa-ticket"></i>
            </div>
        </a>
        <div class="timeline-time">
            <span class="text-muted" data-bind="text: createdAtText"></span>
        </div>
        <div class="timeline-content">
            <p class="pull-right">
                <i>@Customer type@: </i> <span data-bind="text: customerFormat"></span><br>
                <i>@Created by@: </i> <span data-bind="text: createdBy"></span><br>
                <i>@Reply@: </i> <span data-bind="text: reply"></span>
            </p>
            <p class="push-bit">
                <a href="javascript: void(0)" data-bind="attr: {data-id: id}, click: openTicketDetail"><b class="text-danger" data-bind="text: ticket_id"></b></a>
                <i>(@Source@: <span data-bind="text: source"></span>)</i>
                <br>
                <strong data-bind="text: service"></strong><br>
                <span data-bind="text: status" class="label label-# if(data.status == "Open"){#success#}else{#warning#}#"></span>
            </p>
        </div>
    </li>
</script>

<script type="text/x-kendo-template" id="account-template">
    <div class="col-md-4">
        <h3>#= templateAccountType(data) #</h3>
        <div>
            #= templateAccountList(data) #
        </div>
    </div>
</script>

<script type="text/x-kendo-template" id="timeline-template">
    <li data-bind="css: {active: active}, attr: {data-id: id}">
        <a href="javascript:void(0)" #if(data.active){#data-bind="click: deactive"#}#>
            <div class="timeline-icon">
                # switch(data.type) { 
                    case "call": #
                    <i class="fa fa-phone"></i>
                #   break; 
                    case "email": #
                    <i class="fa fa-envelope"></i>
                #   break; 
                    case "sms": #
                    <i class="fa fa-commenting"></i>
                #   break; 
                    case "chat": #
                    <i class="gi gi-conversation"></i>
                #   break; 
                } #
            </div>
        </a>
        <div class="timeline-time">
            <span class="text-muted" data-bind="text: createdAtText"></span>
        </div>
        <div class="timeline-content">
            <p class="push-bit"><strong data-bind="text: title"></strong></p>
            <p class="push-bit" data-bind="invisible: visibleEditInteractive">
                <span data-bind="text: content"></span>
                <br>
                <a href="javascript:void(0)" data-bind="click: editInteractive, visible: active"><i class="fa fa-pencil"></i></a>
            </p>
            <p class="push-bit" data-bind="visible: visibleEditInteractive">
                <textarea class="k-textbox" data-bind="value: content"></textarea>
                <br>
                <a href="javascript:void(0)" class="k-button" data-bind="click: saveEditInteractive">Save</a>
                <a href="javascript:void(0)" class="k-button" data-bind="click: closeEditInteractive">Cancel</a>
            </p>
        </div>
    </li>
</script>

<script type="text/x-kendo-template" id="note-template">
    <div class="row">
        <div class="alert alert-#= HELPER.bsColors[index % HELPER.bsColors.length] #">
            <button type="button" class="close" aria-hidden="true" data-bind="click: removeNote" data-id="#= id #">×</button>
            <p data-bind="text: content"></p>
            <p class="text-right text-muted"><span data-bind="text: createdBy"></span> - <span data-bind="text: createdAtText"></span></p>
        </div>
    </div>
</script>

<script>
var Config = Object.assign(Config, {
	id: '<?= $this->input->get("id") ?>'
});

function templateAccountType(data) {
    var values = {
        ListAccountCasa : "@TKTT@",
        ListAccountSaving : "@TKTK@",
        ListAccountLoan : "@KUV@",
    }
    return (values[data.type] || "").toString()
}

function templateAccountList(data) {
    var arrHTML = [];
    arrHTML.push(`<ul class="list-group" style="margin-bottom: 0">`);
    switch (data.type) {
        case "ListAccountCasa":
            if(data.list.length) {
                data.list.forEach((account, index) => {
                    arrHTML.push(`<li class="list-group-item list-group-item-action list-group-item-${HELPER.bsColors[index%5]}">
                        <ul class="none-style-type">
                            <li><label>@Branchname@:</label> ${account.AccountBranchName}</li>
                            <li><label>@Account no@:</label> ${account.AccountNumber}</li>
                            <li><label>@Account class@:</label> ${account.Account_class}</li>
                            <li><label>@Account Branch@:</label> ${account.AccountBranch}</li>
                            <li><label>@Working Balance@:</label> ${kendo.toString(Number(account.WorkingBalance), "n0")}</li>
                            <li><label>@AvailableAmount@:</label> ${kendo.toString(Number(account.AvailableAmount), "n0")}</li>
                            <li><label>@Currency@:</label> ${account.Currency}</li>
                            <li><label>@Account Open Date@:</label> ${account.Ac_Open_Date}</li>
                            <li><label>@Account Status@:</label> ${account.AccStatus}</li>
                        </ul>
                    </li>`);
                })
            }
            break;
        case "ListAccountSaving":
            if(data.list.length) {
                data.list.forEach((account, index) => {
                    arrHTML.push(`<li class="list-group-item list-group-item-action list-group-item-${HELPER.bsColors[index%5]}">
                        <ul class="none-style-type">
                            <li><label>@Branchname@:</label> ${account.AccountBranchName}</li>
                            <li><label>@Account no@:</label> ${account.AccountNumber}</li>
                            <li><label>@Account class@:</label> ${account.Account_class}</li>
                            <li><label>@Account Branch@:</label> ${account.AccountBranch}</li>
                            <li><label>@Working Balance@:</label> ${kendo.toString(Number(account.WorkingBalance), "n0")}</li>
                            <li><label>@AvailableAmount@:</label> ${kendo.toString(Number(account.AvailableAmount), "n0")}</li>
                            <li><label>@Currency@:</label> ${account.Currency}</li>
                            <li><label>@Account Open Date@:</label> ${account.Ac_Open_Date}</li>
                            <li><label>@Account Status@:</label> ${account.AccStatus}</li>
                        </ul>
                    </li>`);
                })
            }
            break;
        case "ListAccountLoan":
            if(data.list.length) {
                data.list.forEach((account, index) => {
                    arrHTML.push(`<li class="list-group-item list-group-item-action list-group-item-${HELPER.bsColors[index%5]}">
                        <ul class="none-style-type">
                            <li><label>@Branchname@:</label> ${account.AccountBranchName}</li>
                            <li><label>@Account no@:</label> ${account.AccountNumber}</li>
                            <li><label>@Account Branch@:</label> ${account.AccountBranch}</li>
                            <li><label>@AmountFinanced@:</label> ${kendo.toString(Number(account.AmountFinanced), "n0")}</li> 
                            <li><label>@Currency@:</label> ${account.Currency}</li>
                            <li><label>@Account Status@:</label> ${account.AccStatus}</li>
                        </ul>
                    </li>`);
                })
            }
            break;
        default:
            break;
    }
    arrHTML.push(`</ul>`);
    return arrHTML.join("");
}

function tplRecording(data) {
    return `<a role="button" class="btn btn-sm btn-circle btn-action" data-uid="${data.uid}"><i class="fa fa-ellipsis-v"></i></a>`
}

function playAction(ele) {
    var uid = $(ele).data("uid"),
        dataItem = Detail.model.callHistory.getByUid(uid),
        calluuid = dataItem.calluuid,
        callduration = dataItem.callduration;
    if(callduration) 
        play(calluuid);
    else notification.show("No recording", "warning");
}

function downloadAction(ele) {
    var uid = $(ele).data("uid");
        dataItem = Detail.model.callHistory.getByUid(uid),
        calluuid = dataItem.calluuid,
        callduration = dataItem.callduration;
    if(callduration) 
        downloadRecord(calluuid);
    else notification.show("No recording", "warning");
}

function repopupAction(ele) {
    var uid = $(ele).data("uid");
    var calluuid = Detail.model.callHistory.getByUid(uid).calluuid;
    rePopup(calluuid);
}

function emailFormDetail(ele) {
    emailForm({doc: Detail.model.get("item").toJSON()});
}

function smsFormDetail(ele) {
    smsForm({doc: Detail.model.get("item").toJSON()});
}

function dataSourceService(level=1, parent_id=null) {
    return new kendo.data.DataSource({
        transport: {
            read: {
                url: `${ENV.restApi}servicelevel`,
                data: {id: parent_id, "lv": level}
            },
            parameterMap: parameterMap
        }
    })
}

async function addFormTicket() {
    var formHtml = await $.ajax({
        url: Config.templateApi + "ticket_solve/form",
        error: errorDataSource
    });
    var model = Object.assign({
        assignOption: new kendo.data.DataSource({
            transport: {
                read: ENV.restApi + "user",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total"
            }
        }),
        sourceOption: dataSourceJsonData(["Ticket", "source"]),
        changeStatus: function(e) {
            if(this.item.status == "Open") {
                this.set("item.status", "Urgent");
                $(e.currentTarget).removeClass("label-success").addClass("label-danger");
            } else {
                this.set("item.status", "Open");
                $(e.currentTarget).removeClass("label-danger").addClass("label-success");
            }
        },
        senderOption: () => dataSourceDropDownList("Customer", ["name"]),
        senderChange: function(e) {
            var item = e.sender.dataItem();
            this.set("item.sender_id", item.id);
        },
        relationOption: () => dataSourceJsonData(["Ticket", "relation"]),
        priorityOption: () => dataSourceJsonData(["Ticket", "priority"]),
        customerFormatOption: () => dataSourceJsonData(["Ticket", "customer format"]),
        serviceOption: new kendo.data.DataSource({
            transport: {
                read: ENV.vApi + "servicelevel/select",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total"
            },
            error: errorDataSource
        }),
        serviceLv1Option: dataSourceService(1),
        serviceLv2Option: [],
        serviceLv3Option: [],
        onSearch1: function(e) {
            var field = "value1";
            var filterValue = {field: field, operator: "eq", value: e.dataItem.name};
            var filter = {
                logic: "and",
                filters: [filterValue]
            };
            this.serviceOption.filter(filter);

            var parent_id = e.dataItem.id;
            this.set("serviceLv2Option", dataSourceService(2, parent_id));
            this.set("serviceLv3Option", []);
            $("input[name=serviceLv2]").data("kendoDropDownList").refresh();
            $("input[name=serviceLv3]").data("kendoDropDownList").refresh();
        },
        onSearch2: function(e) {
            var filter = this.serviceOption.filter();
            var field = "value2";
            var filterValue = {field: field, operator: "eq", value: e.dataItem.name};
            if(filter) {
                filter.filters.filter(doc => doc.field != field);
                filter.filters.push(filterValue);
            } else {
                filter = {
                    logic: "and",
                    filters: []
                };
                filter.filters.push(filterValue);
            }

            this.serviceOption.filter(filter);

            var parent_id = e.dataItem.id;
            this.set("serviceLv3Option", dataSourceService(3, parent_id));
            $("input[name=serviceLv3]").data("kendoDropDownList").refresh();
        },
        onSearch3: function(e) {
            var filter = this.serviceOption.filter();
            var field = "value3";
            var filterValue = {field: field, operator: "eq", value: e.dataItem.name};
            if(filter) {
                filter.filters.filter(doc => doc.field != field);
                filter.filters.push(filterValue);
            } else {
                filter = {
                    logic: "and",
                    filters: []
                };
                filter.filters.push(filterValue);
            }
            this.serviceOption.filter(filter);
            var dropdownlist = $("input[name=service]").data("kendoDropDownList");

            dropdownlist.select(dropdownlist.ul.children().eq(0));
            this.set("visibleAdvancedSearch", false);
            this.serviceOption.filter({});
            this.set("item.service", dropdownlist.value());
        },
        enableAdvancedSearch: function(e) {
            this.set("visibleAdvancedSearch", true);
        },
        serviceSelect: function(e) {
            this.set("visibleAdvancedSearch", false);
        }
    }, {
        item: {status: "Open", source: "Hotline", receive_time: new Date(), sender_name: Detail.model.item.name, sender_id: Config.id, assign: ENV.extension},
        save: function() {
            var data = this.item.toJSON();
            $.ajax({
                url: ENV.restApi + "ticket",
                type: "POST",
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify(data),
                success: syncDataSource,
                error: errorDataSource
            })
        }
    });
    kendo.destroy($("#right-form"));
    $("#right-form").empty();
    var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
    kendoView.render($("#right-form"));
}

var Detail = function() {
    var observable = Object.assign(Config.observable, {
        item: {},
        interactiveDataSource: new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            sort: [{field: "createdAt", dir: "desc"}],
            transport: {
                read: `${ENV.vApi}interactive/read`,
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(response) {
                    response.data.map(function(doc, idx){
                        doc.index = idx;
                        doc.createdAtText = (kendo.toString(new Date(doc.createdAt * 1000), "dd/MM/yy H:mm") ||  "").toString();
                    })
                    return response;
                }
            }
        }),
        editInteractive: function(e) {
            let id = $(e.currentTarget).closest("li").data("id"),
                data = this.interactiveDataSource.data();

            data.map(doc =>  doc.visibleEditInteractive = Boolean(doc.id == id));
            this.interactiveDataSource.data(data);
        },
        closeEditInteractive: function() {
            let data = this.interactiveDataSource.data();
            data.map(doc =>  doc.visibleEditInteractive = false);
            this.interactiveDataSource.data(data);
        },
        saveEditInteractive: function(e) {
            var id = $(e.currentTarget).closest("li").data("id");
            var content = $(e.currentTarget).closest("p").find("textarea").val();
            swal({
                title: "Are you sure?",
                text: `Save content this interactive.`,
                icon: "warning",
                buttons: true,
                dangerMode: false,
            })
            .then((sure) => {
                if (sure) {
                    $.ajax({
                        url: `${ENV.vApi}interactive/update/${id}`,
                        type: "PUT",
                        contentType: "application/json; charset=utf-8",
                        data: kendo.stringify({content: content}),
                        success: syncDataSource,
                        error: errorDataSource
                    })
                }
                this.interactiveDataSource.read();
            });
        },
        deactive: function(e) {
            var id = $(e.currentTarget).closest("li").data("id");
            swal({
                title: "Are you sure?",
                text: `Apply this interactive. You can't change info after apply.`,
                icon: "warning",
                buttons: true,
                dangerMode: false,
            })
            .then((sure) => {
                if (sure) {
                    $.ajax({
                        url: `${ENV.vApi}interactive/update/${id}`,
                        type: "PUT",
                        contentType: "application/json; charset=utf-8",
                        data: kendo.stringify({active: false, foreign_id: Config.id}),
                        success: syncDataSource,
                        error: errorDataSource
                    })
                }
                this.interactiveDataSource.read();
            });
        },
        noteData: new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            filter: [
                {field: "foreign_id", operator: "eq", value: Config.id}
            ],
            sort: [{field: "createdAt", dir: "desc"}],
            transport: {
                read: `${ENV.restApi}customer_note`,
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(response) {
                    response.data.map(function(doc, idx){
                        doc.index = idx;
                        doc.createdAtText = (kendo.toString(new Date(doc.createdAt * 1000), "dd/MM/yy H:mm") ||  "").toString();
                    })
                    return response;
                }
            }
        }),
        addNote: function(e) {
            if(e.keyCode == 13) {
                var content = e.currentTarget.value.replace("\n", "");
                swal({
                    title: "Are you sure?",
                    text: `Save this note.`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then((sure) => {
                    if (sure) {
                        e.currentTarget.value = "";
                        $.ajax({
                            url: `${ENV.restApi}customer_note`,
                            type: "POST",
                            contentType: "application/json; charset=utf-8",
                            data: kendo.stringify({
                                collection: "Customer",
                                foreign_id: Config.id,
                                content: content
                            }),
                            success: function() {
                                syncDataSource();
                                Detail.model.noteData.read();
                            },
                            error: errorDataSource
                        })
                    }
                });
            }
        },
        removeNote: function(e) {
            var id = $(e.currentTarget).data("id");
            swal({
                title: "Are you sure?",
                text: `Remove this note.`,
                icon: "warning",
                buttons: true,
                dangerMode: false,
            })
            .then((sure) => {
                if(sure) {
                    $.ajax({
                        url: `${ENV.restApi}customer_note/${id}`,
                        type: "DELETE",
                        success: function() {
                            syncDataSource();
                            Detail.model.noteData.read();
                        },
                        error: errorDataSource
                    })
                }
            })
        },
        onSelectFile: function(e) {
            console.log(e);
        },
        callHistory: new kendo.data.DataSource({
            serverFiltering: true,
            serverPaging: true,
            serverSorting: true,
            sort: {field: "starttime", dir: "desc"},
            pageSize: 5,
            transport: {
                read: `${ENV.vApi}cdr`,
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(response) {
                    response.data.map(function(doc){
                        doc.starttime = doc.starttime ? kendo.toString(new Date(doc.starttime * 1000), "dd/MM/yy H:mm:ss").toString() : "";
                    })
                    return response;
                }
            }
        }),
        uploadAvatar: function() {
            $("#upload-avatar").click();
        },
        uploadSuccessAvatar: function(e) {
            notification.show(e.response.message, e.response.status ? "success" : "error");
            e.sender.clearAllFiles();
            if(e.response.filepath) {
                this.set("item.avatar", e.response.filepath);
                $.ajax({
                    url: ENV.restApi + "customer/" + this.get("item.id"),
                    type: "PUT",
                    contentType: "application/json; charset=utf-8",
                    data: JSON.stringify({avatar: e.response.filepath})
                })
            }
        },
        accountData: [],
        openAccount: function() {
            $.ajax({
                url: ENV.namaApi + "core/getInfoAccount",
                data: {q: JSON.stringify({cif: this.item.cif})},
                success: response => {
                    if(response.data) {
                        this.set("accountData", response.data);
                        console.log(response.data);
                    }
                },
                error: errorDataSource
            })
        },
        onlineBanking: {},
        openOnlineBanking: function() {
            $.ajax({
                url: ENV.namaApi + "core/getThongTinDVNHDT",
                data: {q: JSON.stringify({cif: this.item.cif})},
                success: response => {
                    if(response.status) {
                        this.set("onlineBanking", response.doc);
                    }
                },
                error: errorDataSource
            })
        },
        caseData: new kendo.data.DataSource({
            serverFiltering: true,
            serverPaging: true,
            serverSorting: true,
            filter: {field: "sender_id", operator: "eq", value: Config.id},
            pageSize: 5,
            transport: {
                read: ENV.restApi + "ticket",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(response) {
                    response.data.map(doc =>  {
                        doc.createdAtText = gridTimestamp(doc.createdAt);
                        doc.customerFormat = doc.customerFormat ? doc.customerFormat.join(", ") : "";
                    })
                    return response
                }
            },
            error: errorDataSource
        }),
        openTicketDetail: function(e) {
            $currentTarget = $(e.currentTarget);
            window.open("manage/ticket/#/detail/" + $currentTarget.data("id"),'_blank','noopener');
        },
        createTicket: function() {
            openForm({title: "@Create@ @ticket@", width: 700});
            addFormTicket();
        }
    });
    var model = kendo.observable(observable);
	return {
        model: model,
		read: async function() {
            // Built view
            var customerHTMLArray = [];
            var customerModel = await $.get(`${ENV.vApi}model/read`, {
                q: JSON.stringify({filter: {field: "collection", operator: "eq", value: (ENV.type ? ENV.type + "_" : "") + "Customer"}, sort: {field: "index", dir: "asc"}})
            });

            var dataItemFull = await $.get(`${ENV.restApi}customer/${Config.id}`);
            dataItemFull.phoneHTML = gridPhone(dataItemFull.phone);
            dataItemFull.other_phonesHTML = gridPhone(dataItemFull.other_phones);
            dataItemFull.createdAtText = gridTimestamp(dataItemFull.createdAt);
            dataItemFull.updatedAtText = gridTimestamp(dataItemFull.updatedAt);
            if(dataItemFull.MiddleName !== null) {
                dataItemFull.LastName += dataItemFull.MiddleName;
            }
            if(dataItemFull.Gender == 1) {
                dataItemFull.Gender = "Nam";
            }
            else {
                dataItemFull.Gender = "Nữ";
            }
            dataItemFull.AddressLine = '';
            if(typeof dataItemFull.AddressLine1 !== 'undefined' && dataItemFull.AddressLine1 !== null) {
                dataItemFull.AddressLine += dataItemFull.AddressLine1;
            }
            if(typeof dataItemFull.AddressLine2 !== 'undefined' && dataItemFull.AddressLine2 !== null) {
                dataItemFull.AddressLine += dataItemFull.AddressLine2;
            }
            if(typeof dataItemFull.AddressLine3 !== 'undefined' && dataItemFull.AddressLine3 !== null) {
                dataItemFull.AddressLine += dataItemFull.AddressLine3;
            }
            var listPhone = '';
            $.each(dataItemFull.CustomerPhone, function(indexPhone, phone) {
                listPhone += phone.Number + '\n';
            });
            dataItemFull.listPhone = listPhone;
            var listEmail = '';
            $.each(dataItemFull.CustomerEmail, function(indexEmail, email) {
                listEmail += email.EMailAddress + '\n';
            });
            dataItemFull.listEmail = listEmail;
            customerModel.data.forEach(doc => {

                if(!doc.sub_type) return;
                let sub_type = JSON.parse(doc.sub_type);
                if(!sub_type.detailShow) return;

                if(doc.field != "name") {
                    switch(doc.type) {
                        case "phone":
                            dataItemFull[doc.field + "HTML"] = gridPhone(dataItemFull[doc.field]);
                            customerHTMLArray.push(`<div class="form-group">
                                <label class="col-sm-3 control-label">${doc.title}</label>
                                <div class="col-sm-9">
                                    <p class="form-control-static" data-bind="html: item.${doc.field}HTML"></p>
                                </div>
                            </div>`);
                            break;
                        case "timestamp":
                            dataItemFull[doc.field + "Text"] = gridTimestamp(dataItemFull[doc.field]);
                            customerHTMLArray.push(`<div class="form-group">
                                <label class="col-sm-3 control-label">${doc.title}</label>
                                <div class="col-sm-9">
                                    <p class="form-control-static" data-bind="text: item.${doc.field}Text"></p>
                                </div>
                            </div>`);
                            break;
                        default:
                            customerHTMLArray.push(`<div class="form-group">
                                <label class="col-sm-3 control-label">${doc.title}</label>
                                <div class="col-sm-9">
                                    <p class="form-control-static" data-bind="text: item.${doc.field}"></p>
                                </div>
                            </div>`);
                            break;
                    }
                }
            });
            $("#customer-detail-view").html(customerHTMLArray.join(''));
            this.model.set("item", dataItemFull);

            // Sig_code
            if(dataItemFull.cif) {
                $.get(ENV.namaApi + "core/getInfoListSignature", 
                    {q: JSON.stringify({cif: dataItemFull.cif})}, 
                    function(response) {
                        if(response.status && response.doc) {
                            $("#customer-sign-img").attr("src", "data:image/png;base64, " + response.doc.Sig_code);
                            $("#customer-sign-link").attr("href", "data:image/png;base64, " + response.doc.Sig_code);
                        } else $("#customer-sign-link").html(`<i>@No data@</i>`);
                })
            }

            // CDR
            var filter = {
                logic: "or", 
                filters: [
                    {field: "customernumber", operator: "eq", value: dataItemFull.phone}
                ]
            };
            if(dataItemFull.other_phones) {
                dataItemFull.other_phones.forEach(function(phone){
                    filter.filters.push({field: "customernumber", operator: "eq", value: phone})
                })
            }
            this.model.callHistory.filter(filter);
            var interactiveFilters = [{
                logic: "and",
                filters: [
                    {field: "foreign_id", operator: "eq", value: Config.id},
                    {field: "active", operator: "eq", value: false}
                ]
            }];
            if(dataItemFull.phone) {
                interactiveFilters.push({
                    logic: "and",
                    filters: [
                        {field: "type", operator: "eq", value: "call"},
                        {field: "foreign_key", operator: "eq", value: dataItemFull.phone},
                        {field: "active", operator: "eq", value: true}
                    ]
                })
            }
            if(dataItemFull.email) {
                interactiveFilters.push({
                    logic: "and",
                    filters: [
                        {field: "type", operator: "eq", value: "email"},
                        {field: "foreign_key", operator: "eq", value: dataItemFull.email},
                        {field: "active", operator: "eq", value: true}
                    ]
                })
            }
            this.model.interactiveDataSource.filter({
                logic: "or",
                filters: interactiveFilters
            });
            return dataItemFull;
		},
		init: function() {
            this.read().then(() => {
                kendo.bind($("#detail"), this.model);

                /*
                 * Right Click Menu
                 */
                var menu = $("#cdr-action-menu");
                if(!menu.length) return;

                $("html").on("click", function() {menu.hide()});

                $(document).on("click", "#call-history-grid tr[role=row] a.btn-action", function(e){
                    let row = $(e.target).closest("tr");
                    e.pageX -= 20;
                    showMenu(e, row);
                });

                function showMenu(e, that) {
                    //hide menu if already shown
                    menu.hide(); 

                    //Get id value of document
                    var uid = $(that).data('uid');
                    if(uid)
                    {
                        menu.find("a").data('uid',uid);

                        //get x and y values of the click event
                        var pageX = e.pageX;
                        var pageY = e.pageY;

                        //position menu div near mouse cliked area
                        menu.css({top: pageY , left: pageX});

                        var mwidth = menu.width();
                        var mheight = menu.height();
                        var screenWidth = $(window).width();
                        var screenHeight = $(window).height();

                        //if window is scrolled
                        var scrTop = $(window).scrollTop();

                        //if the menu is close to right edge of the window
                        if(pageX+mwidth > screenWidth){
                        menu.css({left:pageX-mwidth});
                        }

                        //if the menu is close to bottom edge of the window
                        if(pageY+mheight > screenHeight+scrTop){
                        menu.css({top:pageY-mheight});
                        }

                        //finally show the menu
                        menu.show();     
                    }
                }
            })
		}
	}
}();

Detail.init();

async function editForm(ele) {
    var dataItemFull = await $.get(`${Config.crudApi + Config.collection}/${Config.id}`),
        formHtml = await $.ajax({
            url: Config.templateApi + Config.collection + "/form",
            error: errorDataSource
        });

    var model = Object.assign(Config.observable, {
        item: dataItemFull,
        save: function() {
            var data = this.item.toJSON();
            $.ajax({
            	url: `${Config.crudApi + Config.collection}/${Config.id}`,
            	type: "PUT",
            	data: JSON.stringify(data),
                contentType: "application/json; charset=utf-8",
            	success: (response) => {
                    if(response.status) {
                        notificationAfterRefresh("@Edit@ @Customer@ @Success@", "success");
                		location.reload();
                    } else notification.show("@Error@", "error");
            	},
            	error: errorDataSource
            })
        }
    });
    kendo.destroy($("#right-form"));
    $("#right-form").empty();
    var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
    kendoView.render($("#right-form"));
}


$('[data-toggle="lightbox-image"]').magnificPopup({type:"image", image: {titleSrc:"title"}});
</script>

<style>
    #detail .control-label {
        text-align: left;
    }

    #detail .form-control-static {
        min-height: unset;
    }

    .k-splitter {
        height: -moz-available;
        height: -webkit-fill-available;
        height: fill-available;
    }
</style>