<script>
var Config = {
    crudApi: `${ENV.reportApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "chat/agent_chat_summary/read",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            starttime: {type: "date"},
            endtime: {type: "date"},
            statuscode : {type: "number"},
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.starttime = new Date(doc.starttime * 1000);
            doc.endtime = doc.endtime ? new Date(doc.endtime * 1000) : null;
            return doc;
        })
        return response;
    },
    columns: [/*{
            field: "starttime",
            title: "Start",
            template: function(dataItem) {
                return (kendo.toString(dataItem.starttime, "dd/MM/yy H:mm:ss") ||  "").toString();
            },
            width: 140
        },{
        	field: "endtime",
            title: "End",
            template: function(dataItem) {
                return (kendo.toString(dataItem.endtime, "dd/MM/yy H:mm:ss") ||  "").toString();
            },
            width: 140
        },{
            field: "extension",
            title: "Extension",
            width: 100
        },{
            field: "status.text",
            title: "Status",
            width: 180
        },{
            field: "substatus",
            title: "Sub status",
            width: 140
        },{
            field: "note",
            title: "Note"
        },{
            field: "endnote",
            title: "End note"
        }*/
        /*{
            field:'Agent',
            title:'Agent',
        },
        {
            field:'Agent',
            title:'Session Time',
        },
        {
            field:'Agent',
            title:'Pause Time',
        },
        {
            field:'Agent',
            title:'Work Time',
        },*/
       /* {
            field:'Agent',
            title:'Agent',
        },
        {
            field:'fb_message',
            title:'Fb message',
        },
        {
            field:'fb_comment',
            title:'Fb comment',
        },
        {
            field:'zalo_message',
            title:'Zalo message',
        },
        {
            field:'livechat_message',
            title:'Livechat message',
        },
        
        {
            field:'Agent',
            title:'Total conversion',
        },*/
        ]
}; 
</script>

<!-- Page content -->
<div id="page-content">
    <div align="center" style="align-items: center">
        <div style="width: 40%;" >
            <p class="text-left well-sm" style="background-color: #ffcc00; color: #fff; margin-bottom: 0;">
                SEARCH
            </p>
            <form method="GET">
                <table class="text-center table table-borderless table-striped table-vcenter themed-background-white" style="border-radius: 5px;box-shadow: 0 4px 4px rgba(0,0,0,0.3), -4px 4px 0 rgba(0,0,0,0.3);">
                    <tbody id="missed-calls" >

                        <tr> 
                            <td>Groups</td>
                            <td>
                                <select id="optional"  name="example-select[]" multiple="multiple" class="form-control" required>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Start date</td>
                            <td> <input id="from" title="datepicker" /></td>
                        </tr>
                        <tr>
                            <td>End date</td>
                            <td> <input id="to" title="datepicker" /></td>   
                        </tr>
                        <tr>
                            <td></td>
                            <td> <button id="searchbydate" class="k-primary k-button" onclick="searchByDate(event)"><?=lang('search')?></button></td>
                        </tr>
                    </tbody>

                </table>
            </form>
        </div>
    </div>


        <div style="margin: 0 20px;" >
            <p class="text-left well-sm" style="background-color: #ffcc00; color: #fff; margin-bottom: 0;">
                Agent Chat Summary
            </p>
            <table class="text-center table table-borderless table-striped table-vcenter themed-background-white" style="border-radius: 5px;box-shadow: 0 4px 4px rgba(0,0,0,0.3), -4px 4px 0 rgba(0,0,0,0.3);">
                <tbody id="missed-calls" >
                    <tr>
                        <td>Queues</td>
                        <td id="grouparray"></td>
                    </tr>
                    <tr>
                        <td>Start date</td>
                        <td id="startdate-td"></td>
                    </tr>
                    <tr>
                        <td>End date</td>
                        <td id="enddate-td"></td>
                    </tr>
                    <tr>
                        <td>Preiod</td>
                        <td id="period-td"></td>
                    </tr>
                </tbody>
            </table>
        </div>

      
        <div style="clear: both;">

            <div id="example" class="absConf" style="margin: 0 auto">
                
                <p class="text-left well-sm" style="background-color: #ffcc00; color: #fff; margin-bottom: 0;">
                    Chat By Groups
                </p>
                <div id="grid-group-availability" ></div>
            </div>
</div>
    </div>
            <script>
                var agentList = [];
                var groupList = [];
                $(document).ready(function () {
                    var from = sessionStorage.getItem("from");
                    if(from == null ){
                        from = new Date();
                    }
                    var to = sessionStorage.getItem("to");
                    if(to == null ){
                        to = new Date();
                    }
                    $("#from").kendoDatePicker({
                        value: from ,
                        format: "yyyy-MM-dd"
                    });

                    $("#to").kendoDatePicker({
                        value: to,
                        format: "yyyy-MM-dd" 
                    });

                    $("#startdate-td").html($("#from").val());
                    $("#enddate-td").html($("#to").val());

                    datediff(parseDate($("#from").val()), parseDate($("#to").val()));

                 
                    $("#grid-group-availability").kendoGrid({
                        toolbar: ["excel"],
                        excel: {
                            allPages: true,
                            fileName: "group Summary.xlsx"
                        },
                        pdf: {
                            allPages: true,
                            fileName: "group Summary.pdf"
                        },
                        dataSource: {
                            transport: {
                                read: {
                                    url: ENV.reportApi + "chat/agent_chat_summary/groupagentsum",
                                    type: "POST",
                                    dataType: 'json',
                                    contentType: "application/json",
                                    data: function () {
                                        return {from: $("#from").val(), to: $("#to").val(), groupList: groupList};
                                    }
                                },
                                parameterMap: function (data, type) {
                                    return kendo.stringify(data);
                                }
                            },
                            schema: {
                                data: function (response) {
                                    $.each(response.data, function (key, val) {
                                        val.group = val.group;
                                        val.fb_comment = val.fb_comment;
                                        val.fb_comment = val.fb_comment;
                                        val.zalo_message = val.zalo_message;
                                        val.livechat_message = val.livechat_message;
                                        val.total_conversation = val.total_conversation;
                                        val.total_conversation_all = response.total_conversation_all;
                                    });
                                    return response.data;
                                },
                                total: "total",
                                model: {
                                    id: "group",
                                    fields: {
                                        fb_message: {type: "number"},
                                        fb_comment: {type: "number"},
                                        zalo_message: {type: "number"},
                                        livechat_message: {type: "number"},
                                        total_conversation: {type: "number"},

                                        percent_fb_message: {type: "number"},
                                        percent_fb_comment: {type: "number"},
                                        percent_zalo_message: {type: "number"},
                                        percent_livechat_message: {type: "number"},

                                    }
                                }
                            },
                            pageSize: 16,
                            aggregate : [
                                {field: "fb_message", aggregate: "sum"},
                                {field: "fb_comment", aggregate: "sum"},
                                {field: "zalo_message", aggregate: "sum"},
                                {field: "livechat_message", aggregate: "sum"},
                                {field: "total_conversation", aggregate: "sum"},

                                {field: "percent_fb_message", aggregate: "sum"},
                                {field: "percent_fb_comment", aggregate: "sum"},
                                {field: "percent_zalo_message", aggregate: "sum"},
                                {field: "percent_livechat_message", aggregate: "sum"},
                            ],
                            scrollable: true
                        },
                        scrollable: {
                            virtual: true
                        },
                        excelExport: function (e) {
                            console.log(e);
                        },
                        scrollable: true,
                        filterable: true,
                        height: 400,
                        pageable: true,
                        columns: [ 
                            {
                                field:'group',
                                title:'Group',
                                // filterable: {multi: true}, template: '<a href="javascript:void(0);" onclick="redirectToDetail(#= agent #);">#= agent #</a>',
                                footerTemplate: 'Total'
                            },
                            {
                                field:'total_conversation',
                                title:'% Total conversion',
                                filterable: false, 
                                template: '#= Math.round(total_conversation> 0 ? (total_conversation/total_conversation_all)*100 : 0) #%',
                                //template: '#= (total_conversation/data.total_conversation.sum)/100 #%',
                                footerTemplate: '#= data.total_conversation.sum > 0 ? 100 : 0 #%'
                            },

                            {
                                field:'total_conversation',
                                title:'Total conversion',
                                filterable: false, 
                                footerTemplate: '#= data.total_conversation.sum #'
                            },
                            {
                                // field:'percent_fb_message',
                                title:'% Fb message',
                                filterable: false,
                                template: '#= percent_fb_message #%',
                                footerTemplate: '#= data.fb_message.sum > 0 ?  (data.fb_message.sum/data.total_conversation.sum)*100 : 0 #%'
                            },
                            {
                                field:'fb_message',
                                title:'Fb message',
                                filterable: false,
                                footerTemplate: '#= data.fb_message.sum #'
                            },
                            
                            {
                                // field:'fb_comment',
                                title:'% Fb comment',
                                filterable: false,
                                template: '#= percent_fb_comment #%',
                                // footerTemplate: '#= data.percent_fb_comment.sum # %'
                                footerTemplate: '#= data.fb_comment.sum > 0 ?  (data.fb_comment.sum/data.total_conversation.sum)*100 : 0 #%'
                            },
                            {
                                field:'fb_comment',
                                title:'Fb comment',
                                filterable: false,
                                footerTemplate: '#= data.fb_comment.sum #'
                            },
                            
                            {
                                // field:'zalo_message',
                                title:'% Zalo message',
                                filterable: false,
                                template: '#= percent_zalo_message #%',
                                // footerTemplate: '#= data.percent_zalo_message.sum #'
                                footerTemplate: '#= data.zalo_message.sum > 0 ?  (data.zalo_message.sum/data.total_conversation.sum)*100 : 0 #%'

                            },
                            {
                                field:'zalo_message',
                                title:'Zalo message',
                                filterable: false,
                                footerTemplate: '#= data.zalo_message.sum #'
                            },
                           
                            {
                                // field:'livechat_message',
                                title:'% Livechat message',
                                filterable: false,
                                template: '#= percent_livechat_message #%',
                                // footerTemplate: '#= data.percent_livechat_message.sum #'
                                footerTemplate: '#= data.livechat_message.sum > 0 ?  (data.livechat_message.sum/data.total_conversation.sum)*100 : 0 #%'
                            },
                            {
                                field:'livechat_message',
                                title:'Livechat message',
                                filterable: false,
                                footerTemplate: '#= data.livechat_message.sum #'
                            },
                        ],
                       
                    });
});


            //Here is a quick and dirty implementation of datediff, as a proof of concept to solve the problem as presented in the question. It relies on the fact that you can get the elapsed milliseconds between two dates by subtracting them, which coerces them into their primitive number value (milliseconds since the start of 1970).

            // new Date("dateString") is browser-dependent and discouraged, so we'll write
            // a simple parse function for U.S. date format (which does no error checking)
            function parseDate(str) {
                var mdy = str.split('/');
                //        return new Date(mdy[2], mdy[0]-1, mdy[1]);
                console.log(new Date(str));
                return new Date(str);
            }

            function datediff(first, second) {
                // Take the difference between the dates and divide by milliseconds per day.
                // Round to nearest whole number to deal with DST.
                var totalData = Math.round((second - first) / (1000 * 60 * 60 * 24)) + 1;
                var stringDate = (totalData > 1) ? 'Ngày' : 'Ngày';
                $("#period-td").html(totalData.toString() + ' ' + stringDate);
            }

            function secondsTimeSpanToHMS(s) {
                var h = Math.floor(s / 3600); //Get whole hours
                var hstr = "0" + h;
                var m = Math.abs(Math.floor(s % 3600 / 60));
                var mstr = "0" + m;
                var s = Math.abs(Math.floor(s % 3600 % 60));
                var sstr = "0" + s;
                return hstr.toString().substr(-2) + ":" + mstr.toString().substr(-2) + ":" + sstr.toString().substr(-2);
            }

            function findMaxWorkTime(workTimeList) {
                return Math.max(...workTimeList);
            }

            function totalAndAvgWorkTime(workTimeList) {
                var totalWorkTime = 0;
                var avgWorkTime = 0;
                $.each(workTimeList, function (key, val) {
                    totalWorkTime = totalWorkTime + val;
                });
                avgWorkTime = Math.round(totalWorkTime / workTimeList.length);
                return {totalWorkTime: totalWorkTime, avgWorkTime: avgWorkTime};
            }

            function redirectToDetail(extension) {
                //        var starttime = 
                window.location.replace('<?= base_url() ?>reports/report_zendesk_agent_summary_detail_agent/index/' + extension + '%' + $("#from").val().toString().replace(/\//g, '-') + '%' + $("#to").val().toString().replace(/\//g, '-'));
            }

            function searchByDate(e) {
                e.preventDefault();
                var form = $("#from").val() ;
                var to = $("#to").val() ;
                sessionStorage.setItem("from", form );
                sessionStorage.setItem("to", to );
                var queueText = getMultiselectValueAndText($('#optional :selected'));
                console.log(queueText);
                if(queueText) {
                    groupList = queueText.realvalues.map(function(value, index) {
                        console.log(value);
                        return value;
                    /*return {extension:  value}//*///return value
                });
                    /*for (var i = 0; i < queueText.realvalues.length; i++) {
                        console.log(queueText.realvalues[i]);
                        groupList.push(queueText.realvalues);
                    }*/
                    queueText = queueText.textvalues.join(', ');
                    
                    
                }
                console.log(groupList);
                $("#grouparray").text(queueText);
               
              $("#startdate-td").html($("#from").val());
              $("#enddate-td").html($("#to").val());
              datediff(parseDate($("#from").val()), parseDate($("#to").val()));
              $("#grid-group-availability").data("kendoGrid").dataSource.read();
          }

          function getMultiselectValueAndText(htmlValue) {
            var realvalues = [];
            var textvalues = [];
            htmlValue.each(function(i, selected) {
                realvalues[i] = $(selected).val();
                textvalues[i] = $(selected).text();
            });
            return {realvalues: realvalues, textvalues: textvalues}
        }

        function arrayColumn(array, columnName) {
            return array.map(function(value,index) {
                return value[columnName];
            })
        }

       
    </script>
    <script>
        $(document).ready(function() {
            var dataSource_ListAgents = new kendo.data.DataSource({
                    transport: {
                        read: ENV.reportApi + "chat/agent_chat_summary/ListAgents"
                    },
                });
            // console.log(dataSource_ListAgents.length);
            dataSource_ListAgents.fetch(function(){
              var data = dataSource_ListAgents.data();
              /*var agentText = [];
              for (var i = 0; i < data.length; i++) {
                    agentText.push(data[i]);
              }*/
              // agentText = data.join(', ');
              $("#numberOfAgent").text(data.length);
            });


            var dataSource_group = new kendo.data.DataSource({
                    transport: {
                        read: ENV.reportApi + "chat/agent_chat_summary/getGroups"
                    },
                    schema: {
                        data: "data",
                    },
                });
            dataSource_group.fetch(function(){
              var data = dataSource_group.data();
              var groupText = [];
              for (var i = 0; i < data.length; i++) {
                  groupText.push(data[i].name);
              }
              groupText = groupText.join(', ');
              $("#grouparray").text(groupText);
            });

            var optional = $("#optional").kendoMultiSelect({   
                dataTextField: "name",
                dataValueField: "id",
                dataSource: dataSource_group,            
            });
            // var multiselect = $("#optional").data("kendoMultiSelect");
            // console.log(dataSource_group.value());
            // console.log(dataSource_group.data());
            // console.log(multiselect.dataSource.data);
            // console.log(multiselect.value());
            // optional.setDataSource(dataSource);
            // $.ajax({
            //     type:'post',
            //     dataType: "json",
            //     url:ENV.reportApi + "chat/agent_chat_summary/getGroups",
            //     data:{
            //     },
            //     success:function(dataSource) {                    
            //         optional.setDataSource(dataSource);
            //          myMultiselect.kendoMultiSelectBox({
            //             dataTextField: "Text",
            //             dataValueField: "Value",
            //             dataSource: response ,
            //             emptySelectionLabel: "Please select..."

            //         });

            //          myMultiselect.data("kendoMultiSelectBox").bind("selectionChanged", function (e) { $('#results').html("selected item count: " + e.newValue.length); });
            //     }
            // }); 
            
            var myMultiselect = $('#agentMultiSelect');
            myMultiselect.kendoMultiSelectBox({
                dataTextField: "Text",
                dataValueField: "Value",
                emptySelectionLabel: "Please select..."

            });
        });
    </script>

    <?php 
    if($this->session->userdata('isadmin') != 1){
        ?>
        <script>
          $(document).ready(function() {
           var data =   $('#optional').val();
           var myMultiselect = $('#agentMultiSelect');
           $.ajax({
              type:'post',
              dataType: "json",
              url:"<?php echo site_url('reports/report_zendesk/ListAgent') ?>",
              data:{
                 data : data
             },
             success:function(response) {          
                 myMultiselect.kendoMultiSelectBox({
                    dataTextField: "Text",
                    dataValueField: "Value",
                    dataSource: response ,
                    emptySelectionLabel: "Please select..."

                });

                 myMultiselect.data("kendoMultiSelectBox").bind("selectionChanged", function (e) { $('#results').html("selected item count: " + e.newValue.length); });
             }
         });       
           setTimeout(function(){
            $("#agentMultiSelect").data("kendoMultiSelectBox").value(<?= $this->session->userdata("extension") ?>);
            $("#searchbydate").trigger('click');
        },500)
       })
   </script>
   <?php } ?>
<!-- END Page Content -->