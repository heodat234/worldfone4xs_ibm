<script>
    var Config = {
        crudApi: `${ENV.vApi}`,
        templateApi: `${ENV.templateApi}`,
        observable: {
        },
        collection: 'pnrdetail'
    };

    function findPNR(e) {
        var pnr_code = $("#pnr-code").val();
        Config.observable.pnr_code = pnr_code;
        router.navigate(`/detail/${pnr_code}`);
        e.preventDefault();
    }
</script>

<script id="layout" type="text/x-kendo-template">
    <div class="row" style="text-align: center">
        <h3>@PNR Info@</h3>
    </div>
    <div class="container-fluid">
        <div class="row" style="margin: 10px 0">
            <div class="block full">
                <div class="block-title clearfix">
                    <h2><i class="fa fa-search" aria-hidden="true"></i> @SEARCH PNR@</strong></h2>
                </div>
                <div>
                    <form class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-1">@PNR Code@</label>
                            <div class="col-sm-3">
                                <input id="pnr-code" type="text" class="k-textbox upper-case-input" style="width: 100%" data-bind="value: pnr_code">
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-sm btn-primary btn-save"  onclick="findPNR(event)">@Search@</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="pnr-body"></div>
    </div>
</script>