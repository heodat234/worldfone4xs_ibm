<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>@Appointment@</li>
        <li class="pull-right none-breakcrumb" id="top-row">
            <div class="btn-group btn-group-sm">
                <!-- <button href="#/" class="btn btn-alt btn-default active" >@Overview@</button> -->
                <a role="button" class="btn btn-sm" onclick="Table.grid.saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
            </div>
        </li>
    </ul>
    <!-- END Table Styles Header -->
    <div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
        <div class="row form-horizontal">
            <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@From date@</label>
               <div class="col-xs-8">
                  <input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy H:mm:ss" name="fromDateTime" data-bind="value: fromDateTime, events: {change: startDate}">
               </div>
            </div>
            <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@To date@</label>
               <div class="col-xs-8">
                  <input id="end-date" data-role="datepicker" data-format="dd/MM/yyyy H:mm:ss" name="toDateTime" data-bind="value: toDateTime, events: {change: endDate}">
               </div>
            </div>
            <div class="form-group col-sm-4 text-center">
                <button class="k-button" data-bind="click: search">@Search@</button>
            </div>
        </div>
        <div class="row chart-page"  style="background-color: white">

            <div class="col-sm-12">
                <div id="grid"></div>
            </div>
        </div>
        <div class="row" data-bind="visible: visibleNoData">
            <h3 class="text-center">@NO DATA@</h3>
        </div>
    </div>
    <div id="action-menu">
        <ul>
            <a href="javascript:void(0)" data-type="delay" onclick="delayFlight(this)"><li><i class="fa fa-clock-o text-info"></i><span>Delay</span></li></a>
            <a href="javascript:void(0)" data-type="early" onclick="earlyFlight(this)"><li><i class="fa fa-download text-success"></i><span>Early</span></li></a>
            <a href="javascript:void(0)" data-type="cancel" onclick="cancelFlight(this)"><li><i class="fa fa-ban text-success"></i><span>Cancel</span></li></a>

        </ul>
    </div>
    <script>
      var Table = function() {
         return {
              dataSource: {},
              grid: {},
              init: function() {
                  var dataSource = this.dataSource = new kendo.data.DataSource({
                     serverPaging: true,
                     serverFiltering: true,
                     pageSize: 5,
                     transport: {
                        read: ENV.reportApi + "appointment",
                        parameterMap: parameterMap
                     },
                     schema: {
                        data: "data",
                        total: "total",
                        parse: function(response) {
                           response.data.map(doc => {
                              doc.code = doc._id.code;
                           });
                           return response;
                        }
                     }
                  });

                  var grid = this.grid = $("#grid").kendoGrid({
                     dataSource: dataSource,
                     excel: {allPages: true},
                     excelExport: function(e) {
                        var sheet = e.workbook.sheets[0];
                        for (var rowIndex = 1; rowIndex < sheet.rows.length; rowIndex++) {
                          var row = sheet.rows[rowIndex];
                          for (var cellIndex = 0; cellIndex < row.cells.length; cellIndex ++) {
                              if(row.cells[cellIndex].value instanceof Date) {
                                  row.cells[cellIndex].format = "dd-MM-yy hh:mm:ss"
                              }
                          }
                        }
                     },
                     resizable: true,
                     pageable: true,
                     sortable: true,
                     scrollable: true,
                     columns: [
                          {
                              field: "name",
                              title: "@Telesale name@",
                              width: 150,
                          },{
                              field: "code",
                              title: "@Telesale code@",
                              width: 150,

                          },{
                              field: "team",
                              title: "@Team@",
                              width: 120,
                          },
                          {
                              field: "count_appointment",
                              title: "@Appointment@",
                              width: 150,
                          },
                          {
                              field: "count_applied",
                              title: "@Customer Applied@",
                              width: 150,
                          },
                          {
                              field: "count_approve",
                              title: "@Customer Approve@",
                              width: 150,
                          },
                          {
                              field: "count_reject",
                              title: "@Customer Reject@",
                              width: 150,
                          },
                          {
                              field: "count_release",
                              title: "@Customer Release@",
                              width: 150,
                          },

                     ],
                      noRecords: {
                          template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                      }
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
                  var menu = $("#action-menu");
                  if(!menu.length) return;

                  $("html").on("click", function() {menu.hide()});

                  $(document).on("click", "#grid tr[role=row] a.btn-action", function(e){
                      let btna = $(e.target);
                      let row = $(e.target).closest("tr");
                      e.pageX -= 20;
                      showMenu(e, row, btna);
                  });

                  function showMenu(e, that,btna) {
                      //hide menu if already shown
                      menu.hide();

                      //Get id value of document
                      var uid = $(that).data('uid');
                      var fltnumber = btna.data('flt');
                      var date = btna.data('date');
                      if(uid)
                      {
                          menu.find("a").data('uid',uid);
                          menu.find("a").data('fltnumber',fltnumber);
                          menu.find("a").data('date',date);
                          menu.find("a").data('dpt',btna.data('dpt'));
                          menu.find("a").data('arv',btna.data('arv'));
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
      window.onload = function() {
         Table.init();
         var dateRange = 30;
         var nowDate = new Date();
         var date =  new Date(),
               timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
               date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);

         var fromDate = new Date(date.getTime() + timeZoneOffset - (dateRange - 1) * 86400000);
         var toDate = new Date(date.getTime() + timeZoneOffset + kendo.date.MS_PER_DAY -1)
         var observable = kendo.observable({
             trueVar: true,
             loading: false,
             visibleReport: false,
             visibleNoData: false,
            fromDateTime: fromDate,
            toDateTime: toDate,
            filterField: "",
            fromDate: kendo.toString(fromDate, "dd/MM/yyyy H:mm"),
            toDate: kendo.toString(toDate, "dd/MM/yyyy H:mm"),

            startDate: function(e) {
               var start = e.sender,
                  startDate = start.value(),
                  end = $("#end-date").data("kendoDatePicker"),
                     endDate = end.value();

                   if (startDate) {
                       startDate = new Date(startDate);
                       startDate.setDate(startDate.getDate());
                       end.min(startDate);
                   } else if (endDate) {
                       start.max(new Date(endDate));
                   } else {
                       endDate = new Date();
                       start.max(endDate);
                       end.min(endDate);
                   }
            },
            endDate: function(e) {
               var end = e.sender,
                  endDate = end.value(),
                  start = $("#start-date").data("kendoDatePicker"),
                  startDate = start.value();

                if (endDate) {
                    endDate = new Date(endDate);
                    endDate.setDate(endDate.getDate());
                    start.max(endDate);
                } else if (startDate) {
                    end.min(new Date(startDate));
                } else {
                    endDate = new Date();
                    start.max(endDate);
                    end.min(endDate);
                }
            },
            search: function() {
               this.set("fromDate", kendo.toString(this.get("fromDateTime"), "dd/MM/yyyy H:mm"));
               this.set("toDate", kendo.toString(this.get("toDateTime"), "dd/MM/yyyy H:mm"));
               this.asyncSearch();
            },
             asyncSearch: async function() {
               var field = "created_at";
               var fromDateTime = new Date(this.fromDateTime.getTime() - timeZoneOffset).toISOString();
                var toDateTime = new Date(this.toDateTime.getTime() - timeZoneOffset).toISOString();

                var filter = {
                    logic: "and",
                    filters: [
                        {field: field, operator: "gte", value: fromDateTime},
                        {field: field, operator: "lte", value: toDateTime}
                    ]
                };

               Table.dataSource.filter(filter);

            },
         })
         kendo.bind($(".mvvm"), observable);
      };

      async function emailSmsForm(flight_number,dpt,arv,date,status) {
        $rightForm = $("#right-form");
        var formHtml = await $.get(`${ENV.templateApi}sc_policy/form?flight_number=${flight_number}&status=${status}&date=${date}&dpt=${dpt}&arv=${arv}`);
        kendo.destroy($rightForm);
        $rightForm.empty();
        $rightForm.append(formHtml);
      }
    </script>
</div>


<script id="detail-dropdown-template" type="text/x-kendo-template">
   <li data-bind="css: {dropdown-header: active}"><a data-bind="click: goTo, text: name, attr: {href: url}"></a></li>
</script>
<script type="text/x-kendo-template" id="diallist-detail-field-template">
   <div class="item">
        <span style="margin-left: 10px" data-bind="text: title"></span>
        <i class="fa fa-arrow-circle-o-right text-success" style="float: right; margin-top: 10px"></i>
    </div>
</script>
<script type="text/x-kendo-template" id="data-field-template">
   <div class="item">
      <span class="handler text-center"><i class="fa fa-arrows-v"></i></span>
        <span data-bind="text: field"></span>
    </div>
</script>
