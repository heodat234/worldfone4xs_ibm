<?php $id = $this->input->get("id") ?>
<div class="col-sm-12" style="overflow-y: auto; padding: 0">
    <div id="grid-<?= $id ?>"></div>
</div>
<div id="detail-action-menu" class="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="detail" onclick="openForm({title: '@View@ @case@', width: 900}); viewForm(this)"><li><i class="fa fa-television text-info"></i><span>@View@</span></li></a>
    </ul>
</div>
<script>
function gridCallResult(data) {
    var htmlArr = [];
    if(data) {
        data.forEach(doc => {
            htmlArr.push(`<a href="javascript:void(0)" class="label label-${(doc.disposition == "ANSWERED")?'success':'warning'}" 
                title="${kendo.toString(new Date(doc.starttime * 1000), "dd/MM/yy H:mm:ss")} | ${doc.userextension} - ${doc.customernumber}">${doc.disposition}</a>`);
        })
    }
    return htmlArr.join("<br>");
}
var Config = {
    id: '<?= $id ?>',
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "my_diallist_detail",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            index: {type: "number"}
        }
    },
    columns: [{
            field: "index",
            title: "#",
            width: 50,
            locked: true
        },{
            field: "phone",
            title: "@Main phone@",
            template: data => gridPhoneDialId(data.phone, data.id, "manual"),
            width: 110,
            locked: true
        },{
            field: "other_phones",
            title: "@Other phones@",
            template: data => gridPhoneDialId(data.other_phones, data.id, "manual"),
            width: 110,
            locked: true
        },{
            field: "action_code",
            title: "@Call code@",
            width: 110,
            locked: true
        },{
            field: "callResult",
            title: "@Calls@",
            template: diallistDetail => gridCallResult(diallistDetail.callResult),
            width: 120,
            locked: true
        },{
            // Use uid to fix bug data-uid of row undefined
            title: ``,
            template: '<a role="button" class="btn btn-sm btn-circle btn-action" title="#: id #" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            width: 36,
            locked: true
        }]
}; 

var detailTable = function() {
    return {
        dataSource: {},
        grid: {},
        columns: Config.columns,
        init: async function() {
            var dataSource = this.dataSource = new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                serverGrouping: false,
                filter: {field: "diallist_id", operator: "eq", value: Config.id},
                sort: [{field: "priority", dir: "asc"}, {field: "index", dir: "asc"}],
                pageSize: 10,
                batch: false,
                schema: {
                    data: "data",
                    total: "total",
                    groups: "groups",
                    model: Config.model,
                    parse: Config.parse ? Config.parse : res => res
                },
                transport: {
                    read: {
                        url: Config.crudApi + Config.collection,
                    },
                    update: {
                        url: function(data) {
                            return Config.crudApi + Config.collection + "/" + data.id;
                        },
                        type: "PUT",
                        contentType: "application/json; charset=utf-8"
                    },
                    create: {
                        url: Config.crudApi + Config.collection,
                        type: "POST",
                        contentType: "application/json; charset=utf-8"
                    },
                    destroy: {
                        url: function(data) {
                            return Config.crudApi + Config.collection + "/" + data.id;
                        },
                        type: "DELETE",
                    },
                    parameterMap: parameterMap
                },
                sync: syncDataSource,
                error: errorDataSource
            });

            var diallistDetailModel = this.diallist = await $.get(`${ENV.restApi}model`, {q: JSON.stringify({filter: {
                    logic: "and",
                    filters: [
                        {field: "collection", operator: "eq", value: ENV.type + "_Diallist_detail"},
                        {field: "sub_type", operator: "isnotempty", value: ""}
                    ]
                }})});

            diallistDetailColumns = diallistDetailModel.data;

            diallistDetailColumns.map((col, idx) => {
                col.width = 150;
                col.template = data => gridLongText(data[col.field], 20);
            });

            this.columns = this.columns.concat(diallistDetailColumns);

            var grid = this.grid = $(`#grid-${Config.id}`).kendoGrid({
                dataSource: dataSource,
                resizable: true,
                pageable: {
                    refresh: true,
                    pageSizes: true,
                    input: true
                },
                sortable: true,
                scrollable: true,
                height: '80vh',
                columns: this.columns,
                filterable: KENDO.filterable,
                editable: false
            }).data("kendoGrid");

            grid.selectedKeyNames = function() {
                var items = this.select(),
                    that = this,
                    checkedIds = [];
                $.each(items, function(){
                    if(that.dataItem(this))
                        checkedIds.push(that.dataItem(this).uid);
                })
                return checkedIds;
            }

            /*
             * Right Click Menu
             */
            var menu = $("#detail-action-menu");
            if(!menu.length) return;
            
            $("html").on("click", function() {menu.hide()});

            $(document).on("click", `#grid-${Config.id} tr[role=row] a.btn-action`, function(e){
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
        }
    }
}();

async function viewForm(ele) {
    var dataItem = detailTable.dataSource.getByUid($(ele).data("uid")),
        formHtml = await $.ajax({
            url: Config.templateApi + Config.collection + "/view",
            data: {dataFields: JSON.stringify(detailTable.diallist.columns), id: dataItem.id},
            error: errorDataSource
        });
    $("#right-form").empty();
    var kendoView = new kendo.View(formHtml);
    kendoView.render($("#right-form"));
}

detailTable.init();

</script>

<style type="text/css">
    #run-status-switch {width: 110px}
    #run-status-switch .onoffswitch-inner:before {content: "RUNNING";}
    #run-status-switch .onoffswitch-inner:after {content: "STOP";}
</style>