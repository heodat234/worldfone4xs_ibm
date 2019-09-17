<div id="pagecontent">
	<div class="settings">	
		<div class="settings-header clearfix">
			<div class="settings-container">
				<div class="row">
					<div class="col-md-8">
						<div class="settings-intro">
							<h2>{{name}}</h2>
							<p v-if="description!='false'">{{description}}</p>
						</div>
					</div>
					<div class="col-md-4">
						<a href="#" tabindex="-1" class="">               
							<button type="button" v-on:click="submit_form" class="btn btn-primary f-right"><i class="fa fa-plus"></i> Cập nhật</button>
						</a>            
					</div>
				</div>
			</div>
		</div>
		<div class="settings-body">
			<div class="panel-body">
				<div class="col-sm-4">
					<select class="form-control change-windown">
						<option value="windown-suvey">Cửa sổ survey</option>
						<option value="windown-ready-chat">Cửa sổ chat</option>
					</select>
					<div class="windown-item windown-suvey">
						<div class="content-box" style="">
							<div class="full-chat-w">
								<div class="offline_heading" >
									<!-- style="background-color: <?php echo $chat_web_surver_info['color']; ?> -->
									<label class="button_chat_offline_text" data-follow="title_survey_heading">{{survey_texts.title_survey_heading}}</label>
									<div class="minimize"><i class="minimize-icon"></i></div>
								</div>
								<div class="form_header" style="">
									<p data-follow="title_instruction_text" v-html="survey_texts.title_instruction_text"></p>
								</div>
								<div class="form-suvey" style="">									
									<div class="form-suver-content">
										<div class="form-control2 name">
											<p>Họ tên</p> <input type="text">
										</div>
										<div class="form-control2 email">
											<p>Email</p> <input type="text">
										</div>
										<div class="form-control2 phone">
											<p>Số điện thoại</p> <input type="text">
										</div>
										<div :class="item.id" class="form-control2" v-for="(item, key) in survey_texts.data_field" v-if="key>2">
											<p>{{item.field_name}}</p>
											<input type="text"> 
										</div>
									</div>
									<button type="button" class="btn_start_chat" data-follow="title_begin_chat">{{survey_texts.title_begin_chat}}</button> 

								</div><!--/.form-suvey--> 
							</div>
						</div>
					</div><!--/.windown-suvey-->
					<div class="windown-item windown-ready-chat" style="display:none">
						<div class="content-box" style=""> <div class="full-chat-w"> 
							<div class="offline_heading"> <label class="button_chat_offline_text" data-follow="title_ready_text">{{survey_texts.title_ready_text}}</label>
								<div class="minimize"><i class="minimize-icon"></i></div> 
							</div> 
							<div class="operator-info" style=""> <img src="" height="56" width="56" class="avatar"> 
								<span class="operator-name" data-follow="title_agentname_text">{{survey_texts.title_agentname_text}}</span>
								<span class="operator-role" data-follow="title_ready_ask_us_text">{{survey_texts.title_ready_ask_us_text}}</span>
							</div> 
							<div class="full-chat-i" style=""> <div class="full-chat-middle" style=" overflow: auto; "> 		
								<div class="chatbox chatbox" data-room-id="" data-next="0" data-pre="0">

									<div class="chat-content-w ps ps--theme_default scroll1">
										<div class="chat-content">	
										</div>
										<div class="ps__scrollbar-x-rail" style="left: 0px; bottom: 0px;">
											<div class="ps__scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div>
										</div>
										<div class="ps__scrollbar-y-rail" style="top: 0px; right: 0px;">
											<div class="ps__scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
										</div>
									</div>
									<div class="box-load-bottom">
										<button type="button" class="btn-load-bottom btn btn-primary btn-sm">Tải thêm...</button>
									</div>
									<div class="box-searh-single">
										<span>Tìm kiếm</span> <input type="text" name=""> <!-- <button type="button" class="btn-next"><i class="fa fa-angle-up"></i> Cũ hơn</button> <button type="button" class="btn-pre"><i class="fa fa-angle-down"></i> Mới hơn</button>  --> <a title="Hủy tìm kiếm" class="btn-remove-box-search"><i class="fa fa-times-circle-o "></i></a>
									</div>
									<div class="chat-controls">
										<div class="chat-input"><textarea data-follow="title_ready_enter_text" :placeholder="survey_texts.title_ready_enter_text"></textarea></div>
										<div class="chat-input-extra">
											<div class="chat-extra-actions">
												<a class="btn-upload"><i class="fa fa-file-text-o" aria-hidden="true"></i></a>
											</div>
											<div class="chat-btn" data-room-id=""><a class="btn btn-primary btn-sm" href=""><i class="fa fa-paper-plane-o"></i></a></div>
										</div>
									</div>
								</div><!--/.chatbox-->
								</div><!--/.full-chat-middle--> </div> 
								<div class="form-suvey" style="display: none;"> 
									<div class="form-control"> <p>Tên</p> <input type="text" class="v_name"> </div> 
									<div class="form-control"> <p>Số điện thoại</p> <input type="text" class="v_telephone"> </div>
									<button type="button" class="btn_start_chat">Bắt đầu trò chuyện</button> 
							</div><!--/.form-suvey--> </div> 
						</div>
					</div><!--/.windown-ready-chat-->
				</div><!--/.col-sm-4-->
				<form method="post" id="ajaxfieldform">
					<div class="col-sm-4">
						<h4>Thế nào là Form khảo sát?</h4>
						<h5>Form khảo sát giúp bạn thu thập thông tin về visitor trước khi chat.</h5>
						<div class="survey-form">
							<input type="hidden" name="livechat_id" value="<?php echo $livechat_id ?>">
							<div v-if="survey_texts.onoff_surver==1">
								<input type="checkbox" name="onoff_surver" checked> Bật Form khảo sát
							</div>
							<div v-else>
								<input type="checkbox" name="onoff_surver" > Bật Form khảo sát
							</div>

								<ul class="check-rq">
									<li v-for="(item, key) in survey_texts.data_field" v-if="key<3">
										<input type="checkbox" :name="'surver_field['+ key +'][check]'" value="1" class="check-field-item check-field-item-main" :data-class="item.id" v-model="item.check"> {{item.field_name}}
										<div class="pull-right">
											<div class="btn-check btn_check_require" :class="{'not-required' : item.require==0}">
											<input type="text" :name="'surver_field['+ key +'][require]'" check_reset="1" style="display: none" :value="item.require">
											<i class="fa fa-check"></i> Bắt buộc</div>
										</div>
										<input type="hidden" :name="'surver_field['+ key +'][id]'" :value="item.id">
										<input type="hidden" :name="'surver_field['+ key +'][field_name]'" :value="item.field_name">
									</li>
									<!--  -->
									<li v-for="(item, key) in survey_texts.data_field" v-if="key>2">
										<input type="checkbox" :name="'surver_field['+ key +'][check]'" value="1" class="check-field-item" checked style="display:none">
										<input type="text" :name="'surver_field['+ key +'][field_name]'" v-model="item.field_name" class="form-control more-item"> 
										<div class="pull-right">
											<div class="btn-check btn_check_require" :class="{'not-required' : item.require==0}">
												<input type="text" :name="'surver_field['+ key +'][require]'" check_reset="1" style="display: none" :value="item.require">
												<i class="fa fa-check"></i> Bắt buộc
											</div>
											<i class="fa fa-trash"></i>
										</div>
										<input type="hidden" :name="'surver_field['+ key +'][id]'" :value="item.id">
									</li>
								</ul>
								<div class="box-btn-more-field">
									<button type="button" class="btn btn-success btn-more-field pull-right"><i class="fa fa-plus"></i> Thêm trường</button> 
								</div>

							</div>
					</div><!--/.col-sm-4-->
					<div class="col-sm-4">
							<div class="color-picker-wrap">
								<a class="change_color" v-on:click="change_color('#1687c5')"><i class="choosecolor color1"></i></a>
								<a class="change_color" v-on:click="change_color('#449d02')"><i class="choosecolor color2"></i></a>
								<a class="change_color" v-on:click="change_color('#da4a38')"><i class="choosecolor color3"></i></a>
								<a class="change_color" v-on:click="change_color('#ffba00')"><i class="choosecolor color4"></i></a>
								<a class="change_color" v-on:click="change_color('#7c25d6')"><i class="choosecolor color5"></i></a>
								<input type="text" id="picker" name="color" :value="survey_texts.color"  style="border-color: rgb(22, 136, 197);">
								<!-- <?php echo $chat_web_surver_info['color']; ?>-->
							</div>
							<div class="custom-language">
								<h4>Tùy chỉnh</h4>
								<input type="text" class="text windown-suvey-text form-control" placeholder="Widget title" name="title_survey_heading" v-model="survey_texts.title_survey_heading">
								<input type="text" class="text windown-suvey-text instruction_text form-control" placeholder="Instruction" name="title_instruction_text" v-model="survey_texts.title_instruction_text">
								<input type="text" class="text windown-suvey-text form-control" placeholder="Widget title" name="title_begin_chat" v-model="survey_texts.title_begin_chat">

								<input type="text" class="text windown-ready-chat-text title_text_input form-control" placeholder="Widget title" name="title_ready_text" v-model="survey_texts.title_ready_text">
								<input type="text" class="text windown-ready-chat-text name_supporter_input form-control" placeholder="Agent name" name="title_agentname_text" v-model="survey_texts.title_agentname_text">
								<input type="text" class="text windown-ready-chat-text ask_us_text form-control" placeholder="Ask us anything" name="title_ready_ask_us_text" v-model="survey_texts.title_ready_ask_us_text">
								<input type="text" class="text windown-ready-chat-text message_online_text form-control" placeholder="Type your message here" name="title_ready_enter_text" v-model="survey_texts.title_ready_enter_text">
								<input type="hidden" class="text windown-ready-chat-text message_welcome_text form-control" placeholder="Welcome message" name="title_ready_welcome_text" v-model="survey_texts.title_ready_welcome_text">
								<input type="hidden" class="text windown-ready-chat-text message_busy_text form-control" placeholder="Busy message" name="title_ready_busy_text" v-model="survey_texts.title_ready_busy_text">
							</div>
						<!-- </form> -->
					</div><!--/.col-sm-4-->
				</form>
			</div>

		<!-- settings-body -->

	
