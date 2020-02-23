<style type="text/css">
.timeline-header + .timeline-list::after {
    top: 65px;
}
#list-post img.img-circle {
    max-height: 64px;
}

#list-post .push img.img-circle {
    max-height: 48px;
}
</style>

<ul class="breadcrumb breadcrumb-top">
    <li>@Tool@</li>
    <li>@News@</li>
</ul>
<!-- END Timeline Header -->

<!-- Timeline Content Row -->
<div class="container-fluid">
    <div class="row mvvm">

        <div class="col-sm-8" style="padding: 0">
            
            <!-- Feed Style Block -->
            <div class="block">
                <!-- Feed Style Title -->
                <div class="block-title">
                    <div class="block-options pull-right">
                        <a id="post-btn" href="javascript:void(0)" class="btn btn-alt btn-sm btn-primary active" data-toggle="block-toggle-content" data-type="create"><i class="fa fa-pencil-square-o" data-toggle="tooltip" title="@Post@" data-placement="left"></i></a>
                    </div>
                    <h2><strong>@Wall@</strong></h2>
                </div>
                <!-- END Feed Style Title -->

                <div class="block-content" style="display: none">
                    <!-- Quick Post Content -->
                    <form class="form-bordered" onsubmit="return false;" id="post-form">
                        <div class="form-group">
                            <input type="text" id="qpost-title" name="qpost-title" class="form-control" placeholder="@Enter@ @a@ @title@.." data-bind="value: item.title" required validationMessage="Empty!!!">
                        </div>
                        <div class="form-group">
                            <label class="checkbox-inline" for="example-inline-checkbox1">
                                <input type="checkbox" id="example-inline-checkbox1" name="example-inline-checkbox1" data-bind="checked: item.isPost"> @News@
                            </label>
                            <label class="checkbox-inline" for="example-inline-checkbox2" data-type="action/notify">
                                <input type="checkbox" id="example-inline-checkbox2" name="example-inline-checkbox2" data-bind="checked: item.isNotification"> @Notification@
                            </label>
                        </div>
                        <div class="form-group">
                            <textarea id="qpost-content" name="qpost-content" rows="3" class="form-control" placeholder="@Enter@ @content@.." data-bind="value: item.content" required validationMessage="Empty!!!"></textarea>
                        </div>
                        <div class="form-group form-actions">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-globe"></i>&nbsp;
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-custom" style="min-width: 100px">
                                    <li>
                                        <a href="javascript:void(0)" data-scope="all" data-bind="click: selectScope"><i class="fa fa-globe"></i> @All@</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-scope="group" data-bind="click: selectScope"><i class="fa fa-users"></i> @Group@</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-scope="private" data-bind="click: selectScope"><i class="fa fa-user-secret"></i> @Only@ @me@</a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="javascript:void(0)" data-scope="custom" data-bind="click: selectScope"><i class="fa fa-gear"></i> @Custom@</a>
                                    </li>
                                </ul>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary" data-bind="click: save, disabled: disabledPublish"><i class="fa fa-check"></i> @Publish@</button>
                        </div>
                    </form>
                    <!-- END Quick Post Content -->
                </div>

                <!-- Feed Style Content -->
                <div class="block-content-full" style="overflow-y: auto; max-height: 85vh">
                    <!-- You can remove the class .media-feed-hover if you don't want each event to be highlighted on mouse hover -->
                    <ul id="list-post" class="media-list media-feed media-feed-hover" data-bind="source: postSource" data-template="post-template">
                    </ul>

                    <div class="media text-center">
                        <a href="javascript:void(0)" class="btn btn-xs btn-default push" data-bind="click: viewMore" id="view-more-post">@View more@..</a>
                    </div>
                </div>
                <!-- END Feed Style Content -->
            </div>
            <!-- END Feed Style Block -->
        </div>

        <div class="col-sm-4" style="padding: 0">
            <!-- Timeline Style Block -->
            <div class="block full">
                <!-- Timeline Style Title -->
                <div class="block-title">
                    <h2><strong>@Notification@</strong></h2>
                </div>
                <!-- END Timeline Style Title -->

                <!-- Timeline Style Content -->
                <!-- You can remove the class .block-content-full if you want the block to have its regular padding -->
                <div class="timeline block-content-full">
                    <h3 class="timeline-header" style="font-size: 14px; padding-left: 88px">
                        <label>
                            <input type="checkbox" data-bind="checked: checkedUnreadNotification,events: {click: changeUnreadNotification}"> <span>@Only@ @unread notifications@</span>
                        </label>
                    </h3>
                    <!-- You can remove the class .timeline-hover if you don't want each event to be highlighted on mouse hover -->
                    <ul class="timeline-list timeline-hover" data-template="notification-timeline-template" data-bind="source: notificationSource">
                    </ul>

                    <div class="media text-center">
                        <a href="javascript:void(0)" class="btn btn-xs btn-default push" data-bind="click: viewMoreNotification" id="view-more-notification">@View more@..</a>
                    </div>
                </div>
                <!-- END Timeline Style Content -->
            </div>
            <!-- END Timeline Style Block -->
        </div>
        
    </div>
