<div id="header">
    <header class="navbar navbar-default navbar-fixed-top">
      <ul class="nav navbar-nav-custom pull-right ">
        <li class="dropdown noti-box">
          <a v-on:click="loadnotifications" data-toggle="dropdown" class="dropdown-toggle"><i class="fa fa-bell fa-lg"></i></a>
          <ul class="dropdown-menu dropdown-custom dropdown-menu-right">
            <li class="noti-content">
              <div v-for="notification in notifications" class="alert alert-success alert-alt btn-create-room-invite" v-on:click="click_notification(notification.id)">
                <img :src="notification.avatar" alt="">
                <div>{{notification.name}}: {{notification.text}}</div>
                <small>{{notification.date_added}}</small>
              </div>
            </li>

          </ul>
        </li>
          <li class="dropdown">
            <a href="javascript:void(0)" data-toggle="dropdown" aria-expanded="false" class="dropdown-toggle"><img :src="user.profile_pic" :alt="user.name" :title="user.name" /> {{user.name}}  <i class="fa fa-angle-down"></i></a> <ul class="dropdown-menu dropdown-custom dropdown-menu-right">
              <li>
              </li>
              <li class="divider"></li>
              <li>
                <a :href="user.logoutKey"><i class="fa fa-ban fa-fw pull-right"></i> Thoát</a>
              </li>
            </ul>
          </li>
        </ul>
      </header>
</div>

<script type="text/javascript">
Vue.component('hearder', {
  module.exports = {
    data: function() {
          return {
              user: '',
              notifications: [],

          }
    },
    beforeCreated(){

    },
    created(){
      setTimeout(() => {
        this.loadinfo();
      }, 500);

    },
    mounted() {
      function request_invite_box(){
        $.ajax({
          contentType: "application/json; charset=utf-8",
          type: 'POST',
          url: base_url+'customers/chat/ajaxGetNewNotify',
          dataType: "json",
          success: function(data) {
            $( ".noti-box .label-indicator" ).remove();
            if(data.length > 0){
              $( ".noti-box a.dropdown-toggle" ).append('<span class="label label-danger label-indicator animation-pulse ">'+data.length+'</span> ');
            }
          }
        });
      }

      setInterval(
        function () {
          request_invite_box();
        }, 2000
        );
    },
    methods:{
      loadinfo:function(){
        self = this;
        $.ajax({
          type: 'GET',
          url: base_url + 'customers/users/getInfo',
          dataType: "json",
          success: function (json) {
            self.user = json;
          },
        });
      },
      click_test: function(){
        this.$emit('noti_test1',{aa:"12345"});

        // this.noti_test = "okhaby1";
        // sconsole.log('da click');
      },
      loadnotifications: function(){
        self = this;
        $.ajax({
          type: 'GET',
          url: base_url + 'customers/chat/ajaxGetNewNotify',
          dataType: "json",
          success: function (json) {
            self.notifications = json;
          },
        });
      },
      click_notification: function(id){
        self = this;
        $.ajax({
          type: 'POST',
          url: base_url + 'customers/chat/redirectNotify',
          data: {id: id},
          dataType: "json",
          success: function (json) {
            if (json['data_emit']) {
              socket.emit('notification', json['data_emit']);
              console.log(json['data_emit']);
            }
            if (json['redirect']) {
              window.location.href = json['redirect'];
            }
          }
        });
      },

    }
  }
});
</script>