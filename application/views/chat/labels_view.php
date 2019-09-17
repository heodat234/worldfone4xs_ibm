<div id="pagecontent">
    <div class="settings">	
        <div class="settings-header clearfix">
            <div class="settings-container">
                <div class="row">
                    <div class="col-md-8">
                        <div class="settings-intro">
                            <h2>Nhãn Hội Thoại</h2>
                            <p>Tạo nhãn hội thoại giúp bạn dễ dàng quản lý hội thoại, tạo sự tiện lợi và màu sắc cho cuộc hội thoại của bạn</p>
                        </div>
                    </div>
                    <div class="col-md-4">              
                        <button type="button" v-on:click="popup_modal('')" class="btn btn-primary f-right"><i class="fa fa-plus"></i>Thêm Nhãn Hội Thoại</button>  
                    </div>
                </div>
            </div>
        </div>
        <div class="settings-body">
            <div class="settings-container">
                <div class="listContainer">
                    <div class="head clearfix">
                        <div class="col-md-3">Tên nhãn</div>
                        <div class="col-md-2">Màu sắc</div>
                        <div class="col-md-5">Số thứ tự</div>
                        <!-- <div class="col-md-3">&nbsp;</div> -->
                    </div>
                    <div class="list-row clearfix" v-for="label in labels">
                        <div class="col-md-3 truncate"><a v-on:click="popup_modal(label._id.$id)">{{label.tennhan}}</a> </div>
                        <div class="col-md-2 ">
                            <div :style="{backgroundColor: label.mausac,color: label.mausac }"  style="width:20px;border-radius: 60%;">#</div>


                        </div>
                        <div class="col-md-5">{{label.stt}}</div>
                        <div class="col-md-2 actionBtns">                 
                            <a class="action-link last" v-on:click="deletelabel(label._id.$id)"><i class="fa fa-trash"></i> Delete</a>
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


        <div role="dialog" class=" modal fade " id="add-user-model">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <form class=" ui-form"  method="post" id="addLabel">  
                        <div class="modal-header">
                            <span class="modal-title"> {{poup_edit ? 'Sửa Nhãn Hội Thoại' : 'Thêm Nhãn Hội Thoại'}}</span>
                            <button class="close" aria-label="Close" type="button">
                                <span v-on:click="dismiss_modal"><i class="fa fa-times"></i></span>
                            </button>
                        </div>
                        <div class="modal-body">						
                            <div class="ui-field form-field-required">    
                                <label class=" control-label">Tên nhãn</label>
                                <div>
                                    <input v-model="tennhan" name="tennhan" placeholder="Tên nhãn" autocomplete="on" type="text" class="form-control" autofocus>
                                </div>
                            </div>
                            <div class="ui-field form-field-required">    
                                <label class=" control-label">Màu sắc</label>
                                <div>
                                    <div class="">
                                        <input type="text" v-on:change="change_color" id="picker" name="mausac" v-model="mausac" style="border-color: rgb(22, 136, 197);">

                                    </div>
                                </div>
                            </div>
                            <div class="ui-field form-field-required">    
                                <label class=" control-label">Số thứ tự</label>
                                <div>
                                    <input v-model="stt" name="stt" placeholder="STT" autocomplete="on" type="text" class="form-control" autofocus>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <span class="action-icons">
                                <button type="button" class="btn-default btn" v-on:click="dismiss_modal"><span>Cancel</span></button>
                                <button type="button" class="btn-primary btn" v-on:click="poup_edit ? editLabel() : addLabel()"><span>{{poup_edit ? 'Cập nhật' :'Lưu'}}</span></button>
                            </span>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <!-- End dialog  -->

    </div>

</div>

