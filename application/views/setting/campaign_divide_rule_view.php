<div class="col-sm-12 contain-view" style="height: 100vh">
    <h4 class="fieldset-legend" style="margin: 0 0 20px"><span
            style="font-weight: 500; background-color: #eaedf1; line-height: 1">CARD Group A</span></h4>
    <div class="row" style="margin-top: 10px">
        <div class="col-sm-2 text-right">
            <label><i class="text">A01</i></label>
        </div>
        <div class="col-sm-10">
            <select data-role="multiselect" data-clear-button="false" data-value-primitive="true"
                data-bind="value: a1, source: groupList"></select>
        </div>
        <div class="col-sm-12 text-center" style="padding-top: 20px">
        </div>
    </div>

    <div class="row" style="margin-top: 10px">
        <div class="col-sm-2 text-right">
            <label><i class="text">A02</i></label>
        </div>
        <div class="col-sm-10">
            <select data-role="multiselect" data-clear-button="false" data-value-primitive="true"
                data-bind="value:a2, source: groupList"></select>
        </div>
        <div class="col-sm-12 text-center" style="padding-top: 20px">
        </div>
    </div>

    <div class="row" style="margin-top: 10px">
        <div class="col-sm-2 text-right">
            <label><i class="text">A03</i></label>
        </div>
        <div class="col-sm-10">
            <select data-role="multiselect" data-clear-button="false" data-value-primitive="true"
                data-bind="value:a3, source: groupList"></select>
        </div>
        <div class="col-sm-12 text-center" style="padding-top: 20px">
            <button class="k-button" data-bind="click: saveCard" style="font-size: 18px">@Assign@</button>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $.get(ENV.vApi + "Campaign_divide_rule/readRuleCard",'',function(response){
        var a1 = a2 = a3 = '';
        if(response != ''){
            response.forEach(element => {
                switch(element.debt_group){
                    case 'a1':
                    a1rule = element.group_divided;
                    break;
                    case 'a2':
                    a2rule = element.group_divided;
                    break;
                    case 'a3':
                    a3rule = element.group_divided;
                    break;    
                }
                
            })
        }

        var containObservable = kendo.observable({
            groupList: new kendo.data.DataSource({
                transport: {
                    read: ENV.vApi + "Campaign_divide_rule/read_card_group_A",
                },
            }),

            a1: a1rule,
            a2: a2rule,
            a3: a3rule,

            saveCard: function(){
               $.ajax({
                    url: ENV.vApi + "Campaign_divide_rule/saveRuleCard",
                    type: "POST",
                    contentType: "application/json; charset=utf-8",
                    data: JSON.stringify({a1: this.a1.toJSON(), a2: this.a2.toJSON(), a3: this.a3.toJSON()}),
                    success: (response) => {
                        if(response){
                            notification.show("Success", "success");
                        }else{
                            notification.show("Error", "error");
                        }
                    },
                });
            }
        });

      kendo.bind(".contain-view", containObservable);
    })


})
</script>

<style type="text/css">

</style>