<style type="text/css">
	.k-chat .k-message-group .k-message .k-message-status {
		opacity: 0;
	}
	.k-chat .k-message-group:not(.k-alt) .k-message.k-state-selected {
		margin-bottom: 0;
	}
	.k-chat .k-message-group.k-alt .k-message.k-last .k-message-status,
	.k-chat .k-message-group.k-alt .k-message.k-only .k-message-status {
		opacity: 1;
		height: 1.2em;
	}
	.preview-avatar {
		height: 70px;
		border: 2px solid lightgray;
		cursor: pointer;
	}
	.k-card .image-attachment {
        max-height: 120px;
    }
    .k-chat .k-message-img {
    	max-height: 100px;
    }
    .emojionearea.emojionearea-standalone {
    	height: 0;
    }
    .emojionearea .emojionearea-editor .emojioneemoji {
    	display: none;
    }
</style>
<!-- Table Styles Header -->
<?php if(empty($only_main_content)) { ?>
<ul class="breadcrumb breadcrumb-top">
    <li>@Tool@</li>
    <li>@Chat internal@</li>
</ul>
<?php } ?>
<div class="container-fluid" id="chat-window">
	<div class="row">
		<!-- Chat Block -->
		<div class="block" style="margin-bottom: 0">
		    <!-- Title -->
		    <div class="block-title">
		        <div class="block-options pull-right">
		            <div class="btn-group btn-group-sm">
		                <a href="javascript:void(0)" class="btn btn-alt btn-sm btn-default dropdown-toggle enable-tooltip" data-toggle="dropdown" title="@Setting@"><i class="fa fa-cog"></i></a>
		                <ul class="dropdown-menu dropdown-custom dropdown-menu-right">
		                	<li data-bind="visible: room_id">
		                        <a href="javascript:void(0)" data-bind="click: togglePinRoom"><i class="gi gi-pushpin text-info"></i></i><span>@Pin@ / @Unpin@</span></a>
		                    </li>
		                    <li>
		                        <a href="javascript:void(0)" data-bind="click: addChatGroup"><i class="gi gi-conversation text-success"></i><span>@Create@ @group@ chat</span></a>
		                    </li>
		                    <li class="divider"></li>
		                    <li>
		                        <a href="javascript:void(0)" data-bind="click: filterChatRoom"><i class="fa fa-filter text-warning"></i><span>@Filter@</span></a>
		                    </li>
		                    <li data-bind="visible: isGroup">
		                        <a href="javascript:void(0)" data-bind="click: editChatGroup"><i class="fa fa-pencil text-warning"></i><span>@Edit@ @group@</span></a>
		                    </li>
		                    <li data-bind="visible: room_id" class="hidden">
		                        <a href="javascript:void(0)" data-bind="click: pokeRoom"><i class="fa fa-bell text-danger"></i><span>@Poke@</span></a>
		                    </li>
		                </ul>
		            </div>
		        </div>
		        <h2>
		        	<i class="fa fa-commenting" data-bind="invisible: chatName, css: {text-success: isConnect}""></i>
		        	<span data-bind="visible: chatName, css: {text-success: isConnect}">
		        		<i class="fa fa-user" data-bind="invisible: isGroup"></i>
		        		<i class="fa fa-users" data-bind="visible: isGroup"></i>
		        		<sup data-bind="visible: isGroup">(<span data-bind="text: membersCount"></span>)</sup>
		        	</span> 
		        	<span data-bind="html: chatName"></span>
		        </h2>
		    </div>
		    <!-- END Title -->

		    <!-- Content -->
		    <div class="chatui-container block-content-full">
		        <!-- People -->
		        <div class="chatui-people themed-background-dark" id="right-chat-menu">
		            <div class="chatui-people-scroll">

		            	<h2 class="chatui-header" data-bind="visible: visibleSearch">
		            		<input class="form-control" data-bind="events: {keyup: searchChange}" style="width: 100%"
		            		placeholder="@Search@...">
		            	</h2>

		            	<!-- Recent -->
		                <h2 class="chatui-header" style="margin: 20px 0 15px">
		                	<a href="javascript:void(0)" data-bind="click: toggleSearch, invisible: invisibleIconSearch">
		                		<i class="fa fa-search text-info pull-right"></i>
		                	</a>
		                	<a href="javascript:void(0)" data-bind="click: toggleRecent">
			                	<strong>
			                		<i class="fa fa-caret-down"></i>
			                		@Recent@
			                	</strong> 
		                	</a>
		                	<span data-bind="visible: filterRecentText">(<span data-bind="text: filterRecentText"></span>)</span>
		                </h2>
		                <div class="list-group" data-template="room-template" data-bind="source: recentChatData">
		                </div>
		                <!-- END Recent -->

		                <!-- Online -->
		                <hr>
		                <h2 class="chatui-header"><i class="fa fa-circle text-success pull-right"></i><strong>@Online@</strong></h2>
		                <div class="list-group" data-template="user-online-template" data-bind="source: userOnlineData">
		                </div>
		                <!-- END Online -->
		            </div>
		        </div>
		        <!-- END People -->

		        <div class="chatui-talk">
		        	<!-- Talk -->
		        		<div id="chat" style="max-width: 100%"></div>
		        	<!-- END Talk -->
		    	</div>
		    </div>
		    <!-- END Content -->
		</div>
		<!-- END Chat Block -->
	</div>
