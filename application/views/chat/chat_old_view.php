
<div class="chats">


    <div id="content_chat">
        <div class="infomation" style="padding: 13px 0;background-color: #FFF;font-size: 13px;display: none;">
        </div>
        <div class="full-chat-w">
            <div class="full-chat-i">
                <div class="chat-menu">
                    <a href="#" style="margin-top: 44px;" title="Bộ lọc"><i class="fa fa-filter" aria-hidden="true"></i></a>
                    <a href="<?php echo base_url('app/chat_group_manager'); ?>" title="Quản lý nhóm"><i class="fa fa-users" aria-hidden="true"></i></a>
                    <a href="<?php echo base_url('app/fanpage'); ?>" title="Quản lý Fanpage"><i class="fa fa-cloud" aria-hidden="true"></i></a>
                </div>
                <div class="full-chat-left">
                    <div v-if="show_search_form" class="chat-search">
                        <div  class="element-search">
                            <input class="search-input" v-model="input_search" placeholder="Tìm kiếm" type="text">
                            <i v-if="!input_search" class="fa fa-search"></i>
                            <i v-if="input_search && !search_loading" v-on:click="show_search" class="far fa-times-circle"></i>
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
                                <div v-for="room in rooms" class="user-w singer-user" :class="{active:current_room==room.room_id}" v-on:click="getConversation(room.room_id)">
                                    <div class="avatar with-status status-green">
                                        <img :alt="room.page_name" :title="room.page_name" :src="room.avatar">
                                    </div>
                                    <div class="user-info">
                                        <div class="user-date">{{room.date_active}}</div>
                                        <div class="user-name">{{room.group_name}}</div>
                                        <div class="last-message">{{room.last_mes}}</div>
                                        <div class="line-message">
                                            <!--<span v-if="room.transfer_from">transfer từ {{room.transfer_from}} |</span>-->
                                            <span>{{room.page_name}}
                                                <!--{{room.icons}}-->
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
                                <!--{{conversation}}-->
                                <a class="name-room">{{conversation.nameRoom}}</a>
                            </div>
                            <div class="assign-to" v-if="current_room">
                                <!--<button v-on:click="popup_transfer_modal" type="button" class="btn btn-sm btn-transfer"><i class="fas fa-exchange-alt"></i> Transfer</button>-->
                                <!-- <span class="title">Assign to: </span> -->
                                <!-- <select name="" class="form-control select_user">
                                  <option></option>
                                  <option v-for="assign in assigns" :value="assign.username">{{assign.name}}</option>
                                </select> -->
                            </div>
                            <div class="user-actions" v-if="current_room" >
                                <!--{{conversation.messages["0"]["details"]}}-->
                                <a  v-if="conversation.source=='facebook'" v-on:click="popup_modal_info(conversation.page_id,conversation.messages)"  class="btn-search-single" title="Thông tin phòng chat"><i class="fa fa-info-circle"></i></a>
                                <!--{{message.details.post_id}}-->
                                <!--<a v-on:click="private_replies" class="btn-search-single" title="Chat riêng"><i class="fa fa-envelope"></i></a>-->
                                <a v-on:click="show_search" class="btn-search-single" title="Tìm kiếm"><i class="fa fa-search"></i></a>
                                <a class="btn-close-room" v-on:click="resolve_room" title="Đóng phiên"><i class="fa fa-times"></i></a>
                            </div>
                        </div>

                        <div class="chat-content-w scroll1">
                            <div class="chat-content" ref="list" v-if="current_room">

                                <div v-for="message in conversation.messages" class="chat-message" :class="{self:message.sender_id==username}" :ref="'mes_id'+message.id">
                                    <!--{{conversation.messages}}-->
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
                                            <!-- <svg id="Layer_1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 15" width="16" height="12"><path fill="#92A58C" d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"></path></svg> -->
                                            <!-- <svg id="Layer_1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 15" width="16" height="15"><path fill="#4FC3F7" d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"></path></svg> -->
                                        </div>
                                        <div class="chat-message-content" v-else-if="message.type=='image'"><a :href="message.url" target="_blank"><img style="width: 200px;" :src="message.url" :alt="message.text" :title="message.text"></a></div>
                                        <div class="chat-message-content" v-else-if="message.type=='file'"><a :href="message.url" target="_blank">{{message.text ? message.text : message.url}}</a></div>
                                        <div class="chat-message-content" v-else-if="message.type=='link'"><a :href="message.url" target="_blank">{{message.text ? message.text : message.url}}</a></div>
                                    </div>
                                    <div class="chat-message-avatar">
                                        <!--{{message.sender_id}}-->
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
                        <div class="chat-controls" v-if="current_room">
                            <div class="chat-input"><textarea v-model="input_message" placeholder="Nhập 1 tin nhắn..."></textarea></div>
                            <div class="chat-input-extra ">
                                <div class="chat-extra-actions ">
                                    <a v-if="conversation.source!='facebook'" class="btn-upload" v-on:click="upload_file"><i class="fa fa-image"></i></a>
                                    <a v-on:click="" ><i class="fa fa-tag"></i></a>
                                    <a class="btn-show-mess" v-on:click="message_template_click('')" ><i class="fa fa-comment"></i></a>
                                    <div class="box-message-template" v-if="show_message_template">
                                        <ul class="list-group" style=" width: 300px;box-shadow: 0px 0px 1px #E0E4EA;">
                                            <li class="list-group-item" v-on:click="message_template_click('Xin chào bạn, bạn cần giúp đỡ gì ạ?')" >Xin chào bạn, bạn cần giúp đỡ gì ạ?</li>
                                            <li class="list-group-item" v-on:click="message_template_click('Bootstrap')" >Bootstrap</li>
                                            <li class="list-group-item" v-on:click="message_template_click('HTML')" >HTML</li>
                                            <li class="list-group-item" v-on:click="message_template_click('CSS')" >CSS</li>
                                            <li class="list-group-item" v-on:click="message_template_click('Angular JS')" >Angular JS</li>
                                        </ul>
                                    </div>
                                    <!-- v-on:click="upload_file" -->
                                    <!-- <a class="btn-upload" v-on:click="upload_file"><i class="far fa-comment-dots"></i></a> -->
                                </div>
                                <div class="chat-btn btn btn-primary btn-sm" :class="{disabled:!input_message}" v-on:click="btn_chat">Gửi<i></i></div>
                            </div>
                        </div>
                    </div><!--/.chatbox-->
                </div><!--/.full-chat-middle-->


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

        <div role="dialog" class=" modal fade " id="transfer-model">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <form class="ui-form">
                        <div class="modal-header">
                            <span class="modal-title">Chuyển cuộc trò truyện</span>
                            <button v-on:click="dismiss_transfer_modal" class="close" aria-label="Close" type="button">
                                <span ><i class="fa fa-times"></i></span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="ui-field form-field-required" >
                                <label for="groupDescription" class=" control-label">Chuyển cho</label>
                                <div>
                                    <select name="" class="form-control select_user">
                                        <option></option>
                                        <option v-for="assign in assigns" :value="assign.username">{{assign.name}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="ui-field form-field-required">
                                <label class=" control-label">Lời nhắn</label>
                                <div>
                                    <textarea v-model="transfer_message" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <span class="action-icons">
                                <button type="button" class="btn-default btn" v-on:click="dismiss_transfer_modal"><span>Cancel</span></button>
                                <button type="button" class="btn-primary btn" v-on:click="transfer_user"><span>transfer</span></button>
                            </span>
                        </div>

                    </form>
                </div>
            </div>
        </div>



        <div role="dialog" class=" modal fade " id="baoxau-model">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <form class=" ui-form">
                        <div class="modal-header">
                            <span class="modal-title">Báo Xấu</span>
                            <button class="close" aria-label="Close" type="button">
                                <span v-on:click="dismiss_modal_baoxau"><i class="fa fa-times"></i></span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="ui-field form-field-required">
                                <label class=" control-label">Họ tên</label>
                                <div>
                                    <input v-model ="customer_name" autofocus="" placeholder="Nhập họ tên" autocomplete="on" type="text" class="form-control">

                                </div>
                            </div>

                            <div class="ui-field form-field-required">
                                <label class=" control-label">Số điện thoại</label>
                                <div>
                                    <input v-model ="customer_phone" autofocus="" placeholder="Nhập số điện thoại" autocomplete="on" type="text" class="form-control">
                                </div>
                            </div>

                            <div class="ui-field form-field-required">
                                <label class=" control-label">Địa chỉ</label>
                                <div>
                                    <input  v-model ="customer_adress" autofocus="" placeholder="Nhập địa chỉ" autocomplete="on" type="text" class="form-control">

                                </div>
                            </div>

                            <div class="ui-field form-field-required">
                                <label class=" control-label">Lý do báo xấu</label>
                                <div>
                                    <textarea autofocus="" placeholder="" autocomplete="on" type="text" class="form-control">
                                    </textarea>
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <span class="action-icons">
                                <button type="button" class="btn-default btn" v-on:click="dismiss_modal_baoxau"><span>Cancel</span></button>
                                <button type="button" class="btn-primary btn" ><span>Lưu</span></button>
                                <!-- <button type="button" class="btn-primary btn" v-on:click="poup_edit ? edit_official_account() : add_official_account()"><span>{{poup_edit ? 'Cập nhật' : 'Lưu'}}</span></button> -->
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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
                        <!--<div v-if="conversation.source=='facebook'">-->
                        <div class="modal-body">
                            <!--{{conversation}}-->

                            <div class="row" v-for="conver in conversation">
                                <!--{{conver}}-->
                                <p>{{conver.content}}</p>
                                <div class="col-sm-3" v-for="cr in conver.attachments" >
                                    <!--{{cr}}-->
                                    <img  v-if="cr.url" class="img-thumber" :src="cr.url" alt="" width="120" height="120" style="padding-bottom: 20px">
                                    <!--<p></p>-->
                                    <img  v-else class="img-thumber" :src="cr" alt="" width="120" height="120" style="padding-bottom: 20px">
                                </div>

                            </div>
                        </div>
                        <!--</div>-->
                    </form>
                </div>
            </div>
        </div>

    </div>
    <!-- <script>
 new Vue({
     el: "#content",
     data: {
         filter_room_text: 'All2',
     }
 });
</script> -->
    <script type="text/javascript">
        new Vue({
            el: "#content_chat",
            data: function () {
                return {
                    shipping_free: '',
                    customer_name: '',
                    customer_phone: '',
                    customer_adress: '',
                    Province_id: '',
                    Province: [],
                    Districts_id: '',
                    Districts: [],
                    Wards_id: '',
                    Service: '',
                    Wards: [],
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
                }
            },
            created() {
                var self = this;
                this.join_room();
                this.getRooms();
                this.getUserProfile();
                this.getAssigns();
                this.scrollToEnd();
//                this.$parent.$on('noti_test1', function (data) {
//                    console.log(data);
//                });
                socket.on('user_onlines', function (data) {
                    // console.log(data);
                });
                socket.on('notification', function (data) {
                    if (data.source == 'transfer_success') {
                        self.getRooms();
                        // if (data.room_id == self.current_room) {
                        self.current_room = '';
                        // self.$delete(conversation);
                        // self.$delete(conversation);
                        // self.conversation = '';
                        // module_exports.
                        // self.conversation.splice(0,10);
                        // console.log(self.conversation.length);
                        // console.log(self.conversation);
                        // self.conversation.message = '';
                        // }
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
                    /* setTimeout(function(){
                     self.insertDateLine();
                     }, 1500);*/
                    self.getRooms();
                });
                //Nhận mes File
                socket.on('receiveMesImg', function (data) {
                    //console.log(data);
                    if (data.room_id == self.current_room) {
                        self.conversation.messages.push(data);
                        $('.loading-message').hide();
                    }
                    // console.log(self.conversation.messages);
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
            mounted() {
//                var socket = io.connect('https://websocketsanbox.worldfone.vn', {
//                    path: "/omni/socket.io/",
//                    reconnection: true,
//                    reconnectionDelay: 500,
//                    reconnectionDelayMax: 5000,
//                    reconnectionAttempts: Infinity
//                });
//

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



                /*$(document).click(function(event) {
                 if (!$(event.target).is(".box-message-template, .btn-show-mess")) {
                 console.log(self.show_message_template);
                 if (self.show_message_template == true) {
                 // self.show_message_template = false;
                 console.log(self.show_message_template);
                 }
                 }
                 });*/

                /*$(document).click(function(event) {
                 if (!$(event.target).closest(".box-message-template, .btn-show-mess").length) {
                 // $(".box-message-template").hide();
                 self.show_message_template == false;
                 }else{
                 self.show_message_template == true;
                 }
                 console.log(self.show_message_template);
                 });*/

            },
            methods: {

                transfer_user: function () {
                    var self = this;
                    var data_select_user = $('.select_user').select2('data');
                    if (self.transfer_to != '') {
                        // console.log(data_select_user);
                        if (confirm('Bạn có muốn chuyển cuộc trò chuyện này cho ' + data_select_user[0].text)) {
                            // console.log({username: self.transfer_to, transfer_message:self.transfer_message, room_id: self.current_room});
                            $.ajax({
                                type: 'POST',
                                url: base_url + 'app/users/transferTo',
                                dataType: "json",
                                data: {username: self.transfer_to, transfer_message: self.transfer_message, room_id: self.current_room},
                                success: function (json) {
                                    self.dismiss_transfer_modal();
                                    socket.emit('notification', json['success']);
                                },
                            });
                        } else {
                            $('.select_user').val([]);
                        }
                    } else {
                        alert('Chưa chọn User!');
                    }
                },
                getAssigns: function () {
                    var self = this;
                    $.ajax({
                        type: 'GET',
                        url: base_url + 'app/users/getAssigns',
                        dataType: "json",
                        success: function (json) {
                            self.assigns = json;
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
                        type: 'GET',
                        url: base_url + 'app/chat/getRooms',
                        dataType: "json",
                        data: {type: type},
                        success: function (json) {
                            console.log(json);
                            self.rooms = json;
                            if (self.rooms.length > 0 && self.current_room == '') {
                                self.getConversation(self.rooms[0].room_id);
                            }
                        },
                        // error: function (xhr, ajaxOptions, thrownError) {
                        //         alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        //     }
                    });
                },
                getConversation: function (room_id, page = 0, mes_id = '') {
                    $('.loading-message').hide();
                    var self = this;
                    $.ajax({
                        type: 'GET',
                        url: base_url + 'app/chat/conversation',
                        dataType: "json",
                        data: {room_id: room_id, page: page},
                        success: function (json) {
                            // console.log(json);
                            self.conversation = json;
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
                            console.log(json);
                            self.userdata = json;
                            self.current_user_avatar = json.profile_pic;
                        },
                    });
                },
                updatePeopledata: function (name) {
                    var self = this;
                    // console.log(name);
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
                        url: base_url + 'app/chat/redirectNotify',
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
                            url: base_url + 'app/chat/redirectNotify',
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
                                /*self.conversation = [];
                                 self.getRooms();
                                 self.scrollToEnd();*/
                                // alert(location.href);
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
                                // debugger;
                                success: function (json) {
                                    if (json['error']) {
                                        alert(json['error']);
                                    }
                                    if (json['success']) {
                                        //  console.log(json);
                                        // self.conversation.messages.push(json);
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
                popup_modal_product: function (id) {
                    self = this;




                    function formatRepo(repo) {
                        if (repo.loading) {
                            return repo.text;
                        }

                        var markup = "<div class='select2-result-repository clearfix'>" +
                                "<div class='select2-result-repository__avatar'><img src='" + repo.owner.avatar_url + "' /></div>" +
                                "<div class='select2-result-repository__meta'>" +
                                "<div class='select2-result-repository__title'>" + repo.full_name + "</div>";

                        if (repo.description) {
                            markup += "<div class='select2-result-repository__description'>" + repo.description + "</div>";
                        }

                        markup += "<div class='select2-result-repository__statistics'>" +
                                "<div class='select2-result-repository__forks'><i class='fa fa-flash'></i> " + repo.forks_count + " Forks</div>" +
                                "<div class='select2-result-repository__stargazers'><i class='fa fa-star'></i> " + repo.stargazers_count + " Stars</div>" +
                                "<div class='select2-result-repository__watchers'><i class='fa fa-eye'></i> " + repo.watchers_count + " Watchers</div>" +
                                "</div>" +
                                "</div></div>";

                        return markup;
                    }

                    function formatRepoSelection(repo) {
                        return repo.full_name || repo.text;
                    }

                    $("#add-product-model").modal('show');

                },
                dismiss_modal_product: function () {
                    $("#add-product-model").modal('hide');
                    // this.poup_edit = false;
                },
                popup_modal_baoxau: function (id) {
                    self = this;
                    $("#baoxau-model").modal('show');

                },
                dismiss_modal_baoxau: function () {
                    $("#baoxau-model").modal('hide');
                    // this.poup_edit = false;
                },
                popup_modal_info: function (page_id, post_id) {
                    // alert(post_id);
                    self = this;
                    $.ajax({
                        type: 'POST',
                        url: base_url + 'app/chat/getPostFacebook',
                        data: {page_id: page_id, post_id: post_id},
                        dataType: "json",
                        success: function (json) {
                            //console.log(json);
                            self.conversation = json;
                        }
                    });
                    $("#info-model").modal('show');

                },
                dismiss_modal_info: function () {
                    $("#info-model").modal('hide');
                    // this.poup_edit = false;
                },
                message_template_click: function (val) {
                    if (this.show_message_template == true) {
                        this.show_message_template = false;
                    } else {
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
                    // function autoJoin() {
                    $.ajax({
                        type: 'POST',
                        url: base_url + 'app/chat/autoJoin',
                        dataType: "json",
                        success: function (json) {
                            socket.emit('room_join', json);
                        }
                    });
                    // },
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
            //if (localStorage.getItem("noti_browser") > 0) {
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
        /* .full-chat-w .full-chat-left .element-search:before {
          speak: none;
          font-style: normal;
          font-variant: normal;
          text-transfer: none;
          line-height: 1;
          -webkit-font-smoothing: antialiased;
          -moz-osx-font-smoothing: grayscale;
          position: absolute;
          left: 15px;
          top: 48%;
          -webkit-transfer: translateY(-50%);
          transfer: translateY(-50%);
          font-size: 20px;
          color: rgba(0, 0, 0, 0.2);
          font-family: 'Font Awesome 5 Free';
          font-weight: 900;
          content: "\f002";

        } */
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
        /* .full-chat-w .full-chat-left .user-list .user-w:hover .user-name {
            color: #fff
        } */
        /* .full-chat-w .full-chat-left .user-list .user-w:hover .last-message {
            color: rgba(255, 255, 255, 0.5)
        } */
        /* .full-chat-w .full-chat-left .user-list .user-w:hover .user-date {
            background-color: #046fdf;
            color: rgba(255, 255, 255, 0.3)
        } */
        .full-chat-w .full-chat-left .user-list .user-w.active {
            background-color: #F2F5F7;
        }
        .full-chat-w .full-chat-middle {
            /*background-image: url(../../assets/images/background_chat.png);
            background-size: 200px 200px;*/
            background-image: url(../../assets/images/bg_chat_admin/01.jpg);
            background-size: 600px 400px;
            width: 75%;
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


        /*.full-chat-w .chat-content-w .chat-message.bubble-left .chat-message-content-w:before {
            content: "";
            display: block;
            position: relative;
            top: 21px;
            left: 17px;
            height: 13px;
            width: 13px;
            background: #fff9f0;
            z-index: 100;
            -webkit-transfer: rotate(-45deg);
            -moz-transfer: rotate(-45deg);
            -o-transfer: rotate(-45deg);
            transfer: rotate(-45deg);
        }*/

        /*.full-chat-w .chat-content-w .chat-message.bubble-right .chat-message-content-w:before {
            content: "";
            display: block;
            position: relative;
            top: 9px;
            float: right;
            right: 29px;
            height: 13px;
            width: 13px;
            background: #f0f9ff;
            z-index: 100;
            -webkit-transfer: rotate(-45deg);
            -moz-transfer: rotate(-45deg);
            -o-transfer: rotate(-45deg);
            transfer: rotate(-45deg);
        }*/

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
            /*  -webkit-box-shadow: 0px 0px 0px 10px #fff;
              box-shadow: 0px 0px 0px 10px #fff*/
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
            /*display: -webkit-box;*/
            /*display: -ms-flexbox;*/
            /*display: flex;*/
            /*-webkit-box-pack: justify;*/
            /*-ms-flex-pack: justify;*/
            /*justify-content: space-between;*/
            /*-webkit-box-align: center;*/
            /*-ms-flex-align: center;*/
            /*align-items: center;*/
            /*margin-bottom: -6px;*/
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
            /*border-bottom: 1px solid rgba(0, 0, 0, 0.1);*/
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
    </style>
</router-view>
</div> <!--/#page-content-->
<!-- <div class="sbzon" style="z-index:9999999999;position: fixed; bottom: 0px; right: 10px;overflow: hidden; width: 400px; height: 477px;">
  <iframe id="sbzon_frame" width="100%" height="100%" frameborder="0"  src="http://192.168.16.45:8989/worldfone4x/chatiframe/chat"></iframe>
</div> -->