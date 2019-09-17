<div id="page-container">
	<div id="pagecontent">
		<div class="settings">	

			<div class="settings-header clearfix">
				<div class="settings-container">
					<div class="row">
						<div class="col-md-8">
							<div class="settings-intro">
								<h2>Nhóm Support</h2>
								<p>Mỗi thành viên sẽ thuộc một nhóm nào đó, ví dụ nhóm Hổ trợ, Nhóm tư vẩn sản phẩm...Các thành viên trong nhóm sẽ nhận được thông báo từ những Page họ theo dõi.</p>
							</div>
						</div>

						<div class="col-md-4">
							<a tabindex="-1" class="">               
								<button type="button" v-on:click="popup_modal('')" class="btn btn-primary f-right"><i class="fa fa-plus"></i> Thêm nhóm</button>
								
							</a>            
						</div>
					</div>
				</div>
			</div>
			<div class="settings-body">
				<div class="settings-container">
					<div class="listContainer">
						<div class="head clearfix">
							<div class="col-md-4">Tên Nhóm</div>
							<div class="col-md-3">Supervisor</div>
							<div class="col-md-3">Thành viên</div>
							<!-- <div class="col-md-3">&nbsp;</div> -->
						</div>
						<div class="list-row clearfix" v-for="group in groups">
							<div class="col-md-4 truncate"><a v-on:click="popup_modal(group._id.$id)" class="highlight-lin ">{{group.name}}</a></div>
							<div class="col-md-3 "><span class="label label-success">{{group.supervisor}}</span></div>
							<div class="col-md-3">
								<span v-for="agent in group.agents" class="label label-success" style="margin-right: 5px;">{{agent}}</span>
							</div>
							<div class="col-md-2 actionBtns">                 
								<a class="action-link last" v-on:click="deleteGroups(group._id.$id)"><i class="far fa-trash-alt"></i> Delete</a>
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
								<span class="modal-title"> {{poup_edit ? 'Sửa nhóm' : 'Thêm nhóm'}}</span>
								<button class="close" aria-label="Close" type="button">
									<span v-on:click="dismiss_modal"><i class="fas fa-times"></i></span>
								</button>
							</div>
							<div class="modal-body">
								<div class="ui-field form-field-required">    
									<label class=" control-label">Tên Nhóm</label>
									<div>
										<input v-model="name" autofocus="" placeholder="Tên Nhóm" autocomplete="on" type="text" class="form-control" autofocus>
									</div>
								</div>
								<div class="ui-field form-field-required" >   
									<label class=" control-label">Mô tả</label>
									<div>
										<input v-model="description" autofocus="" placeholder="Mô tả" autocomplete="on" type="text" class="form-control">
									</div>
								</div>
								<div class="ui-field form-field-required" >   
									<label for="groupDescription" class=" control-label">Supervisor</label>
									<div>
										<select id="supervisor" v-model="supervisor" data-placeholder="Chọn supervisor" class="form-control">
											<option value=''>Chọn supervisor</option>
											<?php foreach ($agents as $supervisor) {
												echo '<option value="' . $supervisor['extension'] . '">' . $supervisor['agentname'] . '(' . $supervisor['extension'] . ')</option>';										
											}
											?>
										</select>
									</div>
								</div>
								<div class="ui-field form-field-required" >   
									<label for="groupDescription" class=" control-label">Thành viên</label>
									<div>
										<select id="agent" v-model="agents"  class="select-chosen" data-placeholder="Chọn agent" style="width: 250px;" multiple>
											<?php foreach ($agents as $agent) {
												echo '<option value="' . $agent['extension'] . '">' . $agent['agentname'] . '(' . $agent['extension'] . ')</option>"';
											}

											?>
										</select>
									</div>
								</div>
							</div>

							<div class="modal-footer">
								<span class="action-icons">
									<button type="button" class="btn-default btn" v-on:click="dismiss_modal"><span>Cancel</span></button>
									<button type="button" class="btn-primary btn" v-on:click="poup_edit ? edit_groups() : add_groups()"><span>{{poup_edit ? 'Cập nhật' : 'Lưu'}}</span></button>
								</span>
							</div>

						</form>
					</div>
				</div>
			</div>
			<!-- End dialog -->
		</div><!-- End ./settings -->
	</div>