</div>
<div id="create-group-container"></div>
<div class="hidden">
	<input name="file" type="file" id="upload-image"/>
	<input name="file" type="file" id="upload-file"/>
</div>
<style type="text/css">
	#right-chat-menu {
		transition: height 0.25s ease-in;
	}
	#right-chat-menu .list-group .list-group-item {
		padding: 5px 7px;
	}
	.chatui-people .list-group-item .list-group-item-heading {
		line-height: 16px;
	}
	.user-avatar {
		padding: 4px;
		float: left;
	}
	.user-avatar .user-status {
		position: absolute; left: 34px; bottom: 10px;
		border-radius: 16px;
		border: 1px solid white;
	}
	.conversation-metadata {
		float: right;
		height: 40px;
	}
	.conversation-metadata .last-time {
		color: lightgray;
		font-size: 12px;
	}
	.chatui-people .list-group-item .badge {
		margin-top: 3px;
	}
	.chatui-people .chatui-header {
		margin-top: 15px;margin-bottom: 10px;padding-top: 0px;padding-bottom: 0px;padding-right: 10px;padding-left: 10px;
	}
	.rotated-counter-clock {
	    -webkit-transform: rotate(-90deg);  /* Chrome, Safari 3.1+ */
	    -moz-transform: rotate(-90deg);  /* Firefox 3.5-15 */
	    -ms-transform: rotate(-90deg);  /* IE 9 */
	    -o-transform: rotate(-90deg);  /* Opera 10.50-12.00 */
	    transform: rotate(-90deg);  /* Firefox 16+, IE 10+, Opera 12.10+ */
	}
