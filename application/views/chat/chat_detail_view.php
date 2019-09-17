
<div class="chats">
    <div id="content_chat">
        <!-- Oanh 21/01/2019 11:34AM Push to hot data -->
        <div id="copy-doan-hoi-thoai" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Modal Header</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <form id="push-to-hot-data-form" method="POST">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Họ và tên <span class="text-danger">*</span></label>
                                        <div class="col-md-8">
                                            <input v-model="conversation.nameRoom" required id="hot_data_name" name="name" type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Số điện thoại <span class="text-danger">*</span></label>
                                        <div class="col-md-8">
                                            <input required id="hot_data_phone" name="phone" type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Email</label>
                                        <div class="col-md-8">
                                            <input id="hot_data_email" name="email" type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Agent <span class="text-danger">*</span></label>
                                        <div class="col-md-8">
                                            <input required id="hot_data_push_agent" name="push_agent" type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Source <span class="text-danger">*</span></label>
                                        <div class="col-md-8">
                                            <input required id="hot_data_source" name="source" type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <label class="col-md-12 control-label">Nội dung cần tư vấn</label>
                                        <div class="col-md-12">
                                            <textarea v-model="pthdt_content_consulting" id="hot_data_note" name="note" rows="7" class="form-control"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="push-to-hot-data-form" class="btn btn-primary">Lưu</button>
                        <button type="button" style="background-color: #ffffff!important; color: #000000; border-color: #000000" class="btn btn-light" data-dismiss="modal">Thoát</button>
                    </div>
                </div>

            </div>
        </div>
        <!-- END Oanh 21/01/2019 11:34AM Push to hot data -->

        <div class="infomation" style="padding: 13px 0;background-color: #FFF;font-size: 13px;display: none;">
        </div>
        <div class="full-chat-w">
            <div class="full-chat-i">
                <div class="full-chat-left" style="display: none;">
                    <div v-if="show_search_form" class="chat-search" >
                        <div  class="element-search">
                            <input class="search-input" v-model="input_search" placeholder="Tìm kiếm" type="text">
                            <i v-if="!input_search" class="fa fa-search"></i>
                            <i v-if="input_search && !search_loading" v-on:click="show_search" class="fa fa-times-circle"></i>
                            <i v-if="search_loading" class="fa fa-spinner fa-spin"></i>
                        </div>
                    </div>
                    <!--  -->
                    <div class="convo-action">
                        <div class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            <div class="view-filter">{{filter_room_text}}</div>
                            <i class="fa fa-angle-down"></i>
                        </div>
                        <div class="dropdown-menu notify-dropdown" role="menu">
                            <div class="ui-filter-view">
                                <div>
                                    <div class="body">
                                        <div class="search">
                                        </div>
                                        <ul class="nav-list full-width">
                                            <li v-on:click="getRooms('all')" class="active">
                                                <a class="filter-link active">
                                                    <div class="link">All
                                                        <span class="count db" aria-hidden="true" data-toggle="tooltip" data-placement="left">{{rooms.length}}</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li v-on:click="getRooms('message')">
                                                <a class="filter-link">
                                                    <div class="link">Messages</div>
                                                </a>
                                            </li>
                                            <li v-on:click="getRooms('comment')">
                                                <a class="filter-link">
                                                    <div class="link">Comments</div>
                                                </a>
                                            </li>
                                            <li v-on:click="getRooms('resolved')">
                                                <a class="filter-link">
                                                    <div class="link">Resolved</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--  -->
                    <div class="tab-content">
                        <div id="tab-chat" class="tab-pane fade in active">
                            <div class="user-list scroll1">
                                <!--{{rooms}}--> 
                                <div v-for="room in rooms" class="user-w singer-user" :class="[{active:current_room==room.room_id},{unread:room.unread==1}]"  v-on:click="getConversation(room.room_id)">
                                    <!--{{room}}-->
                                    <span v-if="room.unread==1" class="new-mess">New</span>
                                    <div class="avatar with-status status-green">
                                        <img :alt="room.page_name" :title="room.page_name" :src="room.avatar">
                                        <!--{{room.labels}}-->
                                        <span v-if="room.labels !== null" v-for="label in room.labels" style="width: 10px; height: 10px; margin-right: 3px" v-bind:style="{backgroundColor: label.mausac}" class="dot" v-bind:title="label.tennhan"></span>
                                    </div>
                                    <div class="user-info">
                                        <div class="user-date">{{room.date_active}}</div>
                                        <div class="user-name">{{room.group_name}}</div>
                                        <div class="last-message">{{room.last_mes}}</div>
                                        <div class="line-message">
                                            <!--<span v-if="room.transfer_from">transfer từ {{room.transfer_from}} |</span>-->
                                            <span v-if="room_status == 1">
                                                <i class="fa fa-user-plus" v-on:click="popup_transfer_modal"></i></br>
                                                <img v-for="icon in room.icons" :src="icon"/>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-search" class="tab-pane fade">
                            <div class="user-list scroll1 history-result">
                                <!-- {{search_item}}     -->
                                <div v-for="search in search_item" class="user-w search-item" v-on:click="getConversation(search.room_id, search.page, search.id)">
                                    <div class="avatar with-status status-green">
                                        <img :alt="search.page_name" :title="search.page_name" :src="search.avatar">
                                    </div>
                                    <div class="user-info">
                                        <div class="user-date">{{search.date_added}}</div>
                                        <div class="user-name">{{search.group_name}}</div>
                                        <div class="last-message">{{search.text}}</div>
                                        <div class="line-message">{{search.page_name}} <span><img :src="search.icon"/></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-contact" class="tab-pane fade ">
                            <div class="user-list scroll1">
                                <?php foreach ($danhbas as $key => $danhba): ?>
                                    <div class="user-w group-user" data-key="<?php echo $key; ?>">
                                        <div class="user-info">
                                            <div class="user-name"><i class="fa fa-caret-right"></i>
                                                <?php echo $danhba['name']; ?></div>
                                        </div>
                                    </div>
                                    <?php foreach ($danhba['user_array'] as $agent): ?>
                                        <div class="user-w group-user-item " data-user-id="<?php echo $agent['username']; ?>" data-user-name="<?php echo $agent['name']; ?>" data-user-type="username" style="display: none;">
                                            <div class="user-info">
                                                <div class="user-name"><i class="fa fa-circle icon-offline"></i> <span><?php echo $agent['name']; ?> (<?php echo $agent['username']; ?>)</span></div>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div><!--./tab-content-->
                </div>
                <div class="full-chat-middle ">
                    <div class="chatbox chatbox" data-next="0" data-pre="0">
                        <div class="chat-head" >
                            <div class="user-info">
                                <span><i class="fa fa-star-o" style=" margin-top: 3px; font-size: 18px;"></i></span>
                                <a class="name-room">{{conversation.nameRoom}}</a></br>
                                <span style="color:#ccc;" v-if="conversation.read_by_id"  class="new-mess"><i>Đã xem bởi {{conversation.read_by_id}} lúc  {{conversation.read_by_time}} </i></span>
                            </div>
                            <div class="user-actions" v-if="current_room" >
                                <a  v-if="conversation.source=='facebook'" v-on:click="popup_modal_info(conversation.page_id,conversation.messages)"  class="btn-search-single" title="Thông tin phòng chat"><i class="fa fa-info-circle"></i></a>
                                <!-- <a v-on:click="unread(unread_stt)"  title="Tùy chỉnh"><i class="fa fa-eye" id="btn-unread" style="color:#ccc"></i></a> -->
                                <!-- Oanh 21/01/2019 11:23AM Push to hot data -->
                                <!-- <div id="btn-group-push-to-hot-data" class="btn-group">
                                    <a id="btn-push-to-hot-data" title="Push to hot data" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-paper-plane"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a @click="openPushToHotDataModal('part', current_room)" href="#" data-toggle="modal" data-target="#copy-doan-hoi-thoai">Copy đoạn hội thoại</a></li>
                                        <li><a @click="openPushToHotDataModal('all', current_room)" href="#">Copy tất cả nội dung hội thoại</a></li>
                                    </ul>
                                </div> -->
                                <!-- END Oanh 21/01/2019 11:23AM Push to hot data -->
                                <!-- <a v-on:click="popup_ticket_modal" title="Ticket"><i class="fa fa-ticket" aria-hidden="true"></i></a> -->
                                <!-- <a v-on:click="show_search" class="btn-search-single" title="Tìm kiếm"><i class="fa fa-search"></i></a> -->

                                <!-- <a v-if="room_status == 1" class="btn-close-room" v-on:click="resolve_room" title="Đóng phiên"><i class="fa fa-times"></i></a> -->
                            </div>
                        </div>

                        <div class="chat-content-w scroll1">
                            <div class="chat-content" ref="list" v-if="current_room">

                                <div v-for="message in conversation.messages" class="chat-message" :class="{self:message.sender_id==username}" :ref="'mes_id'+message.id">
                                    <div class="user-name" v-if="message.sender_id!=username">
                                        {{message.name}} 
                                    </div>
                                    <div class="chat-message-content-w">
                                        <div class="chat-message-content" v-if="message.type=='text'" >
                                            <span v-html="message.text"></span>
                                            <div v-if="conversation.trigger=='comment' && message.sender_id!=username" class="chat-message-action">
                                                <div v-if="message.comment_trash">
                                                    <i class="far fa-thumbs-up disabled"></i>
                                                    <i class="fa fa-eye-slash disabled"></i>
                                                    <a :href="message.details.post_url" target="_blank"><i class="fa fa-link disabled" data-action="view"></i></a>
                                                    <i class="far fa-trash disabled" ></i>
                                                </div>
                                                <div v-else>
                                                    <i v-on:click="comment_action($event, message.id, 'like')" class="fa fa-thumbs-up " :class="{active:message.comment_like}" ></i>
                                                    <i v-on:click="comment_action($event, message.id, 'hide')" class="fa fa-eye-slash" :class="{active:message.comment_hide}" ></i>
                                                    <a :href="message.details.post_url" target="_blank"><i class="fa fa-link " data-action="view"></i></a>
                                                    <i v-on:click="comment_action($event, message.id, 'trash')" class="fa fa-trash" :class="{active:message.comment_trash}" ></i>
                                                </div>
                                            </div>
                                            <svg v-if="message.sended" id="Layer_1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 15" width="16" height="12"><path fill="#92A58C" d="M10.91 3.316l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"></path></svg>
                                        </div>
                                        <div class="chat-message-content" v-else-if="message.type=='image'"><a :href="message.url" target="_blank"><img style="width: 200px;" :src="message.url" :alt="message.text" :title="message.text"></a></div>
                                        <div class="chat-message-content" v-else-if="message.type=='file'"><a :href="message.url" target="_blank">{{message.text ? message.text : message.url}}</a></div>
                                        <div class="chat-message-content" v-else-if="message.type=='link'"><a :href="message.url" target="_blank">{{message.text ? message.text : message.url}}</a></div>
                                    </div>
                                    <div class="chat-message-avatar">
                                        <img v-if="message.sender_id==username" :alt="message.name" :title="message.name" :src="profile_pic">
                                        <img v-else-if="message.sender_id==conversation.page_id && message.profile_pic" :alt="message.name" :title="message.name" :src="message.profile_pic">
                                        <img v-else :alt="message.name" :title="message.name" :src="current_user_avatar">
                                    </div>
                                    <div class="chat-message-date" :data-time="message.timestamp">{{message.date}}</div>
                                </div>
                                <div class="loading-message" style=" text-align: right; margin: -20px 50px 0 0; display: none; "><img src="../assets/images/loading_mess.svg" alt=""></div>
                            </div>
                        </div>
                        <button v-if="page_bottom>0" v-on:click="loadBottom" type="button" class="btn-load-bottom"><i class="fas fa-angle-down"></i></button>
                        <div class="box-searh-single">
                            <span>tìm kiếm</span> <input type="text" name=""> <a title="Hủy tìm kiếm" class="btn-remove-box-search"><i class="fa fa-times-circle-o "></i></a>
                        </div>