<script>
    new Vue({
        el: "#pagecontent",
        data: function () {
            return {
                tennhan: '',
                mausac: '',
                stt: '',
                labels: [],
                poup_edit: '',
                survey_texts: [],

            }
        },
        methods: {
            getLabels: function () {
                self = this;
                $.ajax({
                    type: 'GET',
                    url: ENV.baseUrl + 'app/labels/getLabels',
                    dataType: "json",
                    success: function (json) {
                        self.labels = json.data;
                    },
                });
            },
            addLabel: function () {
                self = this;
                // var bodyColor = self.rgb2hex($('#picker').css("border-color"));
                
                $.ajax({
                    type: 'POST',
                    url: ENV.baseUrl + 'app/labels/addLabel',
                    data: {tennhan: self.tennhan, mausac: $('#picker').val(), stt: self.stt},
                    dataType: "json",
                    success: function (json) {
                        if (json['error']) {
                            alert(json['error']);
                        }
                        if (json['success']) {
                            self.reset_form();
                            self.dismiss_modal();
                            self.getLabels();
                        }
                    },
                });
            },
            editLabel: function () {
                self = this;
                var bodyColor = $('#picker').css("border-color");
                $.ajax({
                    type: 'POST',
                    url: ENV.baseUrl + 'app/labels/editLabel',
                    data: {tennhan: self.tennhan, mausac: $('#picker').val(), stt: self.stt, id: self.poup_edit},
                    dataType: "json",
                    success: function () {
                        self.reset_form();
                        self.dismiss_modal();
                        self.getLabels();
                    },
                });
            },
            deletelabel: function (id) {
                self = this;
                if (confirm('Bạn có muốn xóa nhãn này?')) {
                    $.ajax({
                        type: 'POST',
                        url: ENV.baseUrl + 'app/labels/deleteLabel',
                        data: {id: id},
                        dataType: "json",
                        success: function (json) {
                            self.getLabels();
                        },
                    });
                }
            },
            reset_form: function () {
                this.tennhan = '';
                this.mausac = '';
                this.stt = '';
            },
            rgb2hex: function (rgb) {
                rgb = rgb.match(/^rgb?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
                return (rgb && rgb.length === 4) ? "#" +
                        ("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
                        ("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
                        ("0" + parseInt(rgb[3], 10).toString(16)).slice(-2) : '';
            },
            popup_modal: function (id) {
                self = this;
                if (id == '') {
                    this.poup_edit = false;
                    this.tennhan = '';
                    this.mausac = '';
                    this.stt = '';
                } else {
                    this.poup_edit = id;
                    $.ajax({
                        type: 'GET',
                        url: ENV.baseUrl + 'app/labels/getLabel',
                        dataType: "json",
                        data: {id: id},
                        success: function (json) {
                            self.tennhan = json['tennhan'];
                            document.getElementById("picker").style.borderColor = json['mausac'];
                            self.mausac = json['mausac'];
                            self.stt = json['stt'];
                        },
                    });
                }
                $("#add-user-model").modal('show');

            },
            dismiss_modal: function () {
                $("#add-user-model").modal('hide');
                this.poup_edit = false;
            },
            change_color: function () {
                self = this;
                self.survey_texts.color = self.mausac;
                console.log(self.mausac);
                $("#picker").val(self.mausac);
                $("#picker").css({'border-color': self.mausac});
                $("#picker").trigger('change');
            },
        },
        mounted() {
            this.getLabels();            
            $(document).ready(function () {

                $("#picker").change(function (e) {
                    $("#picker").css({'border-color': $(this).val()});

                });
                $("#picker").trigger('change');
                  var currentHex = '#0000ff';
                $('#picker').colorpicker({
                    color: currentHex,
                    onShow: function (colpkr) {
                        $(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        $(colpkr).fadeOut(500);
                        return false;
                    },
                    onChange: function (hsb, hex, rgb) {
                        currentHex = hex;
                        $('#picker').val = currentHex;
                    }
                });
            });
//            $('#picker').click(function () {
//                var hex = rgb2hex($('#picker').val());
//                //  alert(hex); 
//                $("#picker").html(hex);
//            });
            $(function () {
                $('#picker').colorpicker();

            });


        }

    })

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
    #picker {
        width: 189px;
        border-radius: 3px;
        border: 1px solid #CCC;
        border-right: 58px solid #4a4a4a;
        padding: 5px 10px!important;
    }
    .choosecolor {
        display: inline-block;
        vertical-align: top;
        width: 28px;
        height: 28px;
        margin-right: 8px;
        cursor: pointer;
    }

</style>