</style>
<script type="text/x-kendo-template" id="room-template">
	# if(data.type == "group") { #
    <a href="javascript:void(0)" data-bind="click: openRoomChat, css: {active: active}, attr: {data-room-id: id}" class="list-group-item">
    	<div class="conversation-metadata">
    		<div class="last-time" data-bind="text: last_time">__:__</div>
    		<span class="badge pull-right" data-bind="text: unread_count, visible: unread_count"></span>
    	</div>
    	<div class="user-avatar">
        	<img data-bind="visible: avatar, attr: {src: avatar}" alt="Avatar" class="img-circle">
        	<img data-bind="invisible: avatar" src="public/proui/img/placeholders/avatars/avatar.jpg" alt="Avatar" class="img-circle">
    	</div>
        <h5 class="list-group-item-heading room-name">
        	<b data-bind="text: name"></b>
        	<i class="gi gi-pushpin text-muted" data-bind="visible: pin"></i>
        </h5>
        <h6 class="list-group-item-heading last-message" data-bind="text: last_message"></h6>
    </a>
    # } else { #
    <a href="javascript:void(0)" data-bind="click: openRoomChat, css: {active: active}, attr: {data-room-id: id}" class="list-group-item">
    	<div class="conversation-metadata">
    		<div class="last-time" data-bind="text: last_time">__:__</div>
    		<span class="badge pull-right" data-bind="text: unread_count, visible: unread_count"></span>
    	</div>
    	<div class="user-avatar">
        	<img data-bind="visible: avatar, attr: {src: avatar}" alt="Avatar" class="img-circle">
        	<img data-bind="invisible: avatar" src="public/proui/img/placeholders/avatars/avatar.jpg" alt="Avatar" class="img-circle">
    	</div>
        <h5 class="list-group-item-heading room-name">
        	<b data-bind="text: extension"></b> (<span data-bind="text: agentname"></span>)
        	<i class="gi gi-pushpin text-muted" data-bind="visible: pin"></i>
        </h5>
        <h6 class="list-group-item-heading last-message" data-bind="text: last_message"></h6>
    </a>
    # } #
</script>
<script type="text/x-kendo-template" id="user-online-template">
    <a href="javascript:void(0)" data-bind="click: createUserRoomChat, attr: {data-user-id: extension}" class="list-group-item">
    	<div class="conversation-metadata">
    		<div class="last-time" data-bind="text: last_time">__:__</div>
    	</div>
    	<div class="user-avatar">
        	<img data-bind="visible: avatar, attr: {src: avatar}" alt="Avatar" class="img-circle">
        	<img data-bind="invisible: avatar" src="public/proui/img/placeholders/avatars/avatar.jpg" alt="Avatar" class="img-circle">
        	<i class="fa fa-circle user-status # if(data.chat_statuscode){# text-success # }else{ # text-warning # } #"></i>
    	</div>
        <h5 class="list-group-item-heading room-name" style="line-height: 32px"><b data-bind="text: extension">Unknown</b> (<span data-bind="text: agentname"></span>)</h5>
    </a>
