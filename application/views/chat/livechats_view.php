<div id="pagecontent">
	<div class="settings">	
		<div class="settings-header clearfix">
			<div class="settings-container">
				<div class="row">
					<div class="col-md-8">
						<div class="settings-intro">
							<h2>Live chat</h2>
							<p>Tạo live chat cho từng website của bạn.</p>
						</div>
					</div>
					<div class="col-md-4">
						<a href="#" tabindex="-1" class="">               
							<button type="button" v-on:click="popup_modal('')" class="btn btn-primary f-right"><i class="fa fa-plus"></i> Thêm live chat</button>
						</a>            
					</div>
				</div>
			</div>
		</div>
		<div class="settings-body">
			<div class="settings-container">
				<div class="listContainer">
					<div class="head clearfix">
						<div class="col-md-3">Tên live chat</div>
						<div class="col-md-4">Website Url</div>
						<div class="col-md-2">Action</div>
					</div>
					<div class="list-row clearfix" v-for="livechat in livechats">
						<div class="col-md-3 truncate">
							<a v-on:click="popup_modal(livechat._id.$id)" class="highlight-lin ">{{livechat.page_info.name}}</a>
							<p style=" padding: 0; margin: 0; ">{{livechat.group_name}}</p>
						</div>
						<div class="col-md-4 ">{{livechat.page_info.website_url}}</div>
						<div class="col-md-3">
							<button type="button" v-on:click="getIframe(livechat._id.$id)" class="btn btn-primary btn-view-code" href="#view-iframe-model" data-toggle="modal" ><i class="fa fa-code"></i></button>
							<!-- <router-link :to="{ name: 'livechat_config', params: {id: livechat._id.$id } }" class="btn btn-primary" style="margin-left: 10px;">Interface Edit</router-link> -->

							<a v-on:click="livechat_config_click(livechat._id.$id)" class="btn btn-primary" style="margin-left: 10px;">Interface Edit</a>
						</div>
						<div class="col-md-2 actionBtns">                 
							<a class="action-link last" v-on:click="deleteLivechat(livechat._id.$id)"><i class="far fa-trash-alt"></i> Delete</a>
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


		<div role="dialog" class=" modal fade " id="add-official-model">
			<div class="modal-dialog modal-md">
				<div class="modal-content">
					<form class=" ui-form">  
						<div class="modal-header">
							<span class="modal-title"> {{poup_edit ? 'Sửa live chat' : 'Thêm live chat'}}</span>
							<button class="close" aria-label="Close" type="button">
								<span v-on:click="dismiss_modal"><i class="fas fa-times"></i></span>
							</button>
						</div>
						<div class="modal-body">
							<div class="ui-field form-field-required">    
								<label class=" control-label">Tên live chat</label>
								<div>
									<input v-model="name" autofocus="" placeholder="Tên live chat" autocomplete="on" type="text" class="form-control" autofocus>
								</div>
							</div>
							<div class="ui-field form-field-required" >   
								<label class=" control-label">Mô tả</label>
								<div>
									<input v-model="description" autofocus="" placeholder="Mô tả" type="text" class="form-control">
								</div>
							</div>
							<div class="ui-field form-field-required" >    
								<label class=" control-label">Website Url</label>
								<div>
									<input v-model="website_url" autofocus="" placeholder="Url website mà bạn muốn đặt chat box" type="text" class="form-control">
								</div>
							</div>
						</div>

						<div class="modal-footer">
							<span class="action-icons">
								<button type="button" class="btn-default btn" v-on:click="dismiss_modal"><span>Cancel</span></button>
								<button type="button" class="btn-primary btn" v-on:click="poup_edit ? edit_livechat() : add_livechat()"><span>{{poup_edit ? 'Cập nhật' : 'Lưu'}}</span></button>
							</span>
						</div>

					</form>
				</div>
			</div>
		</div>

	<div role="dialog" class=" modal fade " id="view-iframe-model">
		<div class="modal-dialog modal-lg">
			<div class="modal-content clearfix">
				<form class=" ui-form"> 
					<div class="modal-header">
						<span class="modal-title"> Tích hợp chat vào website</span>
						<button aria-label="Close" type="button" class="close" aria-hidden="true" data-dismiss="modal">
							<span><i class="fas fa-times"></i></span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-sm-12">
								<b style=" margin: 0px 0px 10px 0px; display: inline-block; ">Bước 1: Dán đoạn mã này trong &lt;HEAD&gt;</b>
							</div>
							<div class="col-sm-12">								
								<div style="background: #ffffff; overflow:auto;width:auto;border:solid gray;border-width:.1em .1em .1em .8em;"><pre style="margin: 0; line-height: 125%">&lt;script src=<span style="color: #dd2200; background-color: #fff0f0">&quot;{{base_url}}assets/js/livechat/widget.js&quot;</span>&gt;&lt;/script&gt;
</pre></div>

							</div>
							<div class="col-sm-12">
								<b style=" margin: 30px 0px 10px 0px; display: inline-block; ">Bước 2: Dán đoạn mã này trước &lt;/BODY&gt;</b>
							</div>
							<div class="col-sm-12">
								<div style="background: #ffffff; overflow:auto;width:auto;border:solid gray;border-width:.1em .1em .1em .8em;">
									<pre style="margin: 0; line-height: 125%">&lt;script&gt;
    <span style="color: #003388">window</span>.oscWidget.init({
        token: <span style="color: #dd2200; background-color: #fff0f0">&quot;{{poup_edit}}&quot;</span>,
    });
