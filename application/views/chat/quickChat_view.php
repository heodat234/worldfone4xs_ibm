<div id="pagecontent">
    <div class="settings">

        <div class="settings-header clearfix">
            <div class="settings-container">
                <div class="row">
                    <div class="col-md-8">
                        <div class="settings-intro">
                            <h2>Trả lời nhanh</h2>
                            <p>Giúp nhân viên tư vấn trả lời nhanh các câu hỏi của khách theo các mẫu đã đặt sẵn.</p>
                        </div>
                    </div>
                    <div class="col-md-4">            
                        <button type="button" v-on:click="popup_modal('')" class="btn btn-primary f-right"><i class="fa fa-plus"></i>  Thêm mẫu câu</button>      
                    </div>
                </div>
            </div>
        </div>
        <div class="settings-body">
            <div class="settings-container">
                <div class="listContainer">
                    <div class="head clearfix">
                        <div class="col-xs-10">Mẫu câu</div>
                    </div>
                    <div class="list-row clearfix" v-for="quick in quickChat">
                       
                        <span style="line-height: 30px" v-html="htmlTemplate(quick.maucau)" class="col-xs-10"></span>
                         <!--<input id="mauCauId" v-model="quick._id.$id" type="hidden">-->
                        <div class="col-md-2  actionBtns">
                            <a v-on:click="popup_modal(quick._id.$id,quick.maucau)" class="action-link last"><i class="fa fa-edit">Edit</i></a>
                            <a @click="deleteQuickChat(quick._id.$id)" class="action-link last"><i class="fa fa-trash"></i> Delete</a>
                        </div>

                    </div>
                    <div class="list-end">
                        <span class="line"></span>
                        <span class="circle"></span>
                        <span class="line"></span>
                    </div>
                </div>
            </div>
        </div>
        <!--Popup modal-->
        <div role="dialog" class=" modal fade " id="add-fpage-model">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <form class=" ui-form">  
                        <div class="modal-header">
                            <span class="modal-title"> {{poup_edit ? 'Cập Nhật Mẫu Hội Thoại' : 'Thêm Mẫu Hội Thoại Mới'}}</span>
                            <button class="close" aria-label="Close" type="button">
                                <span v-on:click="dismiss_modal"><i class="fa fa-times"></i></span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="ui-field">
                                <form class="ui-form">
                                    <div class="row">
                                        <textarea id="quickChatMessage" rows="7" class="col-xs-12 form-control" v-model="quickChatMess" required placeholder="Nhập câu trả lời"></textarea>
                                    </div>
                                    <hr />
                                    <div class="row">
                                        <div v-for="rowValue in quickChatDynamicValue" style="margin-bottom: 15px" class="col-xs-12">
                                            <div class="row">
                                                <div class="col-xs-4">
                                                    <div style="padding: 5px; border: solid 1px #2980B9; width: fit-content;"><span @click="insertQuickChatDynamicValue(rowValue.value)" class="quick-chat-dynamic-value span-quickchat-value" style="border: none!important;">{{rowValue.value}}</span></div>
                                                </div>
                                                <span class="col-xs-8">Chèn {{rowValue.text}} của khách hàng</span>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <span class="action-icons">
                                <button type="button" class="btn-default btn" data-dismiss="modal"><span>Cancel</span></button>
                                <button v-if="isUpdateMauCau === true" type="button" class="btn-primary btn" v-on:click="updateQuickChat()"><span>Cập nhật</span></button>
                                <button v-else type="button" class="btn-primary btn" v-on:click="insertQuickChat()"><span>Thêm mới</span></button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--Popup modal-->

    </div><!-- End ./settings -->


</div>