<!--                        <div class="chat-controls" v-if="current_room">-->
<!--                            <div class="chat-input"><textarea v-model="input_message" placeholder="Nhập 1 tin nhắn..."></textarea></div>-->
<!--                            <div class="chat-input-extra ">-->
<!--                                <div class="chat-extra-actions ">-->
<!--                                    <a class="btn-upload" v-on:click="upload_file"><i class="fa fa-image"></i></a>-->
<!--                                    <a v-on:click="openCloseLabel()"><i class="fa fa-tag"></i></a>-->
<!--                                    <div class="box-message-template" v-if="show_labels">-->
<!--                                        <ul class="list-group"-->
<!--                                            style=" width: 300px;box-shadow: 0px 0px 1px #E0E4EA;">-->
<!--                                            <li style="cursor: pointer" v-for='label in listLabels' class="list-group-item"-->
<!--                                                v-on:click="updateLabels(label)"><span v-bind:style="{backgroundColor: label.mausac}" class="dot"></span>-->
<!--                                                {{label.tennhan}}-->
<!--                                            </li>-->
<!--                                        </ul>-->
<!--                                    </div>-->
<!--                                    <a class="btn-show-mess" v-on:click="message_template_click('')"><i-->
<!--                                            class="fa fa-comment"></i></a>-->
<!--                                    <div class="box-message-template" v-if="show_message_template">-->
<!--                                        <ul class="list-group"-->
<!--                                            style=" width: 300px;box-shadow: 0px 0px 1px #E0E4EA;">-->
<!--                                            <li style="cursor: pointer" v-for='quickChat in quickChatList' class="list-group-item"-->
<!--                                                v-on:click="message_template_click(quickChat.maucau)">-->
<!--                                                {{quickChat.maucau}}-->
<!--                                            </li>-->
<!--                                        </ul>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                                <div class="chat-btn btn btn-primary btn-sm" :class="{disabled:!input_message}" v-on:click="btn_chat">Gửi<i></i></div>-->
<!--                            </div>-->
<!--                        </div>-->
                    </div>
                </div>

            </div>

        </div>

        <div id="add-to-group" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <form id="form-add-chat" action="" method="post">
                    <div class="modal-content">
                    </div>
                </form>
            </div>
        </div>
        <!--Model transfer 15012019-->
        <div role="dialog" class=" modal fade " id="transfer-model">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <form class="ui-form">
                        <div class="modal-header">
                            <span class="modal-title">Chuyển cuộc trò truyện</span>
                            <button v-on:click="dismiss_transfer_modal" class="close" aria-label="Close" type="button">
                                <span><i class="fa fa-times"></i></span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="ui-field form-field-required">
                                <label for="groupDescription" class=" control-label">Chuyển cho</label>
                                <div>
                                    <select id="transfer_user"  class="form-control" v-on:click="getAssigns()"  >
                                        <option v-if="assigns.supervisor==extension" v-for="assign in assigns.agents" :value="assign">
                                            {{assign}}
                                        </option>
                                        <option v-if="assigns.supervisor!=extension"  :value="assigns.supervisor">
                                            {{assigns.supervisor}}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="ui-field form-field-required" >
                                <label class=" control-label">Lời nhắn</label>
                                <div>
                                    <textarea v-model="transfer_message" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <span class="action-icons">
                                <button type="button" class="btn-default btn" v-on:click="dismiss_transfer_modal"><span>Cancel</span></button>
                                <button type="button" class="btn-primary btn"
                                        v-on:click="transfer_user"><span>transfer</span></button>
                            </span>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <!--End modal transfer 15012019-->
        <!--Model filter Tram 15012019-->
        <div role="dialog" class=" modal fade " id="filter-model">
            <div class="modal-dialog modal-md" style="width:300px">
                <div class="modal-content" >
                    <form method="post" id="form-filter" class="form-horizontal" style="padding: 10px 0">
                        <div class="modal-header" style="border-bottom: 0px solid #eeeeee;">
                            <button v-on:click="dismiss_filter_modal" class="close" aria-label="Close" type="button">
                                <span><i class="fa fa-times"></i></span>
                            </button>
                        </div>
                        <div class="modal-header">
                            <label class="container">Tất cả
                                <input type="checkbox" id="checkall">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="modal-header">
                            <label class="control-label" style="font-size:15px;padding-bottom: 0px"><b>Tình trạng</b></label>
                            <label class="container"> Chưa đọc
                                <input type="checkbox" id="noread">
                                <span class="checkmark"></span>
                            </label>
                            <label class="container"> Đã đọc
                                <input type="checkbox" id="read">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="modal-header">
                            <label class="control-label" style="font-size:15px;padding-bottom: 0px"><b>Loại</b></label>
                            <label class="container" > Bình luận
                                <input type="checkbox" id="comment" >
                                <span class="checkmark"></span>
                            </label>
                            <label class="container"> Tin nhắn
                                <input type="checkbox" id="messager" >
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="modal-header" style="min-height: 120px">
                            <label class="control-label" style="font-size:15px;padding-bottom: 0px"><b>Nguồn</b></label></br>
                            <div class="col-md-12" style="padding-left: 0px;">
                                <div class="col-md-6" style="padding-left: 0px;">
                                    <label class="container"> Facebook
                                        <input type="checkbox" id="facebook" >
                                        <span class="checkmark"></span> 
                                    </label>
                                    <label class="container"> Zalo
                                        <input type="checkbox" id="zalo">
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="container"> LiveChat
                                        <input type="checkbox" id="live">
                                        <span class="checkmark"></span> 
                                    </label>
                                    <label class="container"> Viber
                                        <input type="checkbox" id="viber">
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <!--{{assigns.supervisor}}-{{extension}}-->
                        <div class="modal-header" v-if="assigns.supervisor==extension" >
                            <label class="control-label" style="font-size:15px;padding-bottom: 0px"><b>Assgins agent</b></label>
                            <label class="container" style="padding-left: 0px;">
                                <div class="col-md-3" style="padding-left: 0px;">Ext.</div>
                                <div class="col-md-9">
                                    <select  class="form-control" id="ext" >
                                        <option value="">Tất cả
                                        </option>
                                        <option v-for="assign in assigns.agents" :value="assign">{{assign}}
                                        </option>
                                    </select>
                                </div>
                            </label>
                        </div>
                        <div class="modal-footer">
                            <span class="action-icons">
                                <button type="button" class="btn-primary btn" v-on:click="filter_chat">
                                    <span>Filter</span>
                                </button>
                            </span>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <!--End modal Tram 15012019-->
        <!--Modal thông tin bài viết faebook-->
        <div role="dialog" class=" modal fade " id="info-model">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <form class=" ui-form">
                        <div class="modal-header">
                            <span class="modal-title">Thông tin bài viết</span>
                            <!--{{conversation}}-->
                            <button class="close" aria-label="Close" type="button">
                                <span v-on:click="dismiss_modal_info"><i class="fa fa-times"></i></span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row" v-for="conver in conversation">
                                <p>{{conver.content}}</p>
                                <div class="col-sm-3" v-for="cr in conver.attachments" >
                                    <img  v-if="cr.url" class="img-thumber" :src="cr.url" alt="" width="120" height="120" style="padding-bottom: 20px">
                                    <img  v-else class="img-thumber" :src="cr" alt="" width="120" height="120" style="padding-bottom: 20px">
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--End modal thông tin-->
        <!--Ticket Modal -->
        <div role="dialog" class=" modal fade " id="ticket-model">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <form class=" ui-form" id="rise-ticket-form">
                        <div class="modal-header">
                            <span class="modal-title"><b>Ticket</b></span>
                            <button data-dismiss="modal" class="close" aria-label="Close" type="button">
                                <span><i class="fa fa-times"></i></span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="ui-field form-field-required">
                                <div class="col-md-12" style="padding-left:0px">
                                    <div class="col-xs-6" style="padding-left:0px">
                                        <div class="ui-field form-field-required" style="padding-bottom: 20px;">  
                                            <div class="canle">
                                                <label class=" control-label">Họ và tên <span style="color:#FF0000;">*</span></label>
                                            </div>
                                            <div>
                                                <input  autofocus="" placeholder="Họ và tên " v-model="rise_ticket_info.name" autocomplete="on" type="text" class="form-control" autofocus style="width:50%">
                                            </div>
                                        </div>
                                        <div class="ui-field form-field-required" style="padding-bottom: 20px;">   
                                            <div class="canle">
                                                <label class=" control-label">Email</label>
                                            </div>
                                            <div>
                                                <input v-model="rise_ticket_info.email" autofocus="" placeholder="Email" autocomplete="on" type="text" class="form-control" autofocus style="width:50%">
                                            </div>
                                        </div>
                                        <div class="ui-field form-field-required" style="padding-bottom: 20px;">  
                                            <div class="canle">
                                                <label class=" control-label">Source<span style="color:#FF0000;">*</span></label>
                                            </div>
                                            <div>
                                                <!-- Oanh 03/03/2019 load thông tin lên rise ticket + 06/03/2019 Thay đổi id của source -->
                                                <select v-model="rise_ticket_info.sourceId" class="form-control" style="width:50%">
                                                    <option v-if="rise_ticket_info.sourceId == 'zalo'" selected value="zalo">Zalo</option><option v-else value="zalo">Zalo</option>
                                                    <option v-if="rise_ticket_info.sourceId == 'facebook' || rise_ticket_info.sourceId == 'messenger' || rise_ticket_info.sourceId == 'comment'" selected value="facebook">Facebook</option><option v-else value="facebook">Facebook</option>
                                                    <option v-if="rise_ticket_info.sourceId == 'livechat_remote'" selected value="livechat_remote">Livechat</option><option v-else value="livechat_remote">Livechat</option>
                                                </select>
                                                <!-- Oanh 03/03/2019 load thông tin lên rise ticket + 06/03/2019 Thay đổi id của source -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-6">
                                        <div class="ui-field form-field-required" style="padding-bottom: 20px;">  
                                            <div class="canle">
                                                <label class=" control-label">Số điện thoại<span style="color:#FF0000;">*</span></label>
                                            </div>
                                            <div>
                                                <input v-model="rise_ticket_info.phone" autofocus="" placeholder="Số điện thoại" autocomplete="on" type="text" class="form-control" autofocus style="width:50%">
                                            </div>
                                        </div>
                                        <div class="ui-field form-field-required" style="padding-bottom: 20px;">  
                                            <div class="canle">
                                                <label class=" control-label">Agent<span style="color:#FF0000;">*</span></label>
                                            </div>
                                            <div>
                                                <input v-model="rise_ticket_info.agent" autofocus="" placeholder="Agent" autocomplete="on" type="text" class="form-control" autofocus style="width:50%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ui-field form-field-required" >
                                <label class=" control-label">Nội dung cần tư vấn</label>
                                <div>
                                    <!--transfer_message tương đương với cột content trong bảng rise ticket-->
                                    <textarea v-model="transfer_message" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <span class="action-icons">
                                <button type="button" class="btn-default btn" data-dismiss="modal"><span>Cancel</span></button>
                                <button type="button" class="btn-primary btn"
                                        v-on:click="riseTicketFormSubmit"><span>Lưu</span></button>
                            </span>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <!--End modal thông tin-->



    </div>
    <script type="text/javascript">
        new Vue({
            el: "#content_chat",
            props: ['notification_message'],
            data: function () {
                return {
                    page_top: 0,
                    page_bottom: 0,
                    rooms: [],
                    search_item: [],
                    username: '',
                    name: '',
                    profile_pic: '',
                    conversation: [],
                    current_user_avatar: '',
                    current_room: '',
                    input_message: '',
                    people_id: '',
                    // load_userdata: false,
                    choice_tab_left: 'info',
                    userdata: [],
                    input_name: '',
                    input_phone: '',
                    input_email: '',
                    input_address: '',
                    input_search: '',
                    search_loading: false,
                    show_search_form: false,
                    edit_phone: false,
                    edit_email: false,
                    edit_address: false,
                    assigns: [],
                    transfer_to: '',
                    transfer_message: '',
                    property_show: false,
                    property_name: '',
                    property_value: '',
                    filter_room_text: 'All',
                    show_message_template: false,
                    note_show: false,
                    show_labels: false,
                    listLabels: [],
                    unread_stt: '',
                    extension: '',
                    rise_ticket_info: {},
                    pthdt_content_consulting: '',
                    room_status: 1
                }
            },
            created() {
                var self = this;
                this.current_room = '<?=$room_id?>';
                // console.log(this.current_room);
                this.join_room();
                this.getRooms();
                this.getUserProfile();
                this.scrollToEnd();
                socket.on('user_onlines', function (data) {
                });
                socket.on('notification', function (data) {
                    if (data.source == 'transfer_success') {
                        self.getRooms();
                        self.current_room = '';
                    }
                    showNotification(data.title, data.text, data.avatar);
                });

                //Nhận mes
                socket.on('receiveMes', function (data) {
                    console.log(data);
                    if (data.room_id == self.current_room) {
                        self.conversation.messages.push(data);
                        $('.loading-message').hide();
                    }

                    self.scrollToEnd();
                    if (self.username != data.sender_id) {
                        showNotification(data.name, data.text, data.profile_pic);
                    }
                    self.getRooms();
                });
                //Nhận mes File
                socket.on('receiveMesImg', function (data) {
                    if (data.room_id == self.current_room) {
                        self.conversation.messages.push(data);
                        $('.loading-message').hide();
                    }
                    self.scrollToEnd();
                    if (self.username != data.sender_id) {
                        showNotification(data.name, 'Bạn nhận được một tệp tin');
                    }
                    setTimeout(function () {
                        self.insertDateLine();
                    }, 2000);
                    self.getRooms();
                });
            },
            beforeMount(){
                this.getConversation(this.current_room);
            },
            mounted() {
                var self = this;

                var timer;
                socket.on('connect', function () {
                    self.join_room();
                });

                socket.on('reconnect', function () {
                    self.join_room();
                    console.log('reconnect fired!');
                });

                socket.on('loadnewroom', function () {
                    self.getRooms();
                    console.log('loadnewroom!');
                });


                $('.sidebar-brand').click(function () {
                    socket.emit('sendnoti', {username: 'demo3', title: 'Thành công rồi'});
                });
                socket.on('sendnoti', function (data) {
                    console.log(data);
                });
                $('.chatbox .chat-content-w').scroll(function (e) {
                    if (timer) {
                        window.clearTimeout(timer);
                    }
                    $this = $(this);
                    timer = window.setTimeout(function () {
                        var curent_scroll = $this.scrollTop();
                        if (curent_scroll <= 10) {
                            self.loadTop();
                        }
                        var scrollHeight_w = $this.get(0).scrollHeight;
                        var get_bottom = scrollHeight_w - (curent_scroll + $this.height());
                        if (get_bottom <= 10) {
                            self.loadBottom();
                        }
                    }, 200);
                });

                $(document).on('keyup', '.search-input', throttle(function () {
                    room_id = self.current_room;
                    if (self.input_search != "") {
                        $.ajax({
                            type: 'POST',
                            url: base_url + 'app/chat/searchSignle',
                            data: {room_id: room_id, text: self.input_search},
                            dataType: "json",
                            beforeSend: function () {
                                self.search_loading = true;
                            },
                            complete: function () {
                                self.search_loading = false;
                            },
                            success: function (json) {
                                if (json['success']) {
                                    self.search_item = json['success'];
                                    $('.full-chat-left .tab-pane').removeClass('in active');
                                    $('.full-chat-left .nav-item').removeClass('active');
                                    $('#tab-search').addClass('in active');
                                }
                            },
                        });
                    } else {
                        self.search_item = [];
                    }
                }));

                // Change assign
                $('.select_user').select2({
                    placeholder: "Choice User",
                }).on('change', function () {
                    self.transfer_to = $(this).val();
                });
                // Dán nhãn 
                this.listLabels = this.getListLabels();

            },
            methods: {
                filter_chat: function () {
                    var checkall = $("#checkall")[0];
                    if (checkall.checked == true) {
                        checkall = "all";
                    } else {
                        checkall = "";
                    }
                    var comment = $("#comment")[0];
                    if (comment.checked == true) {
                        comment = "comment";
                    } else {
                        comment = "";
                    }
                    var messager = $("#messager")[0];
                    if (messager.checked == true) {
                        messager = "messager";
                    } else {
                        messager = "";
                    }
                    var facebook = $("#facebook")[0];
                    if (facebook.checked == true) {
                        facebook = "facebook";
                    } else {
                        facebook = "";
                    }
                    var zalo = $("#zalo")[0];
                    if (zalo.checked == true) {
                        zalo = "zalo";
                    } else {
                        zalo = "";
                    }
                    var viber = $("#viber")[0];
                    if (viber.checked == true) {
                        viber = "viber";
                    } else {
                        viber = "";
                    }
                    var live = $("#live")[0];
                    if (live.checked == true) {
                        live = "livechat";
                    } else {
                        live = "";
                    }
                    var noread = $("#noread")[0];
                    if (noread.checked == true) {
                        noread = "noread";
                    } else {
                        noread = "";
                    }
                    var read = $("#read")[0];
                    if (read.checked == true) {
                        read = "read";
                    } else {
                        read = "";
                    }

                    $.ajax({
                        type: 'POST',
                        url: base_url + 'app/chat/filterChat',
                        dataType: "json",
                        data: {
                            checkall: checkall,
                            noread: noread,
                            read: read,
                            cmt: comment,
                            mess: messager,
                            face: facebook,
                            zalo: zalo,
                            viber: viber,
                            live: live,
                            ext: $('#ext').val(),
                            room_id: self.room_id
                        },
                        success: function (json) {
                            self.rooms = json;
                            if (self.rooms.length > 0 && self.current_room == '') {
                                self.getConversation(self.rooms[0].room_id);
                            }
                            self.dismiss_filter_modal();
                            // socket.emit('notification', json['success']);
                        },
                    });
                },
                transfer_user: function () {
                    var self = this;
                    if (confirm('Bạn có muốn chuyển cuộc trò chuyện này cho ' + $('#transfer_user').val())) {
                        $.ajax({
                            type: 'POST',
                            url: base_url + 'app/transfer/transferTo',
                            dataType: "json",
                            data: {
                                username: $('#transfer_user').val(), 
                                transfer_message: self.transfer_message, 
                                room_id: self.current_room
                            },
                            success: function (json) {
                                self.dismiss_transfer_modal();
                                socket.emit('notification', json['success']);
                            },
                        });
                    } else {
                        $('.select_user').val([]);
                    }

                },
                getAssigns: function () {
                    self = this;
                    $.ajax({
                        type: 'GET',
                        url: base_url + 'app/transfer/getAssigns',
                        data: {room_id: self.current_room},
                        dataType: "json",
                        success: function (json) {
                            self.assigns = json.data;
                            self.extension = json.extension;
                        },
                    });
                },
                getRooms: function (type = '') {
                    var self = this;
                    if (type == 'message') {
                        self.filter_room_text = 'Messages';
                    } else if (type == 'comment') {
                        self.filter_room_text = 'Comments';
                    } else if (type == 'resolved') {
                        self.filter_room_text = 'Resolved';
                    } else {
                        self.filter_room_text = 'All';
                    }

                    $.ajax({
                        type: 'POST',
                        url: base_url + 'app/Chatdetail/getRooms',
                        dataType: "json",
                        contentType: "application/json; charset=utf-8",
                        data: JSON.stringify({room_id: self.current_room, type: type}),
                        success: function (json) {
                            self.rooms = json;
                            if (self.rooms.length > 0 && self.current_room == '') {
                                self.getConversation(self.rooms[0].room_id);
                            }
                        },
                    });
                },
                getConversation: function (room_id, page = 0, mes_id = '') {
                    // $this = $(event.target);
                    var self = this;
                    $.ajax({
                        type: 'GET',
                        url: base_url + 'app/Chatdetail/conversation',
                        dataType: "json",
                        data: {room_id: room_id, page: page},
                        success: function (json) {
                            // $this.closest('.user-w').find('.new-mess').remove();
                            self.conversation = json;
                            self.room_status = json.status;
                            if (!mes_id) {
                                self.scrollToEnd();
                            }
                            self.current_room = room_id;
                            self.getPeople(room_id);
                            setTimeout(function () {
                                self.insertDateLine();
                            }, 100);
                            self.edit_phone = false;
                            self.edit_email = false;
                            self.edit_address = false;
                            self.page_top = page;
                            self.page_bottom = page/* <= 0 ? 0 : page-1*/;
                            if (mes_id) {
                                self.$nextTick(() => {
                                    var ref_mes = 'mes_id' + mes_id;
                                    $('.chat-content').find('.finded').removeClass('finded');
                                    $(self.$refs[ref_mes]).addClass('finded');
                                    $('.chatbox .chat-content-w').animate({
                                        scrollTop: $(self.$refs[ref_mes]).offset().top
                                    }, 500);

                                })
                            }
                        },
                    });
                },
                getPeople: function (room_id) {
                    var self = this;
                    $.ajax({
                        type: 'GET',
                        url: base_url + 'app/peoples/getPeople',
                        dataType: "json",
                        data: {room_id: room_id},
                        success: function (json) {
//                            console.log(json);
                            self.userdata = json;
                            // console.log(json.profile_pic);
                            self.current_user_avatar = (typeof json.profile_pic !== 'undefined' && json.profile_pic !== null && json.profile_pic !== '') ? json.profile_pic : '<?=base_url()?>assets/images/avatar_default.jpg';
                            console.log(self.current_user_avatar);
                        },
                    });
                },
                updatePeopledata: function (name) {
                    var self = this;
                    if (name == 'phone') {
                        var value = this.$refs.phone.value;
                    } else if (name == 'email') {
                        var value = this.$refs.email.value;
                    } else if (name == 'address') {
                        var value = this.$refs.address.value;
                    }

                    $.ajax({
                        type: 'POST',
                        url: base_url + 'app/peoples/editPeople',
                        dataType: "json",
                        data: {name: name, value: value, _id: self.userdata._id.$id},
                        success: function (json) {
                            self.getPeople(self.current_room);
                            self.edit_phone = false;
                            self.edit_email = false;
                            self.edit_address = false;
                        },
                    });
                },
                addPeopleProperty() {
                    var self = this;
                    if (self.property_name != "" && self.property_value != "") {
                        $.ajax({
                            type: 'POST',
                            url: base_url + 'app/peoples/addPeopleProperty',
                            dataType: "json",
                            data: {name: self.property_name, value: self.property_value, _id: self.userdata._id.$id},
                            success: function (json) {
                                self.getPeople(self.current_room);
                                self.property_show = false;
                                self.property_name = '';
                                self.property_value = '';
                            },
                        });
                    }

                },
                property_show_func() {
                    if (this.property_show == false) {
                        this.property_show = true;
                    } else {
                        this.property_show = false;
                    }

                },
                note_show_func() {
                    if (this.note_show == false) {
                        this.note_show = true;
                    } else {
                        this.note_show = false;
                    }

                },
                getUserProfile: function () {
                    var self = this;
                    $.ajax({
                        type: 'POST',
                        url: base_url + 'app/profile/getUserProfile',
                        dataType: "json",
                        success: function (json) {
                            //  console.log(json);
                            self.username = json.username;
                            self.name = json.name;
                            self.profile_pic = json.profile_pic;
                        },
                    });
                },
                show_search: function () {
                    if (this.show_search_form) {
                        this.show_search_form = false;
                        $('.full-chat-left .tab-pane').removeClass('in active');
                        $('.full-chat-left .nav-item').removeClass('active');
                        $('#tab-chat').addClass('in active');
                    } else {
                        this.show_search_form = true;
                        this.input_search = '';
                        this.$nextTick(() => {
                            $('.search-input').focus();
                        })

                    }
                },
                loadTop: function () {
                    var self = this;
                    $.ajax({
                        type: 'GET',
                        url: base_url + 'customers/search/redirectNotify',
                        dataType: "json",
                        data: {room_id: self.current_room, page: self.page_top + 1},
                        success: function (json) {
                            if (json[0]) {
                                self.page_top = self.page_top + 1;
                                self.conversation.messages = json.concat(self.conversation.messages);
                                $chat_contentw = $(document).find('.chat-content-w');
                                setTimeout(function () {
                                    $chat_contentw.animate({
                                        scrollTop: 150
                                    }, 600);
                                }, 100);
                            }

                            setTimeout(function () {
                                self.insertDateLine();
                            }, 100);

                        },
                    });
                },
                loadBottom() {
                    var self = this;
                    if (self.page_bottom != 0) {
                        $.ajax({
                            type: 'GET',
                            url: base_url + 'customers/search/redirectNotify',
                            dataType: "json",
                            data: {room_id: self.current_room, page: self.page_bottom - 1},
                            success: function (json) {
                                if (json[0]) {
                                    self.page_bottom = self.page_bottom - 1;
                                    self.conversation.messages = self.conversation.messages.concat(json);
                                }
                                setTimeout(function () {
                                    self.insertDateLine();
                                }, 100);
                            },
                        });
                    }
                },
                sortChatList: function () {
                    this.getRooms();
                },
                scrollToEnd: function () {
                    var self = this;
                    setTimeout(function () {
                        self.insertDateLine();
                    }, 150);
                    setTimeout(function () {
                        $chat_content = $(document).find('.chat-content');
                        $chat_contentw = $(document).find('.chat-content-w');
                        $chat_contentw.scrollTop($chat_content.height() + 99999);
                    }, 200);

                },
                insertDateLine: function () {
                    var date_array = [];
                    $(document).find('.chatbox .chat-date-separator').remove();
                    $(document).find('.chatbox .chat-message').each(function (index, value) {
                        var timestamp = $(this).find('.chat-message-date').attr('data-time');
                        var d = new Date(timestamp * 1000);
                        var curr_date = d.getDate();
                        var curr_month = d.getMonth() + 1;
                        var curr_year = d.getFullYear();
                        var date = curr_date + '/' + curr_month + '/' + curr_year;
                        if ($.inArray(date, date_array) >= 0) {

                        } else {
                            date_array.push(date);
                            date = MDFormat(curr_year + '-' + curr_month + '-' + curr_date);
                            $('<div class="chat-date-separator"><span>' + date + '</span></div>').insertBefore($(this));
                        }

                    });
                },
                private_replies: function () {
                    var self = this;
                    if (confirm('Xác nhận chat riêng với khách hàng!')) {
                        socket.emit('private_replies', {page_id: self.conversation.page_id, comment_id: self.conversation.details.comment_id});
                    }

                },
                resolve_room: function () {
                    var self = this;
                    if (confirm('Bạn có muốn đóng phiên?')) {//resolve_room
                        $.ajax({
                            url: base_url + 'app/chat/updateCloseRoom',
                            type: 'post',
                            dataType: 'json',
                            data: {room_id: self.current_room},
                            success: function (json) {
                                location.reload();

                            },
                            error: function (xhr, ajaxOptions, thrownError) {
                                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                            }
                        });
                    }
                },
                btn_chat: function () {
                    var self = this;
                    var chatMsg = self.input_message;
                    var text = chatMsg.replace(/\r?\n/g, '<br>');
                    var room_id = self.current_room;
                    var count_text = text.length;
                    if (count_text > 1000) {
                        alert('Tin nhắn vược quá kí tự cho phép!');
                        return
                    } else {
                        self.input_message = '';
                        if (text != "") {
                            $.ajax({
                                type: 'POST',
                                url: base_url + 'app/chat/sendChat',
                                data: {room_id: room_id, text: text},
                                dataType: "json",
                                beforeSend: function () {
                                    $('.loading-message').show();
                                    self.scrollToEnd();
                                },
                                complete: function () {
                                },
                                success: function (json) {
                                    if (json['success']) {
                                        $('.loading-message').hide();
                                        json.profile_pic = self.profile_pic;
                                        socket.emit('msg', {page_id: json['page_id'], receiver_id: json['receiver_id'], trigger: json['trigger'], source: json['source'], message_id: json['id'], text: text, room_id: room_id, type: json['type'], sender_id: self.username, name: self.name, profile_pic: self.profile_pic, date: json['date'], timestamp: json['timestamp']});
                                        self.getRooms();
                                        self.scrollToEnd();
                                        setTimeout(function () {
                                            self.insertDateLine();
                                        }, 100);
                                    }
                                }
                            });
                        }
                    }
                },

                upload_file: function () {
                    var self = this;
                    $('#form-upload').remove();
                    $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" value="" accept="image/*" /></form>');

                    $('#form-upload input[name=\'file\']').trigger('click');
                    if (typeof timer != 'undefined') {
                        clearInterval(timer);
                    }

                    timer = setInterval(function () {
                        if ($('#form-upload input[name=\'file\']').val() != '') {
                            clearInterval(timer);
                            var room_id = self.current_room;
                            $.ajax({
                                url: base_url + 'app/chat/uploadFileNode?room_id=' + room_id,
                                type: 'post',
                                dataType: 'json',
                                data: new FormData($('#form-upload')[0]),
                                cache: false,
                                contentType: false,
                                processData: false,
                                success: function (json) {
                                    if (json['error']) {
                                        alert(json['error']);
                                    }
                                    if (json['success']) {
                                        socket.emit('msgImg', {page_id: json['page_id'], receiver_id: json['receiver_id'], trigger: json['trigger'], source: json['source'], message_id: json['message_id'], text: json['text'], room_id: room_id, sender_id: self.username, name: self.name, date: json['date'], type: json['type'], url: json['url'], timestamp: json['date_added']});
                                        self.getRooms();
                                        self.scrollToEnd();
                                        setTimeout(function () {
                                            self.insertDateLine();
                                        }, 100);
                                    }
                                },
                                error: function (xhr, ajaxOptions, thrownError) {
                                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                                }
                            });
                        }
                    }, 500);
                },
                //          Model Ticket Tram 15012019
                popup_ticket_modal: function () {
                    self = this;

                    // Oanh 03/03/2019 load thông tin lên rise ticket + 06/03/2019 Thay đổi id của source
                    let sourceList = {
                        'zalo': 'zalo',
                        'facebook': 'facebook',
                        'messenger': 'facebook',
                        'comment': 'facebook',
                        'livechat_remote': 'livechat_remote'
                    };
                    this.rise_ticket_info.name = this.conversation.nameRoom;
                    this.rise_ticket_info.phone = '';
                    this.rise_ticket_info.email = '';
                    this.rise_ticket_info.agent = this.name;
                    this.rise_ticket_info.sourceId = sourceList[this.conversation.source];
                    console.log(sourceList[this.conversation.source]);
                    // Oanh 03/03/2019 load thông tin lên rise ticket + 06/03/2019 Thay đổi id của source

                    $('.select_user').val([]);
                    $('.select_user').trigger('change');
                    self.transfer_message = '';
                    $("#ticket-model").modal('show');
                    self.getAssigns();

                },
                dismiss_ticket_modal: function () {
                    $("#ticket-model").modal('hide');
                },
//                        Model Tram 15012019
                popup_filter_modal: function () {
                    self = this;
                    $('.select_user').val([]);
                    $('.select_user').trigger('change');
                    self.transfer_message = '';
                    $("#filter-model").modal('show');
                    self.getAssigns();

                },
                dismiss_filter_modal: function () {
                    $("#filter-model").modal('hide');
                },
                popup_transfer_modal: function () {
                    self = this;
                    $('.select_user').val([]);
                    $('.select_user').trigger('change');
                    self.transfer_message = '';
                    $("#transfer-model").modal('show');

                },
                dismiss_transfer_modal: function () {
                    $("#transfer-model").modal('hide');
                },
//                 End Tram 15012019 15012019       
                popup_modal_info: function (page_id, post_id) {
                    self = this;
                    $.ajax({
                        type: 'POST',
                        url: base_url + 'app/chat/getPostFacebook',
                        data: {page_id: page_id, post_id: post_id},
                        dataType: "json",
                        success: function (json) {
                            self.conversation = json;
                        }
                    });
                    $("#info-model").modal('show');

                },
                dismiss_modal_info: function () {
                    $("#info-model").modal('hide');
                },
                message_template_click: function (val) {
                    if (this.show_message_template == true) {
                        this.show_message_template = false;
                    } else {
                        this.quickChatList = this.getQuickChatList();
                        this.show_message_template = true;
                    }
                    if (val != '') {
                        this.input_message = val;
                    }
                },

                comment_action: function (event, mes_id, action, comment_id) {
                    $this = $(event.target);
                    if (!$this.hasClass('disabled')) {
                        if ($this.hasClass('active')) {
                            action_value = false;
                        } else {
                            action_value = true;
                        }

                        $.ajax({
                            type: 'POST',
                            url: base_url + 'app/chat/actionComment',
                            data: {id: mes_id, action: action},
                            dataType: "json",
                            success: function (json) {
                                if (json['success']) {
                                    $this.toggleClass('active');
                                    var data_array = {page_id: json['page_id'], comment_id: json['comment_id'], action: action, action_value: action_value};
                                    socket.emit('comment_action', data_array);
                                    if (action == "trash") {
                                        $this.closest('.chat-message-action').find('i').removeClass('active').addClass('disabled');
                                    }
                                }
                            }
                        });
                    }
                },
                join_room: function (val) {
                    $.ajax({
                        type: 'POST',
                        url: base_url + 'app/chat/autoJoin',
                        dataType: "json",
                        data: JSON.stringify({room_id: self.room_id}),
                        success: function (json) {
                            socket.emit('room_join', json);
                        }
                    });
                },
                getListLabels() {
                    let listLabels = [];
                    self = this;
                    $.ajax({
                        async: false,
                        global: false,
                        type: 'POST',
                        url: base_url + 'app/chat/getListLabels',
                        dataType: "json",
                        contentType: 'application/json',
                        success: function (json) {
                            listLabels = json;
                        },
                        error: function (errorData) {
                            console.log(errorData);
                        }
                    });
                    return listLabels;
                },

                openCloseLabel() {
                    if (this.show_labels) {
                        this.show_labels = false;
                    } else {
                        this.show_labels = true;
                    }
                },

                updateLabels(label) {
                    self = this;
                    $.ajax({
                        async: false,
                        global: false,
                        type: 'POST',
                        url: base_url + 'app/chat/updateLabels',
                        dataType: "json",
                        contentType: 'application/json',
                        data: JSON.stringify({_id: self.current_room, labelInfo: label}),
                        success: function (json) {
                            if (json === 'not existed') {
                                alert('Thêm nhãn thành công.');
                                self.getRooms();
                            } else {
                                alert('Nhãn đã tồn tại.');
                                self.getRooms();
                            }
                        },
                        error: function (errorData) {
                            console.log(errorData);
                        }
                    });
                },
                getQuickChatList() {
                    let quickChatList = [];
                    self = this;
                    $.ajax({
                        async: false,
                        global: false,
                        type: 'POST',
                        url: base_url + 'app/quickChat/getQuickChat',
                        dataType: "json",
                        contentType: 'application/json',
                        success: function (json) {
                            $.each(json, function (key, value) {
                                value.maucau = self.htmlTemplate(value.maucau);
                            });

                            quickChatList = json;
                        },
                        error: function (errorData) {
                            console.log(errorData);
                        }
                    });
                    return quickChatList;
                },
                htmlTemplate(rawString) {
                    self = this;
                    let newTxt = rawString.split('{{');
                    let htmlStringReturn = rawString;
                    for (let i = 1; i < newTxt.length; i++) {
                        let textValue = newTxt[i].split('}}')[0];
                        if (textValue.indexOf('|') != -1) {
                            let listSpin = textValue.split('|');
                            let randomKey = self.randomIntFromInterval(0, listSpin.length - 1);
                            htmlStringReturn = htmlStringReturn.replace('{{' + newTxt[i].split('}}')[0] + '}}', listSpin[randomKey]);
                        }

                        if (self.userdata[textValue.toString().toLowerCase()]) {
                            htmlStringReturn = htmlStringReturn.replace('{{' + newTxt[i].split('}}')[0] + '}}', self.userdata[textValue.toString().toLowerCase()]);
                        }
                    }
                    return htmlStringReturn;
                },
                randomIntFromInterval(min, max) {
                    return Math.floor(Math.random() * (max - min + 1) + min);
                },

                unread(unread_stt) {

                    self = this;
                    $.ajax({
                        type: 'POST',
                        url: base_url + 'app/chat/modifiUnread',
                        dataType: "json",
                        data: JSON.stringify({room_id: self.current_room, unread: unread_stt}),
                        contentType: 'application/json',
                        success: function (json) {
                            self.unread_stt = json;
                            self.getRooms();
                            if (self.unread_stt == "0") {
                                document.getElementById("btn-unread").style.color = "#1bbae1";
                            } else {
                                document.getElementById("btn-unread").style.color = "#ccc";
                            }

                        },
                        error: function (errorData) {
                            console.log(errorData);
                        }
                    });
                },

                // Oanh 26/02/2019 10:31AM Lưu ticket xuống db + 06/03/2019 Validate thiếu phone
                riseTicketFormSubmit(e) {
                    self = this;
                    this.rise_ticket_info.content = this.transfer_message;
                    if(typeof self.rise_ticket_info.name !== 'undefined' && self.rise_ticket_info.name !== '' && self.rise_ticket_info.name !== null && typeof self.rise_ticket_info.phone !== 'undefined' && self.rise_ticket_info.phone !== '' && self.rise_ticket_info.phone !== null && typeof self.rise_ticket_info.agent !== 'undefined' && self.rise_ticket_info.agent !== '' && self.rise_ticket_info.agent !== null && typeof self.rise_ticket_info.sourceId !== 'undefined' && self.rise_ticket_info.sourceId !== '' && self.rise_ticket_info.sourceId !== null) {
                        $.ajax({
                            type: 'POST',
                            url: base_url + 'app/chat/riseTicket',
                            dataType: "json",
                            contentType: 'application/json',
                            data: JSON.stringify(self.rise_ticket_info),
                            success: function (json) {
                                if(json) {
                                    //showNotification('THÊM MỚI PHÀN NÀN', 'Đã thêm mới thành công.');
                                    alert("Thêm mới phàn nàn thành công.");
                                    self.dismiss_ticket_modal();
                                }
                            },
                            error: function (errorData) {
                                console.log(errorData);
                            }
                        });
                        // alert("Thành công!");
                    }
                    else {
                        alert('Xin vui lòng điền đầy đủ thông tin có dấu *');
                    }
                },
                // Oanh 26/02/2019 10:31AM Lưu ticket xuống db + 06/03/2019 Validate thiếu phone
                // Oanh 23/01/2019 10:47AM Push to hot data
                openPushToHotDataModal(type, room_id) {
                    self = this;
                    switch (type) {
                        case 'part':
                            this.pthdt_content_consulting = '';
                            $('#copy-doan-hoi-thoai').modal('show');
                            break;
                        case 'all':
                            if(room_id) {
                                let content = '';
                                $.ajax({
                                    async: false,
                                    global: false,
                                    type: 'POST',
                                    url: base_url + 'app/chat/getAllChatMessageByRoomId',
                                    dataType: "json",
                                    contentType: 'application/json',
                                    data: JSON.stringify({room_id: room_id}),
                                    success: function (json) {
                                        // $.each(json, function(key, value) {
                                        //     content = content + '[' +
                                        // });
                                        console.log(json);
                                    },
                                    error: function (errorData) {
                                        console.log(errorData);
                                    }
                                });
                                this.pthdt_content_consulting = '123456789 Test ....';
                            }
                            else {
                                this.pthdt_content_consulting = 'Chưa có room id';
                            }
                            $('#copy-doan-hoi-thoai').modal('show');
                            break;
                    }
                }
                // END Oanh 23/01/2019 10:47AM Push to hot data
            },
            watch: {
                notification_message: function (val) {
                    self = this;
                    self.getRooms();
                    /*if (val.source=='zalo') {
                     
                     }*/

                },
            }

        })
        function throttle(f, delay) {
            var timer = null;
            return function () {
                var context = this, args = arguments;
                clearTimeout(timer);
                timer = window.setTimeout(function () {
                    f.apply(context, args);
                },
                        delay || 500);
            };
        }
        socket.on('changeStatus', function (data) {
            if (data.status == "online") {
                class_key = "icon-online";
            } else if (data.status == "offline") {
                class_key = "icon-offline";
            } else if (data.status == "busy") {
                class_key = "icon-busy";
            }
            $('.user-list').find('[data-user-id="' + data.user_id + '"]').find('i').removeClass('icon-offline icon-online icon-busy').addClass(class_key);
        });

        function showNotification(title, text, icon = base_url + 'logo_omni.jpg') {
            if (window.Notification) {
                Notification.requestPermission(function (status) {
                    text = text.replace('<br>', '');
                    text = text.substring(0, 20);
                    var n = new Notification(title, {body: text, icon: icon});
                    setTimeout(n.close.bind(n), 5000);
                });
            } else {
                alert('Your browser doesn\'t support notifications.');
        }
        }

        $(document).on('keyup', '.chat-input textarea', function (e) {
            e.preventDefault();
            $this = $(this);
            if (e.which === 13 && e.ctrlKey) {
                $(this).val(function (i, val) {
                    return val + "\n";
                });
            } else if (e.which === 13 && e.shiftKey) {
                return false;
            } else if (e.which === 13) {
                e.preventDefault();
                var content = $(this).val().replace(/^\s+|\s+$/g, "");
                if (content != "") {
                    $this.closest('.chat-controls').find('.chat-btn').trigger('click');
                } else {
                    $(this).val('');
                }
                return false;
            }
            return false;
        });

        
    </script>
    <script>

        function MDFormat(MMDD) {
            MMDD = new Date(MMDD + ' 00:00:00');
            var months = ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6", "Tháng 7", "Tháng 8", "Tháng 9", "Tháng 10", "Tháng 11", "Tháng 12"];
            var strDate = "";

            var today = new Date();
            today.setHours(0, 0, 0, 0);

            var yesterday = new Date();
            yesterday.setHours(0, 0, 0, 0);
            yesterday.setDate(yesterday.getDate() - 1);

            var tomorrow = new Date();
            tomorrow.setHours(0, 0, 0, 0);
            tomorrow.setDate(tomorrow.getDate() + 1);

            if (today.getTime() == MMDD.getTime()) {
                strDate = "Hôm nay";
            } else if (yesterday.getTime() == MMDD.getTime()) {
                strDate = "Hôm qua";
            } else if (tomorrow.getTime() == MMDD.getTime()) {
                strDate = "Ngày mai";
            } else {
                strDate = MMDD.getDate() + " " + months[MMDD.getMonth()] + " " + MMDD.getFullYear();
            }
            return strDate;
        }
    </script>


    <style type="text/css" media="screen">
        .chat-menu{
            height: calc((100vh - 10px) - 41px);
            -webkit-box-flex: 0;
            border-right: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 4px 0px 0px 4px;
            padding: 0px;
            width: 50px;
        }
        .chat-menu a {
            padding: 10px 5px;
            font-size: 26px;
            display: block;
            text-align: center;
        }
        .chat-menu a:hover {
            background-color: #eee;
        }
        .select2-result-repository{
            padding-top:4px;
            padding-bottom:3px
        }
        .select2-result-repository__avatar{
            float:left;
            width:60px;
            margin-right:10px
        }
        .select2-result-repository__avatar img{
            width:100%;
            height:auto;
            border-radius:2px
        }
        .select2-result-repository__meta{
            margin-left:70px
        }
        .select2-result-repository__title{
            color:black;
            font-weight:700;
            word-wrap:break-word;
            line-height:1.1;
            margin-bottom:4px
        }
        .select2-result-repository__forks,.select2-result-repository__stargazers{
            margin-right:1em
        }
        .select2-result-repository__forks,.select2-result-repository__stargazers,.select2-result-repository__watchers{
            display:inline-block;
            color:#aaa;
            font-size:11px
        }
        .select2-result-repository__description{
            font-size:13px;
            color:#777;
            margin-top:4px
        }
        .select2-results__option--highlighted .select2-result-repository__title{
            color:white
        }
        .select2-results__option--highlighted .select2-result-repository__forks,.select2-results__option--highlighted .select2-result-repository__stargazers,.select2-results__option--highlighted .select2-result-repository__description,.select2-results__option--highlighted .select2-result-repository__watchers{
            color:#c6dcef
        }
        #page-content {
            padding:0 !important;
        }
        .assign-to .btn{
            border: 1px solid #E1E4E9;
        }
        .btn-transfer{

        }
        .assign-to span{
            font-size: 13px;
            color: #2C3B48;

        }
        #transfer-model .select2-container, #transfer-model .select2-dropdown{
            width: inherit;
            border: 1px solid #E1E4E9;
            border-radius: 3px;
            transition: .3s all;
            max-width: 150px;
            min-width: 150px;

        }
        #transfer-model .select2-container--default .select2-selection--single {
            border: none;
        }
        .convo-action {
            position: relative;
            padding: 10px 15px 10px 8px;
            height: 60px;
            background-color: #fff;
            border-bottom: solid 1px #E0E4EA;
            color: #5D5D5D;
        }
        .convo-action div.dropdown-toggle {
            max-width: 240px;
            cursor: pointer;
            font-weight: 600;
            float: left;
            font-size: 14px;
            border-radius: 3px;
            padding: 10px;
            transition: .3s;
            height: 40px;
            overflow: hidden;
        }
        .convo-action div.dropdown-toggle:hover {
            background-color: #E1E4E9;
            transition: .3s;
        }
        .convo-action div.dropdown-toggle i.fa-angle-down {
            display: inline-block;
            -webkit-transition: all .2s ease-in 0s;
            -moz-transition: all .2s ease-in 0s;
            -o-transition: all .2s ease-in 0s;
            transition: all .2s ease-in 0s;
            width: 20px;
            text-align: center;
            font-size: 12px;
            line-height: 1;
            color: #8D8C8C;
        }
        ul.nav-list {
            margin: 0;
            list-style: none;
            padding: 0;
        }
        .convo-action.open div.dropdown-toggle i.fa-angle-down {
            -webkit-transform: rotate(180deg);
            -moz-transform: rotate(180deg);
            -o-transform: rotate(180deg);
            -ms-transform: rotate(180deg);
            transform: rotate(180deg);
        }
        .convo-action div.dropdown-toggle div.view-filter {
            max-width: 140px;
            float: left;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }
        .convo-action div.dropdown-toggle i.icon-ic_arrow_down {
            display: inline-block;
            -webkit-transition: all .2s ease-in 0s;
            -moz-transition: all .2s ease-in 0s;
            -o-transition: all .2s ease-in 0s;
            transition: all .2s ease-in 0s;
            width: 20px;
            text-align: center;
            font-size: 12px;
            line-height: 1;
            color: #8D8C8C;
        }
        ul.nav-list>li a.filter-link {
            color: #2C3B48;
            width: 230px;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }
        .notify-dropdown{
            right: 0;
            left: 0;
            padding: 0;
            margin: 0;
            z-index: 100;
            border: 1px solid #D3D8E0;
            width: 340px;
        }
        .convo-action.open>.dropdown-menu div.ui-filter-view ul.nav-list li{
            position: relative;
            background-color: #fff;
            cursor: pointer;
            border-bottom: 1px solid #FFF;
            height: inherit;
            padding: 10px 38px;

        }
        .convo-action.open>.dropdown-menu div.ui-filter-view ul.nav-list li.active, .convo-action.open>.dropdown-menu div.ui-filter-view ul.nav-list li:hover {
            background-color: #f5f5f5;
        }
        .convo-action.open>.dropdown-menu div.ui-filter-view ul.nav-list li a{
            color: #2C3B48;
        }
        .convo-action.open>.dropdown-menu div.ui-filter-view {

        }
        ul.nav-list>li .actions, ul.nav-list>li .count {
            position: absolute;
            right: 21px;
            top: 10px;
            cursor: pointer;
        }
        div.conversations ul.nav-list li {
            padding: 0;
            border-bottom: solid 1px #efefef;
            display: inline-block;
            width: 100%;
            position: absolute;
            height: 90px;
            transition: all .3s ease-out;
            background-color: #fff;
        }
        div.conversations ul.nav-list li a{
            color: #2C3B48;
            opacity: 1;
        }
        @-webkit-keyframes fadeInRight100{
            from{
                opacity:0;
                -webkit-transfer:translate3d(30px,0,0);
                transfer:translate3d(30px,0,0)
            }
            to{
                opacity:1;
                -webkit-transfer:none;
                transfer:none
            }
        }
        @keyframes fadeInRight100{
            from{
                opacity:0;
                -webkit-transfer:translate3d(30px,0,0);
                transfer:translate3d(30px,0,0)
            }
            to{
                opacity:1;
                -webkit-transfer:none;
                transfer:none
            }
        }
        .fade-enter-active, .fade-leave-active {
            transition: opacity 0.5s
        }

        .fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
            opacity: 0
        }

        .chat-message-action a{
            color: #594939;
            padding: 5px 0px 5px 10px;
        }
        .chat-message-action i.active{
            color: #798fff;
            font-weight: 600;
        }

        .chat-message-action i:first-child{
            padding-left: 0px;
        }
        .chat-message-action i{
            padding: 5px 0px 5px 10px;
            font-size: 14px;
            cursor: pointer;
        }
        .chat-message-action i.disabled{
            color: #c1bfbf;
            cursor: not-allowed;
        }

        .btn-load-bottom{
            margin: 10px 20px 20px;
            box-shadow: rgba(0, 0, 0, 0.2) 0px 1px 2px 0px;
            background-color: white;
            color: rgb(161, 170, 178);
            width: 32px;
            height: 32px;
            text-shadow: none;
            cursor: pointer;
            border-radius: 50%;
            font-size: 22px;
            justify-content: center;
            border: none;
            bottom: 130px;
            right: 0px;
            position: absolute;
        }
        .btn-load-bottom:hover{
            color: #1bbae1;
        }
        .box-searh-single{
            display: none;
        }
        .text-match{
            background-color: rgba(255, 255, 0, 0.8);
        }
        .box-searh-single{
            padding: 5px;
            border-top:1px solid rgba(0, 0, 0, 0.1);
            background-color: #e1f2fd;
        }
        .box-searh-single button{
            color: #09a3e4;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.1);

        }
        .box-searh-single button.btn-next, .box-searh-single button.btn-pre{
            border-radius: 11px;
        }
        .box-searh-single .btn-remove-box-search{
            color: #09a3e4;
            font-size: 18px;
            margin-left: 15px;
            vertical-align: sub;
            cursor: pointer;
        }
        .box-searh-single input{
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0 2px;
            width: 50%
        }
        .btn-upload{cursor: pointer;font-size: 20px}
        .full-chat-middle .box-group{
            padding: 5px 0px 0px 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.2);
        }
        .full-chat-middle .box-group span{
            position: relative;
            margin: 10px 15px 10px 10px;
            border-radius: 10px;
            padding: 2px 10px;
            background-color: #c7edfc;
            color: #222;
            display: inline-block;
        }
        .group-user-item{
            margin-left: 20px;
        }
        .group-user .user-name{
            color: #047bf8;
            font-weight: bold !important;
        }
        .group-user .user-date{
            background-color: #b0c4f3 !important;
            color: #fff !important;
        }
        .choice-user-group{
            margin: 20px 0;
        }
        .choice-user-group i, .agent-list li{
            cursor: pointer;
        }

        .choice-user-group div{
            background: #cdf1f3;
            color: #111;
            font-weight: normal;
            padding: 5px 9px 0px 9px;
            margin: 0px 10px 10px;
            border-radius: 16px;
            margin-right: 10px;
            display: inline-block;
        }
        .full-chat-left .nav-link i {
            display: inline-block;
            color: #b0c4f3;
            font-size: 26px;
            margin-bottom: 5px;

        }
        .full-chat-left .nav-item.active .nav-link i {
            color: #047bf8;
        }
        .full-chat-left .nav-item.active .nav-link span {
            color: #82868a;
        }
        .full-chat-left .nav-link span {
            display: block;
            font-size: 20px;
            color: rgba(0, 0, 0, 0.4);
        }
        .full-chat-left .nav-tabs .nav-item:hover .nav-link{

        }
        .full-chat-left .nav-tabs > li.active > a{
            color: #464a4c;
            background-color: transparent;
            border-color: #f7f7f7 #f7f7f7 transparent;
        }

        .content-box {
            /*vertical-align: top;
            padding: 2rem 3rem;*/
            /*padding: 10px;*/
        }

        .full-chat-left li.nav-item{
            text-align: center;
        }

        .full-chat-w .full-chat-left .os-tabs-w .nav {
            padding: 0px 20px;
        }
        /* @media (max-width: 1650px){
            .content-box {
                padding: 2rem;
            }
        } */

        .full-chat-w .full-chat-i {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: stretch;
            -ms-flex-align: stretch;
            align-items: stretch;
            background-color: #fff;
            padding: 0px
        }
        .full-chat-w .full-chat-left {
            height: calc(100vh - 10px - 41px);
            -webkit-box-flex: 0;
            -ms-flex: 0 0 340px;
            /*flex: 0 0 340px;*/
            width: 25%;
            border-right: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 4px 0px 0px 4px;
            padding:0px;
        }
        .full-chat-w .full-chat-left .os-tabs-w .nav {
            padding: 0px 20px
        }
        .full-chat-w .full-chat-left .chat-search {
            padding: 20px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05)
        }
        .full-chat-w .full-chat-left .element-search {
            position: relative;
            border: 1px solid #eee;
        }
        .full-chat-w .full-chat-left .element-search i {
            speak: none;
            font-style: normal;
            font-variant: normal;
            text-transfer: none;
            line-height: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            position: absolute;
            right: 15px;
            top: 27%;
            -webkit-transfer: translateY(-50%);
            transfer: translateY(-50%);
            font-size: 20px;
            color: rgba(0, 0, 0, 0.2);
            font-weight: 900;
            cursor: pointer;

        }
        .full-chat-w .full-chat-left .element-search i.fa-spin{
            top: 23%;
        }
        .full-chat-w .full-chat-left .element-search input {
            border: none;
            -webkit-box-shadow: none;
            box-shadow: none;
            background-color: #fff;
            border-radius: 30px;
            padding: 10px 50px 10px 15px;
            display: block;
            width: 100%;
            outline: none
        }
        .full-chat-w .full-chat-left .element-search input::-webkit-input-placeholder {
            color: rgba(0, 0, 0, 0.3)
        }
        .full-chat-w .full-chat-left .element-search input:-ms-input-placeholder {
            color: rgba(0, 0, 0, 0.3)
        }
        .full-chat-w .full-chat-left .element-search input::placeholder {
            color: rgba(0, 0, 0, 0.3)
        }
        .full-chat-w .full-chat-left .user-list{
            overflow-y: auto;
            height: calc((100vh - 137px) - 0px);
        }
        .full-chat-w .full-chat-left .user-list .user-w {
            cursor: pointer;
            position: relative;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            padding: 10px 20px;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            -webkit-transition: all 0.2s ease;
            transition: all 0.2s ease;

        }
        .full-chat-w .full-chat-left .user-list .user-w .new-mess{
            position: absolute;
            top: 0px;
            left: 60px;
            color: red;
            font-weight: bold;

        }
        .full-chat-w .full-chat-left .user-list .user-w .avatar {
            margin-right: 20px;
            -webkit-box-flex: 0;
            -ms-flex: 0 0 50px;
            flex: 0 0 50px
        }
        .full-chat-w .full-chat-left .user-list .user-w .avatar img {
            width: 50px;
            height: 50px;
            border-radius: 50px;
            display: block
        }
        .full-chat-w .full-chat-left .user-list .user-w .user-info {
            -webkit-box-flex: 1;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto
        }
        .full-chat-w .full-chat-left .user-list .user-w .user-name {
            font-weight: 500;
            color: #2C3B48;
            -webkit-transition: all 0.2s ease;
            transition: all 0.2s ease
        }
        .full-chat-w .chat-content-w .chat-message .user-name{
            font-size: 11px;
            color: #697379;
            padding-left: 37px;
            font-weight: 400;
            text-transfer: capitalize;
            padding-bottom: 1px;
        }
        .full-chat-w .chat-content-w .chat-message.finded .chat-message-content{
            background-color: #ffeded !important;
        }
        .full-chat-w .chat-content-w .chat-message .chat-message-content a img {
            vertical-align: text-bottom;
        }
        .full-chat-w .full-chat-left .user-list .user-w .last-message {
            color: #90A4AE;
            font-size: 12px;
            -webkit-transition: all 0.2s ease;
            transition: all 0.2s ease;
        }
        .full-chat-w .full-chat-left .user-list .user-w .line-message{
            color: #90A4AE;
            font-size: 12px;
            float:right;

        }
        .full-chat-w .full-chat-left .user-list .user-w .line-message img{
            width:18px;

        }
        .full-chat-w .full-chat-left .user-list .user-w .line-message i{
            font-size: 20px;

        }

        .full-chat-w .full-chat-left .user-list .user-w .user-date {
            float: right;
            border-radius: 5px;
            font-size: 12px;
            color: #90A4AE;
            -webkit-transition: all 0.2s ease;
            transition: all 0.2s ease;
        }
        .full-chat-w .full-chat-left .user-list .user-w:hover {
            background-color: #047bf8;

        }
        .full-chat-w .full-chat-left .user-list .user-w.active {
            background-color: #F2F5F7;
        }
        .full-chat-w .full-chat-middle {
            background-image: url(../../assets/images/bg_chat_admin/01.jpg);
            background-size: 600px 400px;
            width: 100%;
            padding:0;
        }
        .full-chat-w .full-chat-middle .chat-head {
            background: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.2);
            padding: 10px 20px;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-pack: justify;
            -ms-flex-pack: justify;
            justify-content: space-between;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center
        }
        .full-chat-w .full-chat-middle .chat-head a.name-room i {
            color: #27ae60;
            font-size: 20px;
            vertical-align: middle;
            margin-left: 5px;
        }
        .full-chat-w .full-chat-middle .chat-head input {
            border: none;
        }
        .full-chat-w .full-chat-middle .user-info a.active input {
            border: 1px solid #27ae60;
        }
        .full-chat-w .full-chat-middle .user-info {
            /*font-size: 1.08rem*/
        }
        .full-chat-w .full-chat-middle .user-info span {
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px
        }
        .full-chat-w .full-chat-middle .user-info a {
            display: inline-block;
            vertical-align: middle;
            color: #3b75e3;
            font-weight: 500;
            font-size: 17px;
        }
        .full-chat-w .full-chat-middle .user-info a:hover {
            text-decoration: none
        }
        .full-chat-w .full-chat-middle .user-actions a {
            margin-left: 1rem;
            font-size: 24px;
            cursor: pointer;
            display: inline-block;
            vertical-align: middle
        }
        .full-chat-w .full-chat-middle .btn-more{
            font-size: 15px !important;
            border: 1px solid;
            border-radius: 5px;
            padding: 2px 3px;
        }
        .full-chat-w .chat-content-w {
            overflow-y: scroll;
            visibility: hidden;
            /*position: relative;*/
            /*height: calc(100vh - 55px - 177px);*/


        }
        .full-chat-w .chat-content-w .chat-content,
        /*.full-chat-w .chat-content-w .chat-content:hover,
        .full-chat-w .chat-content-w .chat-content:focus, */
        .full-chat-w .chat-content-w:hover,
        .full-chat-w .chat-content-w:focus,
        .chat-message
        {
            visibility: visible;
        }
        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255,255,255,.1);
        }


        ::-webkit-scrollbar-thumb {
            background: rgba(216,216,216,.5);
            border-radius: 5px;
        }
        .full-chat-w .chat-content-w .chat-content {
            padding: 10px 0px;
            /*min-height: 600px*/
            /*overflow-y: hidden;*/
            height: calc(100vh - 55px - 185px);
        }
        .full-chat-w .chat-content-w .chat-date-separator {
            text-align: center;
            color: #656565;
            position: relative;
            margin: 20px 0px
        }
        .full-chat-w .chat-content-w .chat-date-separator:before {
            content: "";
            background-color: rgba(0, 0, 0, 0.1);
            height: 1px;
            width: 100%;
            position: absolute;
            top: 50%;
            left: 0px;
            right: 0px
        }
        .full-chat-w .chat-content-w .chat-date-separator span {
            display: inline-block;
            background-color: #fff;
            padding: 0px 10px;
            position: relative;
            border-radius: 15px;
        }
        .full-chat-w .chat-content-w .chat-message {
            position: relative;
            padding: 0 20px 20px 20px;
        }
        .full-chat-w .chat-content-w .chat-message .chat-message-content {
            padding: 5px 15px;
            background-color: #b4eaff;
            color: #1F2B36;
            display: inline-block;
            margin-bottom: -20px;
            margin-left: 35px;
            border-radius: 5px;
            text-align: left;
            max-width: 70%;
            word-wrap: break-word;
            box-shadow: 0 1px 0 rgba(0,0,0,.12);
        }
        .full-chat-w .chat-content-w .chat-message .chat-message-avatar {
            position:  absolute;
            display: inline-block;
            vertical-align: bottom;
            top: 16px;
            left: 20px;
        }
        .full-chat-w .chat-content-w .chat-message.self .chat-message-avatar {
            top: 0;
            right: 20px;
        }

        .full-chat-w .chat-content-w .chat-message .chat-message-avatar img {
            width: 30px;
            height: 30px;
            border-radius: 30px;
            display: inline-block;
        }

        .full-chat-w .chat-content-w .chat-message .chat-message-date {
            display: inline-block;
            vertical-align: bottom;
            margin-left: 35px;
            /*margin-right: 10px;*/
            font-size: 1.1rem;
            color: rgba(0, 0, 0, 0.3)
        }

        .full-chat-w .chat-content-w .chat-message.self .chat-message-date {
            margin-right: 35px;
        }
        .full-chat-w .chat-content-w .chat-message.self {
            text-align: right;
        }
        .full-chat-w .chat-content-w .chat-message.self .chat-message-content {
            background-color: #fff;
            color: #2A4E7F;
            margin-right: 35px;
            margin-left: 0px;
            word-wrap: break-word;
        }
        .full-chat-w .chat-controls {
            margin: 0 20px;
            border-radius: 5px;
            padding: 10px;
            background: #fff;
            border: 1px solid #E0E4EA;
            box-shadow: 0px 0px 1px #E0E4EA;
        }
        .full-chat-w .chat-input textarea/* input[type="text"] */ {
            padding: 10px 0;
            border: none;
            display: block;
            width: 100%;
            outline: none
        }
        .full-chat-w .chat-input-extra {
            width: 100%;
            border-top: 1px solid #E0E4EA;
            text-align: right;
        }
        .full-chat-w .chat-input-extra .chat-extra-actions{
            position: relative;
            display: inline-block;
        }

        .full-chat-w .chat-input-extra .chat-extra-actions .box-message-template{
            position: absolute;
            bottom: 13px;
            right: 8px;
        }
        .full-chat-w .chat-input-extra .chat-extra-actions a {
            margin-right: 10px;
            display: inline-block
        }
        .full-chat-w .chat-input-extra .chat-extra-actions a i{
            vertical-align: -webkit-baseline-middle;
        }
        .full-chat-w .chat-input-extra .chat-btn {
            padding: 5px 8px;
            border-radius: 3px;
            background-color: #1E68C6;
            margin-top: 7px;
            font-size: 13px;
            line-height: 1;
            margin-left: 5px;
            transition: .3s;
            color: #fff;
        }
        .full-chat-w .user-intro {
            text-align: center
        }
        .full-chat-w .user-intro .user-details{
            width: 100%;
            display: table;
            text-align: left;
            border-bottom: 1px solid #F2F5F7;
            padding-bottom: 15px;
        }
        .full-chat-w .user-intro .avatar{
            display: table-cell;
            width: 22%;
        }
        .full-chat-w .user-intro .user-name{
            display: table-cell;
            width: 78%;
            padding: 5px 5px;
            font-size: 18px;
            padding: 5px 10px;
            color: #1E88E5;
            line-height: 1;
            word-wrap: break-word;
            text-transfer: capitalize;
            position: relative;
            transition: all .3s ease-in-out;
        }
        .full-chat-w .user-intro .avatar img {
            width: 60px;
            border-radius: 50%;
            height: 60px;
        }
        .full-chat-w .user-intro .user-intro-info {
            border-bottom: 1px solid #F2F5F7;
            padding: 10px 0;

        }
        .full-chat-w .user-intro .user-intro-info .user-sub {
            color: rgba(0, 0, 0, 0.3);
            text-transfer: capitalize;
            letter-spacing: 1px;
            margin-top: 5px
        }
        .full-chat-w .user-intro .user-intro-info .user-social {
            margin-top: 1rem
        }
        .full-chat-w .user-intro .user-intro-info .user-social a {
            display: inline-block;
            margin: 0px 6px;
            font-size: 24px
        }
        .full-chat-w .user-intro .user-intro-info .user-social a:hover {
            text-decoration: none
        }
        .full-chat-w .user-intro .user-intro-info .user-social i.os-icon.os-icon-twitter {
            color: #31a7f3
        }
        .full-chat-w .user-intro .user-intro-info .user-social i.os-icon.os-icon-facebook {
            color: #175dc5
        }
        .full-chat-w .chat-info-section {
            padding: 20px
        }
        .full-chat-w .chat-info-section .ci-header i {
            color: #047bf8;
            font-size: 20px;
            margin-right: 10px;
            display: inline-block;
            vertical-align: middle
        }
        .full-chat-w .chat-info-section .ci-header span {
            text-transfer: uppercase;
            color: rgba(0, 0, 0, 0.5);
            letter-spacing: 2px;
            display: inline-block;
            vertical-align: middle
        }
        .full-chat-w .chat-info-section .ci-content {
            padding: 20px
        }
        .full-chat-w .chat-info-section .ci-content .ci-file-list ul {
            list-style-type: square;
            color: #98c9fd;
            margin-left: 0px;
            margin-bottom: 0px;
            padding-left: 10px
        }
        .full-chat-w .chat-info-section .ci-content .ci-file-list ul li {
            margin: 5px
        }
        .full-chat-w .chat-info-section .ci-content .ci-file-list ul li a {
            /*font-size: .81rem;*/
            border-bottom: 1px solid #047bf8
        }
        .full-chat-w .chat-info-section .ci-content .ci-file-list ul li a:hover {
            text-decoration: none
        }
        .full-chat-w .chat-info-section .ci-content .ci-photos-list {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            -webkit-box-align: start;
            -ms-flex-align: start;
            align-items: flex-start
        }
        .full-chat-w .chat-info-section .ci-content .ci-photos-list img {
            margin: 2%;
            border-radius: 6px;
            width: 45%;
            display: inline-block;
            height: auto
        }
        .full-chat-w .chat-info-section+.chat-info-section {
            border-top: 1px solid rgba(0, 0, 0, 0.1)
        }

        div.user-data div.section-title{
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            position: relative;
            line-height: 1;
            color: #2C3B48;
            border: none;
            margin-bottom: 0;
            padding-bottom: 10px;
            padding-top: 15px;
        }
        .right-chat-tab{
            display: flex;
            flex-direction: row;
            margin-bottom: 10px;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            padding: 0;
            margin: 0;
        }
        .right-chat-tab .tab-item {
            box-sizing: border-box;
            flex: 1;
            width: 25%;
            padding: 10px 0 1px 0;
            text-align: center;
            border-bottom: 1px solid #ccc;
            cursor:pointer;
        }
        .right-chat-tab .tab-item.active{
            border-bottom: 2px solid #1bbae1;
        }
        .dot {
            height: 12px;
            width: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        .container {
            display: block;
            position: relative;
            padding-left: 35px;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: #eee;

        }

        .container:hover input ~ .checkmark {
            background-color: #ccc;
        }

        .container input:checked ~ .checkmark {
            background-color: #2196F3;
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .container input:checked ~ .checkmark:after {
            display: block;
        }

        .container .checkmark:after {
            left: 9px;
            top: 5px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 3px 3px 0;
            -webkit-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
            transform: rotate(45deg);
        }
        .modal-header {
            padding: 9px;
        }
        .modal-dialog {
            width: 900px;
            margin: 30px auto;
        }
        .canle{
            float: left;
            padding: 5px 40px 0px 0px;
            width: 40%;
        }

        /* Oanh 21/01/2019 11:23AM Push to hot data */
        .dropdown-menu {
            left: unset;
            right: 0;
        }

        #btn-push-to-hot-data {
            box-shadow: none;
        }

        #btn-group-push-to-hot-data li a:hover {
            color: #1BBAE1;
            background-color: #ffffff;
        }

        #push-to-hot-data-form .col-xs-6 {
            margin-bottom: 15px;
        }
        /* END Oanh 21/01/2019 11:23AM Push to hot data */
    </style>
</div> 