</div>
<div id="popup-container" class="hidden"></div>
<!-- END Timeline Content Row -->
<script type="text/javascript">
    var todayMidnight = new Date();
    todayMidnight.setHours(0,0,0,0);
    window.onload = function() {
        var pageObservable = kendo.observable({
            checkedUnreadNotification: false,
            item: {
                isNotification: true,
                isPost: true,
                scope: "global"
            },
            selectScope: function(e) {
                let $currentTarget = $(e.currentTarget),
                    scope = $currentTarget.data("scope"),
                    $masterBtn = $currentTarget.closest(".btn-group").find(".dropdown-toggle");
                    $iconEle = $masterBtn.find("i");
                $iconEle.removeClass();
                $iconEle.addClass($currentTarget.find("i").attr('class'));
                switch(scope) {
                    case "all": default:
                        this.set("item.scope", scope);
                        this.set("disabledPublish", false);
                        break;
                    case "group":
                        this.set("item.scope", scope);
                        this.openPopupSelectGroup();
                        break;
                    case "private":
                        this.set("item.scope", scope);
                        this.set("disabledPublish", false);
                        break;
                    case "custom":
                        this.set("item.scope", scope);
                        this.openPopupSelectCustom();
                        break;
                }
            },
            openPopupSelectGroup: function(e) {
                this.set("disabledPublish", true);
                if($("#select-group-popup").data("kendoWindow")) {
                    $("#select-group-popup").data("kendoWindow").destroy();
                }
                var model = {
                    group_id: "",
                    groupOption: dataSourceDropDownList("Group", ["name"], {active: true}),
                    close: function(e) {
                        $("#select-group-popup").data("kendoWindow").close();
                    },
                    selectThisGroup: function(e) {
                        pageObservable.set("disabledPublish", false);
                        pageObservable.set("item.group_id", this.get("group_id"));
                        this.close();
                    }
                };
                var kendoView = new kendo.View("select-group-template", {model: model, wrap: false});
                kendoView.render("#popup-container");
                $("#select-group-popup").data("kendoWindow").center().open();
            },
            openPopupSelectCustom: function(e) {
                this.set("disabledPublish", true);
                if($("#select-custom-popup").data("kendoWindow")) {
                    $("#select-custom-popup").data("kendoWindow").destroy();
                }
                var model = {
                    members: [], 
                    userOption: dataSourceDropDownListPrivate("User", ["agentname", "extension"]),
                    close: function(e) {
                        $("#select-custom-popup").data("kendoWindow").close();
                    },
                    selectTheseMembers: function(e) {
                        pageObservable.set("disabledPublish", false);
                        pageObservable.set("item.to", this.get("members"));
                        this.close();
                    }
                };
                var kendoView = new kendo.View("select-custom-template", {model: model, wrap: false});
                kendoView.render("#popup-container");
                $("#select-custom-popup").data("kendoWindow").center().open();
            },
            save: function(e) {
                if (!$("#post-form").kendoValidator().data("kendoValidator").validate()) {
                    notification.show("@Your data is invalid@", "error");
                    return;
                }
                let data = this.get("item").toJSON();
                $.ajax({
                    url: ENV.vApi + `post/add`,
                    type: "POST",
                    contentType: "application/json; charset=utf-8",
                    data: JSON.stringify(data),
                    success: (res) => {
                        if(res.status) {
                            notification.show("@Success@", "success");
                            this.postSource.read();
                            this.notificationSource.read({unread: this.get("checkedUnreadNotification")});
                            this.set("item.title", "");
                            this.set("item.content", "");
                            $("#post-btn").click();
                        } else notification.show(res.message, "error");
                    }
                })
            },
            viewMore: function() {
                this.postSource.pageSize(this.postSource.pageSize() + 5);
            },
            likeThisPost: function(e) {
                var post_id = $(e.currentTarget).closest(".media").data("id");
                $.ajax({
                    url: ENV.vApi + "post/like/" + post_id,
                    success: (res) => {
                        if(res.status) {
                            this.postSource.read();
                        } else notification.show(res.message, "error");
                    } 
                })
            },
            postSource: new kendo.data.DataSource({
                pageSize: 5,
                serverPaging: true,
                transport: {
                    read: ENV.vApi + "post/read",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(res) {
                        if(res.data.length >= res.total) {
                            $("#view-more-post").remove();
                        }  
                        if(!res.total) {
                            setTimeout(() => {
                                $("#list-post").html(`<h3 class="text-center text-muted" style="padding-top: 20px">@Empty@</h3>`);
                            }, 500);
                        }
                        res.data.map(doc => {
                            doc.time_since = doc.createdAt ? time_since(new Date(doc.createdAt * 1000)) : "";
                            switch(doc.scope) {
                                case "global": default:
                                    doc.iconClass = "fa fa-globe";
                                    doc.scopeText = "@All@";
                                    break;
                                case "group":
                                    doc.iconClass = "fa fa-users";
                                    doc.scopeText = doc.group_name;
                                    break;
                                case "private":
                                    doc.iconClass = "fa fa-user-secret";
                                    doc.scopeText = "@Only@ @me@";
                                    break;
                                case "custom":
                                    doc.iconClass = "fa fa-gear";
                                    doc.scopeText = doc.to.join(",");
                                    break;
                            }
                        });
                        return res;
                    }
                }
            }),
            viewMoreNotification: function() {
                this.notificationSource.pageSize(this.notificationSource.pageSize() + 5);
            },
            notificationSource: new kendo.data.DataSource({
                serverPaging: true,
                pageSize: 5,
                transport: {
                    read: ENV.vApi + "post/readNotification",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(res) {
                        if(res.data.length >= res.total) {
                            $("#view-more-notification").remove();
                        }
                        res.data.map(doc => {
                            doc.createdAtText = (doc.createdAt > todayMidnight.getTime()/1000) ? gridTimestamp(doc.createdAt, "H:mm") : gridTimestamp(doc.createdAt, "dd/MM/yy");
                        });
                        return res;
                    }
                }
            }),
            changeUnreadNotification: function(e) {
                if(e.currentTarget.checked) {
                    this.notificationSource.read({unread: true});
                } else this.notificationSource.read({});
            },
            viewComment: function(e) {
                $currentTarget = $(e.currentTarget);
                $commentContainer = $currentTarget.closest(".media").find("ul.media-list.push");
                if($currentTarget.hasClass("shown")) {
                    $commentContainer.slideUp();
                    $currentTarget.removeClass("shown");
                } else {
                    if(!$currentTarget.hasClass("active")) {
                        $currentTarget.addClass("active");
                        var post_id = $currentTarget.closest(".media").data("id");
                        var commentTemplate = `<ul class="media-list push" data-bind="source: commentsSource" data-template="comment-template">
                        </ul>`;
                        var commentModel = window.commentModel = kendo.observable({
                            commentsSource: new kendo.data.DataSource({
                                serverSorting: true,
                                sort: {field: "createdAt", dir: "asc"},
                                transport: {
                                    read: ENV.vApi + "post/readComments/" + post_id,
                                    parameterMap: parameterMap
                                },
                                schema: {
                                    data: "data",
                                    total: "total",
                                    parse: function(res) {
                                        res.data.map(doc => {
                                            doc.time_since = doc.createdAt ? time_since(new Date(doc.createdAt * 1000)) : "";
                                        })
                                        return res;
                                    }
                                }
                            })
                        });
                        var layoutCommentView = new kendo.View(commentTemplate, {model: commentModel, wrap: false});
                        $currentTarget.closest(".media-body").append(layoutCommentView.render());
                        setTimeout(() => {
                            this.createCommentForm(post_id, $currentTarget);
                        }, 1000);
                    }
                    $commentContainer.slideDown();
                    $currentTarget.addClass("shown");
                }
            },
            createCommentForm: function(post_id, $currentTarget) {
                let model = {
                    avatar: ENV.avatar,
                    item: {post_id: post_id},
                    save: function() {
                        var item = this.get("item");
                        $.ajax({
                            url: ENV.vApi + "post/addComment",
                            type: "POST",
                            contentType: "application/json; charset=utf-8",
                            data: JSON.stringify(item),
                            success: (res) => {
                                if(res.status) {
                                    window.commentModel.commentsSource.read().then(
                                        () => pageObservable.createCommentForm(item.post_id, $currentTarget)
                                    );
                                } else notifications.show(res.message, "error");
                            } 
                        })
                    }
                };
                var kendoView = new kendo.View("#comment-form-template", {model: model, wrap: false});
                $currentTarget.closest(".media").find("ul.media-list.push").append(kendoView.render());
            }
        });
        kendo.bind(".mvvm", pageObservable);
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

<script type="text/x-kendo-template" id="select-group-template">
    <div data-role="window" id="select-group-popup" style="padding: 14px 0"
         data-title="@Select@ @group@"
         data-visible="false"
         data-actions="['Close']"
         data-bind="">
        <div class="k-edit-form-container" style="width: 360px">
            <div class="k-edit-field" style="width: 95%">
                <input data-role="dropdownlist"
                data-text-field="name"
                data-value-field="id"
                data-value-primitive="true"
                data-bind="value: group_id, source: groupOption" style="width: 100%">
            </div>
            <div class="k-edit-buttons k-state-default">
                <a class="k-button k-primary k-scheduler-update" data-bind="click: selectThisGroup">@Select@</a>
                <a class="k-button k-scheduler-cancel" href="#" data-bind="click: close">@Cancel@</a>
            </div>
        </div>
    </div>
</script>

<script type="text/x-kendo-template" id="select-custom-template">
    <div data-role="window" id="select-custom-popup" style="padding: 14px 0"
         data-title="@Custom@"
         data-visible="false"
         data-actions="['Close']"
         data-bind="">
        <div class="k-edit-form-container" style="width: 360px">
            <div class="k-edit-field" style="width: 95%">
                <select data-role="multiselect"
                data-text-field="agentname"
                data-value-field="extension"
                data-value-primitive="true"
                data-bind="value: members, source: userOption" style="width: 100%"></select>
            </div>
            <div class="k-edit-buttons k-state-default">
                <a class="k-button k-primary k-scheduler-update" data-bind="click: selectTheseMembers">@Select@</a>
                <a class="k-button k-scheduler-cancel" href="#" data-bind="click: close">@Cancel@</a>
            </div>
        </div>
    </div>
</script>

<script type="text/x-kendo-template" id="post-template">
    <li class="media" data-bind="attr: {data-id: id}">
        <a href="javascript:void(0)" class="pull-left">
            <img data-bind="attr: {src: avatar}" alt="Avatar" class="img-circle">
        </a>
        <div class="media-body">
            <p class="push-bit">
                <span class="text-muted pull-right">
                    <small data-bind="text: time_since"></small>
                    <span class="text-danger" data-role="tooltip" title="#: scopeText #"><i class="#: iconClass #"></i></span>
                </span>
                <strong><a href="javascript:void(0)" data-bind="text: agentname">@Agent@</a> @published@ @a@ @new post@.</strong>
            </p>
            <h4 data-bind="text: title"></h4>
            <p data-bind="text: content"></p>
            <p>
                <a href="javascript:void(0)" class="btn btn-xs btn-success" data-bind="click: likeThisPost" title="#if(typeof likes != 'undefined'){##: likes.join(',') + ' @like@ @this post@' ##}#"><i class="fa fa-thumbs-up"></i> <span data-bind="text: likesCount, visible: likesCount"></span></a>
                <a href="javascript:void(0)" class="btn btn-xs btn-info" data-bind="click: viewComment"><i class="fa fa-pencil"></i>&nbsp;<span data-bind="visible: commentsCount">(<span data-bind="text: commentsCount"></span>)</span> @Comment@</a>
            </p>
        </div>
    </li>
</script>
<script type="text/x-kendo-template" id="comment-template">
    <li class="media">
        <a href="javascript:void(0)" class="pull-left">
            <img data-bind="attr: {src: avatar}" alt="Avatar" class="img-circle">
        </a>
        <div class="media-body">
            <a href="javascript:void(0)"><strong data-bind="text: agentname"></strong></a>
            <span>&nbsp;@write a comment@&nbsp;</span>
            <span class="text-muted"><small><em data-bind="text: time_since"></em></small></span>
            <p data-bind="text: content"></p>
        </div>
    </li>
</script>
<script type="text/x-kendo-template" id="notification-timeline-template">
    <li data-bind="css: {active: unread}">
        <div class="timeline-icon"><i class="#if(typeof icon != 'undefined'){##: icon ##}#"></i></div>
        <div class="timeline-time" data-bind="text: createdAtText"></div>
        <div class="timeline-content">
            <p class="push-bit"><strong data-bind="text: title"></strong></p>
            <span data-bind="html: content, visible: content"></span>
            <div class="text-right">
                <i><small>
                    #if(typeof createdBy != 'undefined'){
                        if(convertExtensionToAgentname[createdBy]){ #
                            #: convertExtensionToAgentname[createdBy] #
                        # } else { #
                            #: createdBy #
                        # }
                    }#
                </small></i>
            </div>
        </div>
    </li>
</script>

<script type="text/x-kendo-template" id="comment-form-template">
    <li class="media">
        <a href="javascript:void(0)" class="pull-left">
            <img data-bind="attr: {src: avatar}" alt="Avatar" class="img-circle">
        </a>
        <div class="media-body">
            <form onsubmit="return false;">
                <textarea data-bind="value: item.content" class="form-control" rows="2" placeholder="@Write@ @something@.."></textarea>
                <button class="btn btn-xs btn-primary" data-bind="click: save"><i class="fa fa-pencil"></i> @Post@ @Comment@</button>
            </form>
        </div>
    </li>
</script>