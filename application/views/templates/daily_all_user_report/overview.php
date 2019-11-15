<div id="page-content">
    <!-- END Table Styles Header -->
    <div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
        <div class="row form-horizontal">
            <div class="form-group col-sm-3">
               <label class="control-label col-xs-4">Date</label>
               <div class="col-xs-8">
                  <input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy" name="fromDateTime" disabled="" data-bind="value: fromDateTime">
               </div>
            </div>
            <div class="form-group col-sm-9 text-right ">
              <div class="btn-group btn-group-sm">
                  <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
              </div>
            </div>
        </div>
        <div class="row chart-page" style="display: none;" >
          <div data-role="tabstrip">
            <ul>
                <li class="k-state-active">
                    SMS SIBS
                </li>
                <li>
                    SMS CARD
                </li>
            </ul>
            <div>
              <div class="container-fluid">
                <div class="col-sm-12">
                    <div id="grid"></div>
                </div>
              </div>
            </div>
            <div>
                <div class="container-fluid">
                  <div class="col-sm-12">
                    <div id="grid_1"></div>
                  </div>
                </div>
            </div>
        </div>
    </div>
    <div id="action-menu">
        <ul>
        </ul>
    </div>
    <script>
      
      function saveAsExcel() {
        $.ajax({
          url: ENV.reportApi + "loan/loan_group_report/downloadExcel",
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