</div>
<script>
	var base_url = ENV.baseUrl;
	new Vue({
        el: "#pagecontent",
		data: function() {
			return {
				name: '',
				description: '',
				supervisor: '',
				agents: [],
				users_data:[],
				poup_edit: '',
				groups:[],
			}
		},
		created() {
			this.getGroups();
			this.get_users();
		},
		methods : {
			getGroups: function(){
				self = this;
				$.ajax({
					type: 'GET',
					url: base_url + 'app/Chat_group_manager/getGroups',
					dataType: "json",
					success: function (json) {
						self.groups = json;
					},
				});					
			},	
			get_users: function(){
				self = this;
				$.ajax({
					type: 'GET',
					url: base_url + 'customers/users/getuserAccounts',
					dataType: "json",
					success: function (json) {
						self.users_data = json;
					},
				});					
			},
			add_groups: function(){
				self = this;
				if (this.name!='' || this.supervisor !='') {
					console.log($('#agent').val());
					$.ajax({
						type: 'POST',
						url: base_url + 'app/Chat_group_manager/addGroup',
						data: {name: this.name, description: this.description, supervisor: this.supervisor, agents: $('#agent').val()},
						dataType: "json",
						success: function (json) {
							self.description = '';
							//self.users = '';
							$('#supervisor').val();
							$('#agent').val();
							self.name = '';
							self.dismiss_modal();
							self.getGroups();
						},
					});
					//this.$v.$reset();					
				}else{
					alert('Kiểm tra các trường trống');
				}
			},
			edit_groups: function(){
				self = this;
				if (this.name!='' || this.supervisor !='') {
					$.ajax({
						type: 'POST',
						url: base_url + 'app/Chat_group_manager/editGroup',
						data: {id:this.poup_edit, name: this.name, description: this.description, supervisor: this.supervisor, agents: $('#agent').val()},
						dataType: "json",
						success: function (json) {
							self.description = '';
							self.users = '';
							self.name = '';
							self.dismiss_modal();
							self.getGroups();
						},
					});
					//this.$v.$reset();					
				}else{
					alert('Kiểm tra các trường trống');
				}
			},
			deleteGroups: function(id){
				self = this;
				if (confirm('Bạn có muốn xóa Groups Account này?')) {
					$.ajax({
						type: 'POST',
						url: base_url + 'app/Chat_group_manager/deleteGroups',
						data:{id:id},
						dataType: "json",
						success: function (json) {
							self.getGroups();
						},
					});
				}
			},
			popup_modal: function(id){	
				self = this;
				if (id=='') {
					this.poup_edit = false;
					this.name = '';
					this.description = '';
					this.get_users();
					// $('.select_users').val([]);
					// $('.select_users').trigger('change');
					self.supervisor = '';
					self.agents = [];
					$('#supervisor').val();
					$('#agent').val();
					setTimeout(function(){
						$('#agent').trigger('chosen:updated');
					}, 50);
				}else{
					this.poup_edit = id;
					$('#agent').trigger('chosen:updated');
					$.ajax({
						type: 'GET',
						url: base_url + 'app/Chat_group_manager/getGroup',
						dataType: "json",
						data: {id:id},
						success: function (json) {
							self.name = json['name'];
							self.description = json['description'];
							self.supervisor = json['supervisor'];
							self.agents = json['agents'];
							var optionsToSelect = json['agents'];
							var select = document.getElementById('agent');

							for ( var i = 0, l = select.options.length, o; i < l; i++ ){
								o = select.options[i];
								if ( optionsToSelect.indexOf( $(o).val()) != -1 )
								{
									$(o).attr({
										selected: true,
									});
								}
							}
							setTimeout(function(){
								$('#agent').trigger('chosen:updated');
							}, 50);
							
						},
					});		
				}
				$("#add-official-model").modal('show');
				
			},
			dismiss_modal: function(){
				$("#add-official-model").modal('hide');
				this.poup_edit = false;
			},
		},
		mounted() {
			/*if (document.getElementById('my-datatable')) return;
			var scriptTag = document.createElement("script");
			scriptTag.src = "https://zjs.zdn.vn/zalo/sdk.js";
			scriptTag.id = "my-datatable";
			document.getElementsByTagName('head')[0].appendChild(scriptTag);*/
			/*var self = this;
			$('.select_users').select2({
			}).on('change', function () {
				self.users = $(this).val();
			});*/
		},

		
	});

</script>
<style type="text/css">
    #pagecontent {
        background-color: #F2F5F7;
        min-height: 570px;
        //  margin-left: 62px;
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