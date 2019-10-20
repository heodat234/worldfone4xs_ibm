<li data-toggle="tooltip" title="@Notification@" data-placement="right" id="header-notification" data-bind="attr: {data-total: item.total}">
    <a href="javascript:void(0)" class="btn btn-alt btn-sm btn-default" data-toggle="dropdown" data-bind="click: openList">
        <i class="fa fa-bell" data-bind="css: {animation-hatch: item.total}"></i>
        <span class="label label-primary label-indicator animation-floating" data-bind="text: item.totalText"></span>
    </a>
    <ul class="dropdown-menu dropdown-custom dropdown-options dropdown-notification" style="left: -200px; width: 250px; max-height: 70vh; overflow-y: scroll; display: none" data-template="header-notification-template" data-bind="source: item.data, visible: item.total" >
    </ul>
    <ul class="dropdown-menu dropdown-custom dropdown-options" style="left: -150px; width: 200px" data-bind="invisible: item.total">
        <li class="dropdown-header text-center"><a>@Not any notification@</a></li>
    </ul>
</li>
<script id="header-notification-template" type="text/x-kendo-template"> 
    <li data-bind="css: {dropdown-header: odd}">
        <a href="javascript:void(0)" data-bind="click: openLink, attr: {data-link: link, data-id: id}">
            <i class="#: (data.icon || '').toString() + " " + (data.color || '').toString() # icon-notification"></i> 
            <span class="title-notification #: (data.color || '').toString() #" data-bind="text: title"></span>
            <p class="content-notification" data-bind="html: content, visible: content"></p>
            <p class="time-since-notification text-right">
                <small data-bind="text: createdAtFrom, attr: {title: createdAtText}"></small>
            </p>
        </a>
    </li>
</script>
<script type="text/javascript">
    function notificationWidget(e) {
        $headerNotification = $("#header-notification");
        var previousTotal = Number($headerNotification.find(".label-indicator").text());

        var notificationWidgetObservable = kendo.observable({
            item: {total: 0},
            openList: function(e) {
                if( !$headerNotification.hasClass("open") ) {
                    $.ajax({
                        url: ENV.restApi + "notification",
                        data: {q: JSON.stringify({
                            filter: {
                                logic: "and",
                                filters: [
                                    {field: "active", operator: "eq", value: true},
                                    {field: "to", operator: "eq", value: ENV.extension},
                                    {field: "read.extension", operator: "neq", value: ENV.extension}
                                ]
                            }
                        })},
                        success: (res) => {
                            if(res.total) {
                                res.data.map((doc, index) => {
                                    doc.createdAtDate = new Date(doc.createdAt * 1000);
                                    doc.createdAtText = gridDate(doc.createdAtDate);
                                    doc.createdAtFrom = time_since(doc.createdAtDate);
                                    if(index % 2 != 0)
                                        doc.odd = true;
                                });
                                this.set("item.data", res.data);
                            }
                        }
                    })
                }
            },
            openLink: function(e) {
                $currentTarget = $(e.currentTarget);
                var id = $currentTarget.data("id");
                $.ajax({
                    url: ENV.vApi + "widget/readNotification/" + id,
                    type: "DELETE",
                    success: (response) => {
                        if(response.status) {
                            var link = $currentTarget.data("link");
                            if(link != "undefined") {
                                window.open(link,'_blank','noopener');
                            }
                            this.set("item.total", this.get("item.total") - 1);
                            this.set("item.data", this.get("item.data").filter(doc => doc.id != id));
                            let itemJSONString = JSON.stringify(this.get("item").toJSON());
                            localStorage.setItem('header_notifications_event_data', itemJSONString);
                        }
                    }
                })
            }
        });
        kendo.bind($headerNotification, notificationWidgetObservable);
        if(!e.data) return;
        var item = JSON.parse(e.data);
        if(item.total) {

            if(item.total > previousTotal) {
                notification.show(`@You have@ @new notification@ @unread@`, "warning");
                document.title = ((item.total < 10) ? `(${item.total}) ` : "(9+) ") + currentTitle;
                $headerNotification.removeClass("open"); 
            }
            
            item.totalText = item.total;
        } else item.totalText = "";
        notificationWidgetObservable.set("item.total", item.total);
        notificationWidgetObservable.set("item.totalText", item.totalText);
    }

    function time_since(time) {

      switch (typeof time) {
        case 'number':
          break;
        case 'string':
          time = +new Date(time);
          break;
        case 'object':
          if (time.constructor === Date) time = time.getTime();
          break;
        default:
          time = +new Date();
      }
      var time_formats = [
        [60, '@seconds@', 1], // 60
        [120, '1 @minute@ @ago@', '1 @minute@ @from now@'], // 60*2
        [3600, '@minutes@', 60], // 60*60, 60
        [7200, '1 @hour@ @ago@', '1 @hour@ @from now@'], // 60*60*2
        [86400, '@hours@', 3600], // 60*60*24, 60*60
        [172800, '@Yesterday@', '@Tomorrow@'], // 60*60*24*2
        [604800, '@days@', 86400], // 60*60*24*7, 60*60*24
        [1209600, '@Last week@', '@Next week@'], // 60*60*24*7*4*2
        [2419200, '@weeks@', 604800], // 60*60*24*7*4, 60*60*24*7
        [4838400, '@Last month@', '@Next month@'], // 60*60*24*7*4*2
        [29030400, 'months', 2419200], // 60*60*24*7*4*12, 60*60*24*7*4
        [58060800, '@Last year@', '@Next year@'], // 60*60*24*7*4*12*2
        [2903040000, '@years@', 29030400], // 60*60*24*7*4*12*100, 60*60*24*7*4*12
      ];
      var seconds = (+new Date() - time) / 1000,
        token = '@ago@',
        list_choice = 1;

      if (seconds == 0) {
        return '@Just now@'
      }
      if (seconds < 0) {
        seconds = Math.abs(seconds);
        token = '@from now@';
        list_choice = 2;
      }
      var i = 0,
        format;
      while (format = time_formats[i++])
        if (seconds < format[0]) {
          if (typeof format[2] == 'string')
            return format[list_choice];
          else
            return Math.floor(seconds / format[2]) + ' ' + format[1] + ' ' + token;
        }
      return time;
    }
</script>