&lt;/script&gt;
</pre>
</div>

							</div>
							<div class="col-sm-12">
								<b style=" margin: 30px 0px 10px 0px; display: inline-block; ">(Không bắt buộc) Xác định người dùng Live chat</b>
							</div>
							<div class="col-sm-12">
								<div style="background: #ffffff; overflow:auto;width:auto;border:solid gray;border-width:.1em .1em .1em .8em;"><pre style="margin: 0; line-height: 125%">&lt;script&gt;
    <span style="color: #888888">//Set Name</span>
    <span style="color: #003388">window</span>.oscWidget.user.setName(<span style="color: #dd2200; background-color: #fff0f0">&#39;John&#39;</span>);

    <span style="color: #888888">//Set Phone</span>
    <span style="color: #003388">window</span>.oscWidget.user.setPhone(<span style="color: #dd2200; background-color: #fff0f0">&#39;0366446777&#39;</span>);

    <span style="color: #888888">//Set Email</span>
    <span style="color: #003388">window</span>.oscWidget.user.setEmail(<span style="color: #dd2200; background-color: #fff0f0">&#39;john.doe@gmail.com&#39;</span>);

    <span style="color: #888888">//Set Address</span>
    <span style="color: #003388">window</span>.oscWidget.user.setAddress(<span style="color: #dd2200; background-color: #fff0f0">&#39;Los angeles&#39;</span>);

    <span style="color: #888888">//Set Properties</span>
    <span style="color: #003388">window</span>.oscWidget.user.setProperties({
      plan: <span style="color: #dd2200; background-color: #fff0f0">&quot;Estate&quot;</span>,                 <span style="color: #888888">// meta property 1</span>
      status: <span style="color: #dd2200; background-color: #fff0f0">&quot;Active&quot;</span>                <span style="color: #888888">// meta property 2</span>
    });
&lt;/script&gt;
</pre>
</pre></div>


							</div>

						</div>
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
		data: function() {
			return {
				base_url: base_url,
				name: '',
				description: '',
				website_url: '',
				group_data:[],
				poup_edit: '',
				group_id: '',
				livechats:[],
				iframe_text: '',
			}
		},
		created() {
			this.getlivechats();
		},
		methods : {
			livechat_config_click: function(id){
				window.location.href = "<?php echo base_url('app/livechat_config'); ?>?id="+id;				
			},	
			getlivechats: function(){
				self = this;
				$.ajax({
					type: 'GET',
					url: base_url + 'app/livechats/getlivechats',
					dataType: "json",
					success: function (json) {
						self.livechats = json;
					},
				});					
			},	
			add_livechat: function(){
				self = this;
				//if (!this.$v.name.$invalid) {
					$.ajax({
						type: 'POST',
						url: base_url + 'app/livechats/addlivechat',
						data:{name: self.name, description: self.description, website_url: self.website_url, group_id: self.group_id},
						dataType: "json",
						success: function (json) {
							self.reset_form();
							self.dismiss_modal();
							self.getlivechats();
						},
					});
					// this.$v.$reset();					
				//}
			},
			edit_livechat: function(){
				self = this;
				//if (!this.$v.name.$invalid) {
					$.ajax({
						type: 'POST',
						url: base_url + 'app/livechats/editlivechat',
						data:{id: self.poup_edit, name: self.name, description: self.description, website_url: self.website_url, group_id: self.group_id},
						dataType: "json",
						success: function (json) {
							self.reset_form();
							self.dismiss_modal();
							self.getlivechats();
						},
					});
					// this.$v.$reset();					
				//}
			},
			deleteLivechat: function(id){
				self = this;
				if (confirm('Bạn có muốn xóa Groups Account này?')) {
					$.ajax({
						type: 'POST',
						url: base_url + 'app/livechats/deletelivechat',
						data:{id:id},
						dataType: "json",
						success: function (json) {
							self.getlivechats();
						},
					});
				}
			},
			getIframe: function(id){
				self = this;
				this.poup_edit = id;
				/*$.ajax({
					type: 'GET',
					url: base_url + 'app/livechats/codeView',
					dataType: "html",
					data:{id:id},
					success: function (html) {

						//$('#view-iframe-model .modal-body').html(html);
						// self.iframe_text = json;
					},
				});	*/				
			},	
			popup_modal: function(id){	
				self = this;			
				if (id=='') {
					this.poup_edit = false;
					self.reset_form();
					$('.select_group').val([]);
					$('.select_group').trigger('change');
				}else{
					this.poup_edit = id;
					$.ajax({
						type: 'GET',
						url: base_url + 'app/livechats/getlivechat',
						dataType: "json",
						data: {id:id},
						success: function (json) {
							self.name = json['page_info']['name'];
							self.website_url = json['page_info']['website_url'];
							self.description = json['page_info']['description'];
							$('.select_group').val(json['group_id']);
							$('.select_group').trigger('change');
						},
					});		
				}
				$("#add-official-model").modal('show');	
			},
			dismiss_modal: function(){
				$("#add-official-model").modal('hide');
				this.poup_edit = false;
			},
			reset_form: function(){
				self.name = '';
				self.description = '';
				this.website_url = '';
				$('.select_group').val([]);
				$('.select_group').trigger('change');
			}
		},
		mounted() {
			/*if (document.getElementById('my-datatable')) return;
			var scriptTag = document.createElement("script");
			scriptTag.src = "https://zjs.zdn.vn/zalo/sdk.js";
			scriptTag.id = "my-datatable";
			document.getElementsByTagName('head')[0].appendChild(scriptTag);*/
			var self = this;
			$('.select_group').select2({
			}).on('change', function () {
				self.group_id = $(this).val();
			});
		},
		
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