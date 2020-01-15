<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>Write of expectation</li>
        <li class="pull-right none-breakcrumb" id="top-row">
            <div class="btn-group btn-group-sm">
                <!-- <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a> -->
            </div>
        </li>
    </ul>
    <!-- END Table Styles Header -->
    <div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
        <div class="row form-horizontal">
            <div class="form-group col-sm-3">
               <label class="control-label col-xs-3">@Date@</label>
               <div class="col-xs-8">
                  <input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy" name="fromDateTime" data-bind="value: fromDateTime, events: {change: startDate}" disabled>
               </div>
            </div>
            <!-- <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@To date@</label>
               <div class="col-xs-8">
                  <input id="end-date" data-role="datepicker" data-format="dd/MM/yyyy H:mm:ss" name="toDateTime" data-bind="value: toDateTime, events: {change: endDate}">
               </div>
            </div>
            <div class="form-group col-sm-4 text-center">
                <button class="k-button" data-bind="click: search">@Search@</button>
            </div> -->
        </div>
       
        <h3 class="col-sm-12 text-center" style="margin-bottom: 20px;color: #27ae60">Write of expectation</h3>
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
                     pageSize: 10,
                     transport: {
                        read: ENV.reportApi + "loan/write_of_expectation/write_of",
                        parameterMap: parameterMap
                     },
                     schema: {
                        data: "data",
                        total: "total",
                        parse: function (response) {
                        response.data.map(function(doc) {
                        // doc.due_date = new date(doc.due_date * 1000)
                        doc.Due_date = doc.Due_date ? new Date(doc.Due_date * 1000) : undefined;
                      
            
                        return doc;
                        })
                         return response;
                     }
                     }
                  });

                  var grid = this.grid = $("#grid").kendoGrid({
                    toolbar: ["excel"],
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
                              field: "Group",
                              title: "Group",
                              width: 80
                          },
                          {
                              field: "Account_number",
                              title: "AC NUMBER",
                              width: 140
                          },{
                              field: "Name",
                              title: "Name",
                              width: 130
                          },
                          {
                              field: "Due_date",
                              title: "Due date",
                              template: data => gridDate(data.Due_date, 'dd/MM/yyyy'),
                              width: 130
                          },
                          {
                              field: "Release_date",
                              title: "Release date",
                            
                              width: 130
                          },
                          {
                              field: "Release_amount",
                              title: "Release amount",
                              width: 130
                          },
                          {
                              field: "Interest_rate",
                              title: "Interest rate",
                              width: 130
                          },
                          {
                              field: "Loan_Term",
                              title: "Loan Term",
                              width: 130
                          },
                          {
                              field: "Off_balance",
                              title: "Off balance Principal amount as of dated",
                              width: 130
                          },
                          {
                              field: "Actual_payment",
                              title: "Actual payment (term)",
                              width: 130
                          },
                          {
                              field:"Profession",
                              title:"Profession",
                              width: 130
                          },
                          {
                              field:"MRC",
                              title:"MRC",
                              width: 130
                          },
                          {
                              field:"Reason_of_uncollected",
                              title:"Reason of uncollected",
                              width: 130
                          },
                          {
                              field:"If_bike_is_defined",
                              title:"If bike is defined",
                              width: 130
                          },
                          {
                              field:"If_site_visit_made",
                              title:"If site visit made",
                              width: 130
                          },
                          {
                              field:"If_there_is_fielder_in_location",
                              title:"If there is fielder in location",
                              width: 130
                          },
                          {
                              field:"Last_date_made_field_visit",
                              title:"Last date made field visit",
                              width: 130
                          },
                          {
                              field:"No_of_site_visit_made",
                              title:"No. of site visit made",
                              width: 130
                          },
                          {
                              field:"Last_date_made_collections_call",
                              title:"Last date made collections call",
                              width: 130
                          },
                          {
                              field:"If_still_collectable",
                              title:"If still collectable",
                              width: 130
                          },
                          {
                              field:"SMS_sent",
                              title:"SMS sent",
                              width: 130
                          },
                          {
                              field:"Call",
                              title:"Call",
                              width: 130
                          },
                          {
                              field:"Send_reminder_letter",
                              title:"Send reminder letter",
                              width: 130
                          },
                          {
                              field:"Litigation",
                              title:"Litigation",
                              width: 130
                          },
                          {
                              field:"Note",
                              title:"Note",
                              width: 130
                          },
                          {
                              field:"Cus_ID",
                              title:"Customer's ID",
                              width: 130
                          },
                          {
                              field:"Product_code",
                              title:"Product code",
                              width: 130
                          },
                          {
                              field:"Partner_name_company",
                              title:"Tên cty partner",
                              width: 130
                          },
                          {
                              field:"Phone",
                              title:"Mobile phone",
                              width: 130
                          },
                          {
                              field:"Outstanding_balance",
                              title:"Outstanding balance",
                              width: 130
                          },
                          {
                              field:"Dealer_code",
                              title:"Dealer code",
                              width: 130
                          },
                          {
                              field:"Dealer_name",
                              title:"Dealer name",
                              width: 130
                          }
                         

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

         // var fromDate = new Date(date.getTime() + timeZoneOffset - (dateRange - 1) * 86400000);
         var fromDate = new Date(date.getTime() + timeZoneOffset);
         var toDate = new Date(date.getTime() + timeZoneOffset + kendo.date.MS_PER_DAY -1)
         var observable = kendo.observable({
             trueVar: true,
             loading: false,
             visibleReport: false,
             visibleNoData: false,
            fromDateTime: fromDate,
            toDateTime: toDate,
            filterField: "",
            cif:"",
            loanContract: "",
            nationalID: "",
            fromDate: kendo.toString(fromDate, "dd/MM/yyyy H:mm"),
            toDate: kendo.toString(toDate, "dd/MM/yyyy H:mm"),

            search: function() {
               this.set("fromDate", kendo.toString(this.get("fromDateTime"), "dd/MM/yyyy H:mm"));
               this.set("toDate", kendo.toString(this.get("toDateTime"), "dd/MM/yyyy H:mm"));
               this.asyncSearch();
            },
             asyncSearch: async function() {
               var field = "created_at";
               var fromDateTime = new Date(this.fromDateTime.getTime() - timeZoneOffset).toISOString();
                var toDateTime = new Date(this.toDateTime.getTime() - timeZoneOffset).toISOString();
                var cif = this.cif;
                var loanContract = this.loanContract;
                var nationalID = this.nationalID;
                var field_1 = 'CUS_ID';
                var field_2 = 'account_number';
                var field_3 = 'LIC_NO';

                if (cif != '') {
                    filter_1 = {field: field_1, operator: "eq", value: cif};
                }else{
                    filter_1 = {field: field_1, operator: "neq", value: cif};
                }
                if (loanContract != '') {
                    filter_2 = {field: field_2, operator: "eq", value: loanContract};
                }else{
                    filter_2 = {field: field_2, operator: "neq", value: loanContract};
                }
                if (nationalID != '') {
                    filter_3 = {field: field_3, operator: "eq", value: nationalID};
                }else{
                    filter_3 = {field: field_3, operator: "neq", value: nationalID};
                }
                var filter = {
                    logic: "and",
                    filters: [
                        filter_1,filter_2,filter_3
                    ]
                };

               Table.dataSource.filter(filter);

            },
         })
         kendo.bind($(".mvvm"), observable);
      };

      
    </script>
    <script>
        function saveAsExcel() {
            $.ajax({
              url: ENV.reportApi + "loan/write_of_expectation/exportWriteOf",
              type: 'POST',
              dataType: 'json',
              timeout: 30000
            })
            .done(function(response) {
              if (response.status == 1) {
                window.location = response.data
              }
            })
            .fail(function() {
              console.log("error");
            });

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