<script>
    $.fn.insertAtCaret = function (myValue) {
        return this.each(function () {
            //IE support
            if (document.selection) {
                this.focus();
                sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
            }
            //MOZILLA / NETSCAPE support
            else if (this.selectionStart || this.selectionStart == '0') {
                var startPos = this.selectionStart;
                var endPos = this.selectionEnd;
                var scrollTop = this.scrollTop;
                this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
                this.focus();
                this.selectionStart = startPos + myValue.length;
                this.selectionEnd = startPos + myValue.length;
                this.scrollTop = scrollTop;
            } else {
                this.value += myValue;
                this.focus();
            }
        });
    };
    new Vue({
        el: "#pagecontent",
        data: function () {
            return {
                quickChatDynamicValue: [],
                quickChatMess: '',
                isUpdateMauCau: false,
                content: '',
                quickChat: [],
                poup_edit: '',
            }
        },
        created() {
            this.get_quickChat();
            this.getQuickChatDynamicValue();
        },
        methods: {
            get_quickChat: function () {
                self = this;
                $.ajax({
                    type: 'GET',
                    url: ENV.baseUrl + 'app/Quickchat/getquickChat',
                    dataType: "json",
                    success: function (json) {
                        self.quickChat = json;
                    },
                });
            },
            getQuickChatDynamicValue() {
                let self = this;
                $.ajax({
                    url: ENV.baseUrl + "app/Quickchat/getQuickChatDynamicValue",
                    dataType: 'json',
                    contentType: 'application/json; charset=UTF-8',
                    success: function (response) {
                        self.quickChatDynamicValue = response.value;
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            },

            insertQuickChatDynamicValue(value) {
                // Author: https://gist.github.com/sdkester/676394#file-gistfile1-js
                $("#quickChatMessage").insertAtCaret('{{' + value.toUpperCase() + '}}');
                return false
                // Author: https://gist.github.com/sdkester/676394#file-gistfile1-js
            },

            insertQuickChat() {
                self = this;
                console.log(self.quickChatMess);
                $.ajax({
                    type: 'POST',
                    url: ENV.baseUrl + "app/Quickchat/insertIntoQuickChat",
                    dataType: 'json',
                    data: {'maucau': self.quickChatMess},
                    success: function (json) {
                        alert('Thêm mới mẫu câu thành công.');
                        $("#add-fpage-model").modal('hide');
                        self.get_quickChat();
                        self.isUpdateMauCau = false;

                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            },
            updateQuickChat() {
                self = this;
                let isUpdate = confirm("Bạn có muốn thay đổi mẫu câu này?");
                if (isUpdate === true) {
                    $.ajax({
                        type: 'POST',
                        url: ENV.baseUrl + "app/Quickchat/updateQuickChat",
                        dataType: 'json',
                        data: {_id: this.popup_edit, 'maucau': $("#quickChatMessage").val()},
                        
                        success: function (json) {
                            alert('cập nhật mẫu câu thành công.');
                            $("#add-fpage-model").modal('hide');
                            self.get_quickChat();
                            self.isUpdateMauCau = false;
                        },
                        error: function (error) {
                            console.log(error);
                        },
                       
                    });
                  
                }
            },

            deleteQuickChat(_id) {
                self = this;
                let isDelete = confirm("Bạn có muốn xóa mẫu câu này?");
                if (isDelete == true) {
                    $.ajax({
                        type: 'POST',
                        url: ENV.baseUrl + "app/Quickchat/deleteQuickChat",
                        dataType: 'json',
                        data: {_id: _id},
                        success: function (json) {
                                alert('Xóa mẫu câu thành công.');
                             self.get_quickChat();
                                
                        },
                        error: function (error) {
                            console.log(error);
                        }
                    });
                }
            },
            popup_modal(id,maucau) {
                this.popup_edit =id;
                if (maucau) {
                    this.quickChatMess = maucau;
                    this.isUpdateMauCau = true;
                    console.log(this.isUpdateMauCau);
                } else {
                    this.quickChatMess = '';
                    this.isUpdateMauCau = false;
                }
                $("#add-fpage-model").modal('show');
            },
            dismiss_modal: function () {
                $("#add-fpage-model").modal('hide');
                this.poup_edit = false;
            },

            htmlTemplate(rawString) {
                let newTxt = rawString.split('{{');
                let htmlStringReturn = rawString;
                for (let i = 1; i < newTxt.length; i++) {
                    htmlStringReturn = htmlStringReturn.replace('{{' + newTxt[i].split('}}')[0] + '}}', '<span class="quick-chat-dynamic-value">' + newTxt[i].split('}}')[0] + '</span>');
                }
                return htmlStringReturn;
            },

        },
    });
</script>
<style type="text/css">
    #pagecontent {
        background-color: #F2F5F7;
        min-height: 560px;
    }
    .settings {
        padding: 20px;
    }
    .modal-title {
        font-size: 14px;
        font-weight: 700;
        position: relative;
        color: #1F2B36;
        text-transform: uppercase;
    }
    .modal-content .modal-body label {
        text-transform: capitalize;
        float: left;
        font-size: 13px;
        display: block;
        width: 100%;
        text-align: left;
        font-weight: 600;
        line-height: 1;
        color: black;
    }
    a {
        cursor: pointer;
    }
    .settings-header {
        width: 100%;
        background-color: #F8F9FB;
        padding: 20px 50px;
        box-shadow: 0 0 4px rgba(0,0,0,.12);
        z-index: 10;
        position: relative;
    }
    .settings-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .settings-intro {
        margin-left: 84px;
    }
    .settings-intro h2 {
        font-size: 18px;
        margin: 0 0 5px;
        font-weight: 600;
        color: #1F2B36;
        text-transform: uppercase;
    }
    .settings-intro p {
        font-size: 14px;
        color: #455A64;
        margin: 0;
        line-height: 1.3;
    }
    .settings-body {
        padding: 30px 50px;
    }
    .listContainer .head {
        padding: 0 10px 10px;
        font-size: 13px;
        font-weight: 600;
        text-align: left;
        color: #7c818b;
        text-transform: uppercase;
        margin: 0;
    }
    .listContainer .list-row {
        background: #FFF;
        padding: 8px 10px;
        border-radius: 3px;
        margin: 0 0 4px;
        position: relative;
    }
    .truncate{
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }
    /* .settings */ .thumb-box h4.title {
        font-size: 14px;
        text-transform: uppercase;
        font-weight: 700;
    }
    /* .settings */ .thumb-box .thumbs {
        text-align: center;
        float: left;
        margin: 8px;
        background: #FFF;
        border-radius: 5px;
        width: 150px;
        min-height: 150px;
        cursor: pointer;
        box-shadow: 0 2px 2px rgba(36,37,38,.08);
        transition: box-shadow .25s ease,transform .25s ease;
    }
    .thumb-box .thumbs p {
        padding-top: 16px;
        color: #2C3B48;
        font-size: 11px;
        text-transform: uppercase;
        line-height: 1;
    }
    .thumb-box .thumbs .link-content-icon {
        padding-top: 18px;
    }
    .thumb-box .thumbs .link-content-icon i{
        font-size: 80px;
        height: 80px;
        color: #7ebece
    }
</style>
<style type="text/css">
    /* .settings */ .thumb-box h4.title {
        font-size: 14px;
        text-transform: uppercase;
        font-weight: 700;
    }
    /* .settings */ .thumb-box .thumbs {
        text-align: center;
        float: left;
        margin: 8px;
        background: #FFF;
        border-radius: 5px;
        width: 150px;
        min-height: 150px;
        cursor: pointer;
        box-shadow: 0 2px 2px rgba(36,37,38,.08);
        transition: box-shadow .25s ease,transform .25s ease;
    }
    /* .settings */ .thumb-box .thumbs p {
        padding-top: 16px;
        color: #2C3B48;
        font-size: 11px;
        text-transform: uppercase;
        line-height: 1;
    }
    /* .settings */ .thumb-box .thumbs .link-content-icon {
        padding-top: 18px;
    }
    .thumb-box .thumbs .link-content-icon i{
        font-size: 80px;
        height: 80px;
        color: #7ebece
    }

    .settings-container {
        max-width: none !important;
    }

    .quick-chat-dynamic-value {
        padding: 4px 4px!important;
        color: #2980B9;
        border: solid 1px #2980B9;
        background-color: #ffffff;
    }

    .span-quickchat-value {
        text-transform: uppercase
    }

    .span-quickchat-value:hover {
        cursor: pointer;
    }
</style>