<!-- End dialog -->
</div><!-- End ./settings -->


</div>
</div>
<script src="/assets/js/bootstrap-colorpicker.js"></script>
<script>
	new Vue({
        el: "#pagecontent",
		data: function() {
			return {
				id: '<?php echo $livechat_id ?>',
				name: '',
				description: '',
				website_url: '',
				group_data:[],
				poup_edit: '',
				group_id: '',
				livechats:[],
				survey_texts: [],
				row_item: 0,
			}
		},
		created() {
			self = this;
			// setTimeout(function(){
				self.getlivechat();
				self.getChatSurvey();
			// },1000);			

		},
		methods : {
			getlivechat: function(){
				self = this;
				var id = self.id;
				$.ajax({
					type: 'GET',
					url: base_url + 'app/livechats/getlivechat',
					dataType: "json",
					data: {id:id},
					success: function (json) {
						self.name = json['name'];
						self.website_url = json['website_url'];
						self.description = json['description'];
						$('.select_group').val(json['group_id']);
						$('.select_group').trigger('change');
					},
				});						
			},
			getChatSurvey: function(){
				self = this;
				var id = self.id;
				$.ajax({
					type: 'GET',
					url: base_url + 'app/livechats/getChatSurvey',
					dataType: "json",
					data: {id:id},
					success: function (json) {
						self.survey_texts = json;
						self.row_item += json.data_field.length;
						// $("#picker").value();
						// $("#picker").trigger('change');
					},
				});						
			},
			getGroups: function(){
				self = this;
				$.ajax({
					type: 'GET',
					url: base_url + 'customers/groups/getGroups',
					dataType: "json",
					success: function (json) {
						self.group_data = json;
					},
				});					
			},
			add_livechat: function(){
				self = this;
				if (!this.$v.name.$invalid) {
					$.ajax({
						type: 'POST',
						url: base_url + 'customers/groups/addGroup',
						data:{name: self.name, description: self.description, users: self.users},
						dataType: "json",
						success: function (json) {
							self.reset_form();
							self.dismiss_modal();
							self.getlivechats();
						},
					});
					this.$v.$reset();					
				}
			},
			edit_livechat: function(){
				self = this;
				if (!this.$v.name.$invalid) {
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
					this.$v.$reset();					
				}
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
							self.name = json['name'];
							self.website_url = json['website_url'];
							self.description = json['description'];
							$('.select_group').val(json['group_id']);
							$('.select_group').trigger('change');
						},
					});		
				}
				$("#add-official-model").modal('show');	
			},
			dismiss_modal: function(){
				self = this;
				$("#add-official-model").modal('hide');
				this.poup_edit = false;
			},
			reset_form: function(){
				self = this;
				self.name = '';
				self.description = '';
				this.website_url = '';
				$('.select_group').val([]);
				$('.select_group').trigger('change');
			},
			submit_form: function(){
				$('#ajaxfieldform').trigger('submit');
			},
			change_color:function(color){
				self = this;
				self.survey_texts.color = color;
				$("#picker").val(color);
				$("#picker").css({'border-color':color}); 
				$("#picker").trigger('change');
			}
		},
		mounted() {
			var self = this;
			
			// console.log(this.$route.params.id);
			// $('#picker').colorpicker();
			
			$('.select_group').select2({
			}).on('change', function () {
				self.group_id = $(this).val();
			});

			function myRandom() {
				return Math.floor((Math.random() * 100000) + 1);
			}
			$(document).ready(function() {
				// var row_new = self.row_item;

				$('.btn-more-field').click(function(e) {
					var rand = myRandom();
					html = '';
					html += '<li class="item-'+rand+'"><input type="checkbox" name="surver_field['+self.row_item+'][check]" value="1" class="check-field-item" checked style="display:none"> <input type="text" name="surver_field['+self.row_item+'][field_name]" value="" placeholder="" class="form-control more-item">';
					html += '	<div class="pull-right">';
					html += '	<div class="btn-check btn_check_require">';
					html += '     <input type="text" name="surver_field['+self.row_item+'][require]" check_reset="1" style="display: none" value="1">';
					html += '       <i class="fa fa-check" aria-hidden="true"></i> Bắt buộc</div>';
					html += '<i class="fa fa-trash"></i>';
					html += '</div>';

					html += '    <input type="hidden" name="surver_field['+self.row_item+'][id]" value="'+rand+'">';
					html += '</li>';
					$('.survey-form ul').append(html);
					html1 = '';
					html1 += '<div data-class="item-'+rand+'" class="form-control2">';
					html1 += '	<p>Text</p>';
					html1 += '	<input type="text" class=""> ';
					html1 += '</div>';
					$('.form-suver-content').append(html1);
					self.row_item ++;
				});		
				$(document).on('click', '.btn_check_require', function(e) {
					$(this).toggleClass('not-required');
					// console.log($(this).hasClass('not-required'));
					if ($(this).hasClass('not-required')) {
						$(this).find('input').val(0);
					}else{
						$(this).find('input').val(1);
					}
				});

				$(document).on('click', '.check-rq li .fa-trash', function(e) {
					if (confirm("Bạn có muốn xóa Field này?")) {
						$(this).closest('li').remove();
						var class1 = $(this).closest('li').attr('class');
						$(document).find('[data-class="'+class1+'"]').remove();
					}
				});
				$(document).on('keyup', '.more-item', function(e) {
					var class1 = $(this).closest('li').attr('class');
					$(document).find('[data-class="'+class1+'"]').find('p').text($(this).val());
				});
				$(document).on('change', '.check-field-item-main', function(e) {
					class1 = $(this).attr('data-class');
					if ($(this).is(':checked')) {				
						$(document).find('.'+class1).closest('.form-control2').show();
					}else{
						$(document).find('.'+class1).closest('.form-control2').hide();
					}
				});
				$("#picker").change(function(e) {
					// $('.offline_heading').css({'background-color':'#449d02'});
					$('.offline_heading').css({'background-color':/*self.survey_texts.color*/$(this).val()});
					$('.btn_start_chat').css({'background-color':/*self.survey_texts.color*/$(this).val()});
					$("#picker").css({'border-color':$(this).val()}); 
				});
				$("#picker").trigger('change');

			});
			$(".change-windown").change(function(e) {
				$('.windown-item').hide();
				$('.text').hide();
				$('.'+$(this).val()).show();
				$('.'+$(this).val()+'-text').show();
			});
			$(".change-windown").trigger('change');
			$(function () {
				$('#picker').colorpicker();
			});
			function customize_laguage(val, id){
				$(document).find('[data-follow="'+id+'"]').text(val)
			}
		},
		validations: {
			/*name: {
				required,
				minLength: minLength(1)
			},
			group_id: {
				required,
				minLength: minLength(1)
			},
			website_url: {
				required,
				minLength: minLength(1)
			},*/
		},
		
	});
	$(document).ready(function() {
		$('#ajaxfieldform').submit(function(e) {
			e.preventDefault();
			$.ajax({
				type: 'POST',
				url: base_url + 'app/livechats/add_field',
				data: $('#ajaxfieldform').serialize(),
				dataType: "json",                
				success: function (json) {
					if (json['error']) {
						alert(json['error']);
					}else{
						alert('Lưu surver thành công!');
					}
				}
			}); 
		});
		$('#ajaxcolorcustomform').submit(function(e) {
			e.preventDefault();
			$.ajax({
				type: 'POST',
				url: base_url + 'chatnodejs/chat_survey/add_colorcustom',
				data: $('#ajaxcolorcustomform').serialize(),
				dataType: "json",                
				success: function (json) {
					if (json['error']) {
						alert(json['error']);
					}else{
						alert('Lưu surver thành công!');
					}
				}
			}); 
		});
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
.color1 {
	background-color: #1687c5;
}
.color2 {
	background-color: #449d02;
}
.color3 {
	background-color: #da4a38;
}
.color4 {
	background-color: #ffba00;
}
.color5 {
	background-color: #7c25d6;
}
#picker {
	width: 99px;
	border-radius: 3px;
	border: 1px solid #CCC;
	border-right: 28px solid #4a4a4a;
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
.check-rq li .fa-trash{
	margin-left: 10px;
	color: red;
	border: 1px solid;
	border-radius: 2px;
	padding: 2px 5px;
	cursor: pointer;
}
.survey-form ul li{
	list-style: none;
	padding: 10px 20px;
	border-bottom: 1px solid #DDD;
}

.survey-form ul .btn_check_require{
	/*float: right;*/
}
.survey-form .check-rq .btn-check {
	display: inline-block;
	cursor: pointer;
	background: #1aabe8;
	border-radius: 2px;
	height: 20px;
	line-height: 20px;
	color: #fff;
	padding: 0 5px;
	font-size: 11px;
}
.check-rq .btn-check.not-required {
	background: #b1b1b1;
	color: #777;
}
.survey-form .box-btn-more-field{
	margin-top: 10px;
}
.survey-form .more-item{
	width: 54%;
	display: inline-block;
}

.content-box {
	/* padding: 10px; */
	border-radius: 5px 5px 0 0;
	box-shadow: 0 2px 25px rgba(0,0,0,.2);
	background: #fff;
	/*position: fixed;*/
	/*width: 100%;*/
	/*bottom: 0;*/
}
.button_chat_offline {
  padding: 9px 16px 9px;
  color: #FFF;
  border-radius: 5px 5px 0 0;
  cursor: pointer;
  display: block;
  white-space: nowrap;
  float: right; 
  background-color: #1688c5;

}
.button_chat_offline_text i{
  padding-right:8px;
}
.minimize {
  padding: 8px;
  cursor: pointer;
  z-index: 1;
  float: right;

}
.minimize-icon {
  display: inline-block;
  width: 16px;
  height: 2px;
  background: #FFF;
}
.operator-info {
  background-size: auto;
  padding: 8px 10px;
  display:block;
  background-color:#f6f6f6;
  min-height: 74px;
}
.operator-info .avatar {
    float: left;
    margin-right: 15px;
    border-radius: 3px;
    background-clip: padding-box;
    padding: 1px;
    width: 58px;
    height: 58px;
    vertical-align: top;
}
.operator-name {
  color: #000;
  font-size: 16px;
  font-weight: 700;
}
.operator-role {
  margin-top: .25em;
  color: #000;
  font-size: 12px;
}
.operator-name, .operator-role {
    display: block;
    /*max-width: 180px;*/
}
.online_heading, .offline_heading {
	position: relative;
	width: 100%;
	padding: 6px 10px;
	color: #FFF;
	cursor: pointer;
	background-color: #da4b38;
	box-sizing: border-box;
	border-radius: 5px 5px 0 0;
}
.form_header {
	margin: 10px 10px 0px 10px;
}
.form-suvey {
	padding: 10px 30px 20px 30px;
}
.form_header p {
	/*color: #000;*/
	line-height: 20px;
	text-align: center;
	margin: 0;
}
.form-suvey .form-control2 {
	padding: 5px 0px;
}
.form-suvey .form-control2 p {
	padding: 5px 0;
	margin: 0px;
}
.form-suvey .form-control2 input {
	width: 100%;
	background-clip: padding-box;
	border: 1px solid #ababab;
	border-radius: 2px;
	padding: 6px 7px 8px 7px;
	margin: 0 auto;
	font-size: 14px;
	color: #333;
	font-family: 'Roboto', sans-serif;
	box-shadow: 0px 0px 3px #e4e4e4;
	box-sizing: border-box;
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	outline: none;
}
.form-suvey button {
	background-color: #1688c5;
	width: 70%;
	text-align: center;
	line-height: 35px;
	color: #FFF;
	display: inline-block;
	border-radius: 2px;
	font-weight: bold;
	font-size: 15px;
	margin: 20px 15% 3px 15%;
	text-decoration: none;
	border: none;
}
.full-chat-w .full-chat-middle .chat-head {
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
.full-chat-w .chat-content-w {
    height: 279px;
    position: relative;
    overflow-x: hidden;
    overflow-y: auto;
}
.scroll1::-webkit-scrollbar {
  width: 6px;
}

.scroll1::-webkit-scrollbar-track {
  background: #ddd;
}
 
.scroll1::-webkit-scrollbar-thumb {
  /*background: #666; */
  border-radius: 8px;
  /*border: 2px solid white;*/
  background-color: rgba(0, 0, 0, .5);
}
.full-chat-w .chat-content-w .chat-content {
    padding: 10px 0px;
    /*min-height: 600px*/
    overflow-y: hidden;
}
.full-chat-w .chat-content-w .chat-date-separator {
    text-align: center;
    color: rgba(0, 0, 0, 0.3);
    /*font-size: .81rem;*/
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
    position: relative
}
.full-chat-w .chat-content-w .chat-message {
    margin-bottom: 20px;
}
.full-chat-w .chat-content-w .chat-message.self .chat-message-content {
    background-color: #f0f9ff;
    color: #2A4E7F;
    margin-right: 20px;
    margin-left: 0px;
    word-wrap: break-word;
}
.full-chat-w .chat-controls {
    padding: 20px;
    padding-top: 0px;
    border-top: 1px solid rgba(0, 0, 0, 0.1)
}
.full-chat-w .chat-input textarea/* input[type="text"] */ {
    padding: 10px 0px 10px 0px;
    border: none;
    display: block;
    width: 100%;
    outline: none
}
.full-chat-w .chat-input-extra {
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
.full-chat-w .chat-input-extra .chat-extra-actions a {
    margin-right: 10px;
    display: inline-block
}
.btn-load-bottom{
  margin: 10px auto;
  display: none;    
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
.chat-input-extra{
	display:none !important;
}
</style>