</script>
<script>

	var Config = {
		defaultIconUrl: "public/proui/img/placeholders/avatars/avatar.jpg"
	};

	window.onload = function() {
		ReadyChat.init();
		/* Socket.io */
		var webSocketURL = ENV.baseUrl;
		var socket = window.socket = io(webSocketURL);
		socket.on('connect', function(){
			notification.show("Đã mở kết nối chat", "success");
			chatWindowObservable.set("isConnect", true);
			socket.emit('start session', ENV.extension);
		});

		socket.on('typing', (msg) => {
			var chatUI = $("#chat").data("kendoMyChat");
			if(!chatUI) return;

			if (msg.indexOf("error") > 0) {
	            console.log("Error: " + res + "\r\n");
	        } else {
	        	var data = JSON.parse(msg);
	        	if(data.user_id == ENV.extension) return;

	        	var user = {
	        		id: data.user_id,
	        		name: data.name
	        	};
	        	if(data.action == "start")
	        		chatUI.renderUserTypingIndicator(user);
	        	else chatUI.clearUserTypingIndicator(user);
	        }
		});

		socket.on('typing all', (msg) => {
			if (msg.indexOf("error") > 0) {
	            console.log("Error: " + res + "\r\n");
	        } else {
	        	var data = JSON.parse(msg);
	        	if(data.user_id == ENV.extension) return;
	        	$lastMessage = $(`.list-group a.list-group-item[data-room-id='${data.room_id}'] .last-message`);
	        	if(data.action == "start")
	        		$lastMessage.data("last-message", $lastMessage.html()).html(`<i class="animation-pulse">${data.name} is typing ...</i>`);
	        	else {
	        		$lastMessage.data("last-message") ? $lastMessage.html($lastMessage.data("last-message")) : void 0;
	        	}
	        }
		});

		socket.on('chat message', (msg) => {
			var chatUI = $("#chat").data("kendoMyChat");

			if(!chatUI) return;

		  	if (msg.indexOf("error") > 0) {
	            console.log("Error: " + res + "\r\n");
	        } else {
	        	var data = JSON.parse(msg);
	        	if(data.from.id != chatUI.getUser().id) {
	        		if(!data.from.iconUrl) {
	        			data.from.iconUrl = Config.defaultIconUrl;
	        		}
		        	chatUI.renderMessage(data, data.from);
			    } else {
			    	if(data.status && data.uid) {
		        		$("#" + data.uid).find(".k-message-status").html(`<i class="fa fa-check-circle text-success"></i>`);
				    }
			    }
	        }
		});

		socket.on('new message', (msg) => {
			if (msg.indexOf("error") > 0) {
	            console.log("Error: " + res + "\r\n");
	        } else {
	        	var data = JSON.parse(msg);
	        	if(data.to.indexOf(ENV.extension) > -1) {
	        		chatWindowObservable.recentChatData.read();
	        		if(data.user_id != ENV.extension) {
		        		$.ajax({
		        			url: ENV.vApi + "chat/readMessageId",
		        			type: "POST",
		        			contentType: "application/json; charset=utf-8",
							data: JSON.stringify(data)
		        		})
	        		}
	        	}
	        }
		});

		socket.on('disconnect', function(closeEvent){
			chatWindowObservable.set("isConnect", false);
	  		console.log(closeEvent);
	  	});

	  	socket.on('error', function(exception) {
		  	console.log('SOCKET ERROR');
		  	console.log(exception);
		  	chatWindowObservable.set("isConnect", false);
		  	socket.destroy();
		});

		var chatWindowObservable = window.chatWindowObservable = kendo.observable({
			chatName: "",
			agentName: "",
			toggleSearch: function(e) {
				this.set("visibleSearch", !this.get("visibleSearch"));
			},
			toggleRecent: function(e) {
				$caretIcon = $(e.currentTarget).find(".fa");
				$caretIcon.toggleClass("rotated-counter-clock");
				$("div[data-template=room-template]").slideToggle( "slow" );
				var windowW = window.innerWidth
                    || document.documentElement.clientWidth
                    || document.body.clientWidth;
                if (windowW < 768) {
                	if($caretIcon.hasClass("rotated-counter-clock")) {
                		$("#right-chat-menu").css('height', 50);
                		$('.chatui-talk').css('height', 450);
                		this.set("invisibleIconSearch", true);
                		this.set("visibleSearch", false);
	                } else {
	                	$("#right-chat-menu").css('height', 250);
                		$('.chatui-talk').css('height', 250);
                		this.set("invisibleIconSearch", false);
	                }
                }
			},
			searchChange: function(e) {
				var value = e.currentTarget.value;
				this.recentChatData.filter({
					logic: "or",
					filters: [
						{field: "name", operator: "contains", value: value},
						{field: "members", operator: "contains", value: value}
					]
				});
				this.userOnlineData.filter({
					logic: "or",
					filters: [
						{field: "extension", operator: "contains", value: value},
						{field: "agentname", operator: "contains", value: value}
					]
				})
			},
			togglePinRoom: function(e) {
				var room = this.recentChatData.get(this.get("room_id")),
					operation = room.pin ? "@Unpin@" : "@Pin@";
				swal({
                    title: operation + " @this group@.",
                    text: `@Are you sure@?`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then((sure) => {
                    if(sure) {
                        $.ajax({
							url: ENV.vApi + "chat/pinRoom/" + this.get("room_id"),
							type: "POST",
							contentType: "application/json; charset=utf-8",
							data: JSON.stringify({pin: !room.pin}),
							success: (res) => {
								if(res.status) {
									chatWindowObservable.recentChatData.read();
									notification.show(operation + " @group@ @success@", "success");
								} else notification.show(res.message, "error");
							}
						});
                    }
                })
			},
			addChatGroup: function(e) {
				if($("#create-group-popup").data("kendoWindow")) {
                    $("#create-group-popup").data("kendoWindow").destroy();
                }
                var members = [];
            	for(let ext in convertExtensionToAgentname) {
            		members.push({value: ext, text: `${ext} (${convertExtensionToAgentname[ext]})`});
            	}
                var model = {
                    item: {type: "group"},
                    memberOption: members,
                    uploadAvatar: function() {
						$("#upload-avatar").click();
					},
					uploadSuccessAvatar: function(e) {
						notification.show(e.response.message, e.response.status ? "success" : "error");
		  				e.sender.clearAllFiles();
		  				if(e.response.filepath) {
		  					this.set("item.avatar", e.response.filepath);
		  				}
					},
                    close: function(e) {
                        $popup = $("#create-group-popup").data("kendoWindow"), 
                        $popup.close(),
                        $popup.destroy();
                    },
                    save: function(e) {
                    	var data = this.item.toJSON();
                    	if(!data.members || (data.members.length < 3)) {
                    		notification.show("@Your data is invalid@", "error");
                    		return;
                    	}
                    	$.ajax({
							url: ENV.vApi + "chat/createRoom",
							type: "POST",
							contentType: "application/json; charset=utf-8",
							data: JSON.stringify(data),
							success: (res) => {
								if(res.status) {
									chatWindowObservable.recentChatData.read();
									notification.show("@Create@ @group@ @success@", "success");
									this.close();
								} else notification.show(res.message, "error");
							}
						});
                    }
                };
				var kendoView = new kendo.View("create-group-template", {model: model, wrap: false});
                kendoView.render("#create-group-container");
                $("#create-group-popup").data("kendoWindow").center().open();
			},
			editChatGroup: function(e) {
				var room = this.recentChatData.get(this.get("room_id"));
				if($("#create-group-popup").data("kendoWindow")) {
                    $("#create-group-popup").data("kendoWindow").destroy();
                }
                var members = [];
            	for(let ext in convertExtensionToAgentname) {
            		members.push({value: ext, text: `${ext} (${convertExtensionToAgentname[ext]})`});
            	}
                var model = {
                	room_id: room.id,
                    item: {type: "group", name: room.name, members: room.members, avatar: room.avatar},
                    memberOption: members,
                    uploadAvatar: function() {
						$("#upload-avatar").click();
					},
					uploadSuccessAvatar: function(e) {
						notification.show(e.response.message, e.response.status ? "success" : "error");
		  				e.sender.clearAllFiles();
		  				if(e.response.filepath) {
		  					this.set("item.avatar", e.response.filepath);
		  				}
					},
                    close: function(e) {
                    	$popup = $("#create-group-popup").data("kendoWindow"), 
                        $popup.close(),
                        $popup.destroy();
                    },
                    save: function(e) {
                    	$.ajax({
							url: ENV.vApi + "chat/editRoom/" + this.get("room_id"),
							type: "POST",
							contentType: "application/json; charset=utf-8",
							data: JSON.stringify(this.item.toJSON()),
							success: (res) => {
								if(res.status) {
									chatWindowObservable.recentChatData.read();
									notification.show("@Create@ @group@ @success@", "success");
									this.close();
								} else notification.show(res.message, "error");
							}
						});
                    }
                };
				var kendoView = new kendo.View("create-group-template", {model: model, wrap: false});
                kendoView.render("#create-group-container");
                $("#create-group-popup").data("kendoWindow").title("@Edit@ @group@").center().open();
			},
			filterChatRoom: function(e) {
				var buttons = {
					private: "Chat @private@",
					group: "Chat @group@",
					all: "@All@",
					cancel: true
				};
				swal({
                    title: "@Filter@ Chat @Recent@.",
                    icon: "info",
                    buttons: buttons
                }).then(type => {
                    if(type !== null && type !== false) {
                        if(type == "all") {
                        	this.recentChatData.filter({});
                        	this.set("filterRecentText", "");
                        } else {
                        	this.recentChatData.filter({field: "type", operator: "eq", value: type});
                        	this.set("filterRecentText", type == "group" ? "@Group@" : "@Private@");
                        }
                    }
                })
			},
			pokeRoom: function(e) {

			},
			userOnlineData: new kendo.data.DataSource({
				serverFiltering: true,
				transport: {
					read: ENV.vApi + "chat/users",
					parameterMap: parameterMap
				},
				schema: {
					data: "data",
					total: "total",
					parse: function(res) {
						res.data = res.data.filter(doc => doc.extension != ENV.extension);
						return res;
					}
				}
			}),
			recentChatData: new kendo.data.DataSource({
				pageSize: 5,
				serverPaging: true,
				serverFiltering: true,
				serverSorting: true,
				filter: {field: "members", operator: "eq", value: ENV.extension},
				sort: [{field: "pin", dir: "desc"}, {field: "last_time", dir: "desc"}, {field: "updatedAt", dir: "desc"}],
				transport: {
					read: ENV.vApi + "chat/rooms",
					parameterMap: parameterMap
				},
				schema: {
					data: "data",
					total: "total",
					parse: function(res) {
						res.data.map(doc => {
							doc.active = Boolean(doc.id == chatWindowObservable.get("room_id"));
							if(doc.type == "private") {
								doc.members.forEach(ext => {
									if(ext != ENV.extension) {
										doc.extension = ext;
										doc.agentname = convertExtensionToAgentname[ext];
										doc.avatar = ENV.vApi + "avatar/agent/" + ext;
									}
								})
							}
						});
						return res;
					}
				}
			}),
			messageDataSource: new kendo.data.DataSource({
				pageSize: 20,
				serverPaging: true,
				serverSorting: true,
				sort: {field: "time", dir: "desc"},
                transport: {
                    read: ENV.vApi + "chat/readMessage",
                    parameterMap: function(options, operation) {
                    	options.room_id = chatWindowObservable.get("room_id");
					    return {q: kendo.stringify(options)};
					}
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(res) {
                    	res.data.forEach(doc => {
                    		if(!doc.from.iconUrl) {
			        			doc.from.iconUrl = Config.defaultIconUrl;
			        		}
			        		doc.status = '<i class="fa fa-check-circle text-success"></i>';
			        		chatUI.renderMessage(doc, doc.from, 0);
                    	});
                    	window.chatWindowScrollable = true;
                    	return res;
                    }
                }
            }),
            createUserRoomChat: function(e) {
				var name = $(e.currentTarget).find(".room-name").html();
				this.set("chatName", name);
				this.openRoomChatAsync(e);
				this.recentChatData.read();
			},
			openRoomChat: function(e) {
				var name = $(e.currentTarget).find(".room-name").html();
				this.set("chatName", name);
				this.openRoomChatAsync(e);
				$("#right-chat-menu .list-group-item").removeClass("active");
				$(e.currentTarget).addClass("active");
				var id = $(e.currentTarget).data("room-id"),
					room = this.recentChatData.get(id);
				this.set("isGroup", Boolean(room.type == "group"));
				this.set("membersCount", room.members.length)
			},
			openRoomChatAsync: async function(e) {
				var $chat = $("#chat");
				if($chat.data("kendoMyChat")) {
					$chat.data("kendoMyChat").destroy();
					$chat.empty();
				}
				var room_id = $(e.currentTarget).data("room-id");
				if(!room_id) {
					var user_id = $(e.currentTarget).data("user-id").toString();
					var response = await $.ajax({
						url: ENV.vApi + "chat/createRoom",
						type: "POST",
						contentType: "application/json; charset=utf-8",
						data: JSON.stringify({members: [ENV.extension, user_id], type: "private"}),
					});
					if(response.status)
						room_id = response.data.id;
				}
				this.set("room_id", room_id);

				socket.emit('join_room', room_id);

			  	/* Chat UI */
				var chatUI = window.chatUI = $chat.kendoMyChat({
					user: {
						iconUrl: ENV.avatar,
						name: ENV.agentname
					},
				    messages: {
				        placeholder: "@Type a message here@..."
				    },
				    post: function (args) {
				    	args.uid = window.kendo.guid();
				    	args.status = `<i class="fa fa-spinner fa-pulse"></i>`;
				    	args.sender.renderMessage(args, args.from);
		                socket.emit('chat message', JSON.stringify({type: args.type, text: args.text, from: args.from, time: args.timestamp, user_id: ENV.extension, room_id: room_id, uid: args.uid, org_type: ENV.type}));
		            },
		            typingStart: function(e) {
		            	socket.emit('typing', JSON.stringify({action: "start", user_id: ENV.extension, name: ENV.agentname, room_id: chatWindowObservable.get("room_id")}));
		            },
		            typingEnd: function(e) {
		            	socket.emit('typing', JSON.stringify({action: "end", user_id: ENV.extension, name: ENV.agentname, room_id: chatWindowObservable.get("room_id")}));
		            },
		            toolClick: function (ev) {
		            	switch(ev.name) {
		            		case "sendimage":
		            			$("#upload-image").click();
		            			break;
		            		case "sendfile":
		            			$("#upload-file").click();
		            			break;
		            		default: break;
		            	}
	                },
	                toolbar: {
	                    toggleable: true,
	                    buttons: [
	                        { name: "sendimage", iconClass: "k-icon k-i-image", text: "@Upload@ @image@" },
	                        { name: "sendfile", iconClass: "k-icon k-i-file", text: "@Upload@ @file@" }
	                    ]
	                }
				}).data("kendoMyChat");

				this.messageDataSource.page(1);
				setTimeout(() => chatUI.scrollToBottom(), 1000);

				$messageList = $chat.find(".k-message-list");

				this.currentScrollTop = 0;

				$messageList.on("scroll", () => {
					if($messageList.scrollTop() < 10 && $messageList.scrollTop() < this.currentScrollTop) {
						if(window.chatWindowScrollable) {
							if(this.messageDataSource.pageSize() * this.messageDataSource.page() < this.messageDataSource.total()) {
								window.chatWindowScrollable = false;
								this.messageDataSource.page(this.messageDataSource.page() + 1);
								$messageList.scrollTop(20);
							} else {
								chatUI.renderSuggestedActions([{
							        title: "@First of conversation@. @Scroll to bottom@.",
							        value: ""
							    }], 0);
							}
						}
					}
					this.currentScrollTop = $messageList.scrollTop();
				});

				chatUI.messageBox.input.after('<input id="emoji-btn" style="width: 0">');

				$("#emoji-btn").emojioneArea({
					standalone: true,
					buttonTitle: "@Use the TAB key to insert emoji faster@",
					searchPosition: "bottom",
					events: {
						change: function (editor, event) {
							chatUI.messageBox.input.val($(".k-chat .k-message-box .k-input").val() + $("#emoji-btn").val());
							$("#emoji-btn").val("");
							chatUI.messageBox.input.focus();
					    }
					}
				});
			}
		});
		kendo.bind($("#chat-window"), chatWindowObservable);
		renderAfter();
		timeOutCheckOnline();
		function timeOutCheckOnline() {
			chatWindowObservable.userOnlineData.read();
			setTimeout(timeOutCheckOnline, 30000);
		}
	};
</script>
<script type="text/x-kendo-template" id="create-group-template">
    <div data-role="window" id="create-group-popup" style="padding: 14px 0"
         data-title="@Create@ @group@"
         data-visible="false"
         data-actions="['Close']"
         data-bind="">
        <div class="k-edit-form-container" style="width: 500px">
            <div class="k-edit-label" style="width: 20%">
                <label>@Group name@</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <input class="k-textbox" data-bind="value: item.name" style="width: 100%">
            </div>
            <div class="k-edit-label" style="width: 20%">
                <label>@Members@</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <select style="width: 100%"
                data-role="multiselect"
                data-value-primitive="true"
                data-value-field="value" data-text-field="text" 
                data-bind="value: item.members, source: memberOption"></select>
            </div>
            <div class="k-edit-label" style="width: 20%">
                <label>@Avatar@</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <img src="<?= PROUI_PATH ?>img/placeholders/avatars/avatar.jpg" data-bind="invisible: item.avatar, click: uploadAvatar" class="preview-avatar img-circle">
			    <img data-bind="attr: {src: item.avatar}, visible: item.avatar, click: uploadAvatar" class="preview-avatar img-circle">
			    <div style="display: none">
		        	<input name="file" type="file" id="upload-avatar"
                   data-role="upload"
                   data-multiple="false"
                   data-async="{ saveUrl: 'api/v1/upload/avatar/chatgroup', autoUpload: true }"
                   data-bind="events: { success: uploadSuccessAvatar }"/>
		        </div>
            </div>
            <div class="k-edit-buttons k-state-default">
                <a class="k-button k-primary k-scheduler-update" data-bind="click: save">@Save@</a>
                <a class="k-button k-scheduler-cancel" data-bind="click: close">@Cancel@</a>
            </div>
        </div>
    </div>
</script>

<script type="text/javascript">
	function renderAfter() {
		var upload = $("#upload-image").kendoUpload({
	        async: {
	            saveUrl: "api/v1/upload/avatar/chatimg",
	            autoUpload: true
	        },
	        validation: {
	            allowedExtensions: [".jpg", ".jpeg", ".png", ".bmp", ".gif"]
	        },
	        success: onSuccess,
	        showFileList: false,
	        dropZone: "#chat"
	    }).data("kendoUpload");

	    function onSuccess(e) {
	        if (e.operation === "upload") {
	        	notification.show(e.response.message, e.response.status ? "success" : "error");
  				e.sender.clearAllFiles();
  				if(e.response.filepath) {
  					var msg = {
  						type: "img", 
  						url: e.response.filepath, 
  						from: chatUI.getUser(), 
  						time: new Date, 
  						user_id: ENV.extension, 
  						room_id: window.chatWindowObservable.get("room_id"), 
  						uid: window.kendo.guid()
  					};
  					chatUI.renderMessage(msg, chatUI.getUser());
  					socket.emit("chat message", JSON.stringify(msg));
  				}
	        }
	    }

	    var uploadFile = $("#upload-file").kendoUpload({
	        async: {
	            saveUrl: "api/v1/upload/file/chatattachment",
	            autoUpload: true
	        },
	        success: function (e) {
		        if (e.operation === "upload") {
		        	notification.show(e.response.message, e.response.status ? "success" : "error");
	  				e.sender.clearAllFiles();
	  				if(e.response.filepath) {
	  					var msg = {
	  						type: "file", 
	  						text: e.response.filename,
	  						size: e.response.size,
	  						url: e.response.filepath, 
	  						from: chatUI.getUser(), 
	  						time: new Date, 
	  						user_id: ENV.extension, 
	  						room_id: window.chatWindowObservable.get("room_id"), 
	  						uid: window.kendo.guid()
	  					};
	  					chatUI.renderMessage(msg, chatUI.getUser());
	  					socket.emit("chat message", JSON.stringify(msg));
	  				}
		        }
		    },
	        showFileList: false
	    }).data("kendoUpload");

	    var IMAGE_CARD_TEMPLATE = kendo.template(
	        '<div class="k-card k-card-type-rich">' +
	        '<div class="k-card-body quoteCard">' +
	        '<img class="image-attachment" src="#: image #" />' +
	        '</div>' +
	        '</div>'
	    );

	    kendo.chat.registerTemplate("image_card", IMAGE_CARD_TEMPLATE);
    }

    function zoomImgChat(ele) {
    	$(ele).magnificPopup({type:"image", image: {titleSrc:"title"}});
    }
</script>