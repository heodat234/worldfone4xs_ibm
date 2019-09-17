
<div id="pagecontent">
    <div class="settings">	
        <div class="settings-header clearfix">
            <div class="settings-container">
                <div class="row">
                    <div class="col-md-8">
                        <div class="settings-intro">
                            <h2>Fanpage Manager</h2>
                            <p>Các Fanpage khi được kích hoạt, tin nhắn và comment từ FanPage sẽ được hiển thị trên giao diện chat để nhân viên có thể hổ trợ trực tiếp từ ứng dụng</p>
                        </div>
                    </div>
                    <div class="col-md-4">           
                            <button type="button" v-on:click="syncFanpage" class="btn btn-primary f-right"><i class="fa fa-plus"></i> Đồng bộ FanPage</button>           
                    </div>
                </div>
            </div>
        </div>
        <div class="settings-body">
            <div class="settings-container">
                <div class="listContainer">
                    <div class="head clearfix">
                        <div class="col-md-5">Tên Fanpage</div>
                        <div class="col-md-4">Nhóm hổ trợ</div>
                        <div class="col-md-2">Nguồn</div>
                        <!-- <div class="col-md-3">&nbsp;</div> -->
                    </div>
                    <div class="list-row clearfix" v-for="page in pages">
                        <div class="col-md-5 truncate">
                            <a v-on:click="popup_modal(page._id.$id)" class="highlight-lin" style="font-size:15px">
                                <img width="64" height="64" class="img-rounded" :src="page.picture"> 
                                {{page.name}}
                            </a>
                        </div>
                        <div class="col-md-4" style="color: #455A64;font-size: 14px;">{{page.group_name}}</div>
                        <div class="col-md-2" style="color: #455A64;font-size: 14px;">{{page.source}}</div>
                    </div>
                    <div class="list-end">
                        <span class="line"></span>
                        <span class="circle"></span>
                        <span class="line"></span>
                    </div>
                </div>
            </div>
        </div>
        <div role="dialog" class=" modal fade " id="add-fpage-model">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <form class=" ui-form">  
                        <div class="modal-header">
                            <span class="modal-title"> {{poup_edit ? 'Sửa FanPage' : 'Thêm FanPage'}}</span>
                            <button class="close" aria-label="Close" type="button">
                                <span v-on:click="dismiss_modal"><i class="fa fa-times"></i></span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="ui-field form-field-required" >    
                                <label class=" control-label">Tên FanPage</label>
                                <div>
                                    <input v-model="name" autofocus="" placeholder="Tên FanPage" autocomplete="on" type="text" class="form-control" disabled>
                                </div>
                            </div>
                            <div class="ui-field form-field-required" >    
                                <label class=" control-label">Nhóm hổ trợ</label>
                                <div>
                                    <select  class="form-control" v-model="group_mn">
                                        <option v-for="group in group_manager" :value='group._id.$id' >{{group.name}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <span class="action-icons">
                                <button type="button" class="btn-default btn" v-on:click="dismiss_modal"><span>Cancel</span></button>
                                <button type="button" class="btn-primary btn" v-on:click="poup_edit ? editfanpage() : addfpage()"><span>{{poup_edit ? 'Cập nhật' : 'Lưu'}}</span></button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        <!-- End dialog -->
    </div><!-- End ./settings -->
</div>
<script>
    new Vue({
        el: "#pagecontent",
        data: function () {
            return {
                name: '',
                group_mn: '',
                status: '',
                pages: [],
                group_manager: [],
                poup_edit: '',
            }
        },
        created() {
            this.get_pages();
            this.getchatgroup_manager();
        },
        methods: {
            getchatgroup_manager: function () {
                self = this;
                $.ajax({
                    type: 'GET',
                    url: ENV.baseUrl + 'app/fanpage/getChatGroupManager',
                    dataType: "json",
                    success: function (json) {
                        console.log(json);
                        self.group_manager = json;
                    },
                });
            },
            get_pages: function () {
                self = this;
                $.ajax({
                    type: 'GET',
                    url: ENV.baseUrl + 'app/fanpage/getFanpages',
                    dataType: "json",
                    success: function (json) {
                        self.pages = json;
                    },
                });
            },

            editfanpage: function () {
                self = this;
                $.ajax({
                    type: 'POST',
                    url: ENV.baseUrl + 'app/fanpage/editFanpage',
                    data: {id: this.poup_edit, group_mn: this.group_mn},
                    dataType: "json",
                    success: function (json) {
                        self.dismiss_modal();
                        self.get_pages();
                    },
                });
            },
            popup_modal: function (id) {
                self = this;
                if (id == '') {
                    this.poup_edit = false;
                    this.name = '';
                    this.oa_id = '';
                    this.oa_secret = '';
                } else {
                    this.poup_edit = id;
                    $.ajax({
                        type: 'GET',
                        url: ENV.baseUrl + 'app/fanpage/getFanpage',
                        dataType: "json",
                        data: {id: id},
                        success: function (json) {
                            self.name = json['name'];
                            self.group_mn = json['group_id'];
                        },
                    });
                }
                $("#add-fpage-model").modal('show');
            },
            dismiss_modal: function () {
                $("#add-fpage-model").modal('hide');
                this.poup_edit = false;
            },
            syncFanpage: function () {
                self = this;
                if (confirm('Bạn có chắc muốn đồng bộ Fanpage?')) {

                    $.ajax({
                        type: 'POST',
                        url: ENV.baseUrl + 'app/fanpage/syncFanpage',
                        dataType: "json",
                        beforeSend: function () {
                            $('.lds-dual-wap').show();
                        },
                        complete: function () {
                            $('.lds-dual-wap').hide();
                        },
                        success: function (json) {
                            alert("Đồng bộ thành công");
                            self.get_pages();
                        },
                    });

                }
            },

        },

    })
</script>
<style type="text/css">
    #pagecontent {
        background-color: #F2F5F7;
        min-height: 500px;
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
</style>
