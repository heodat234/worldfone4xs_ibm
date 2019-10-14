<style>
/* The device with borders */
.smartphone {
  position: relative;
  width: 270px;
  height: 480px;
  margin: auto;
  border: 16px black solid;
  border-top-width: 60px;
  border-bottom-width: 60px;
  border-radius: 36px;
}

/* The horizontal line on the top of the device */
.smartphone:before {
  content: '';
  display: block;
  width: 60px;
  height: 5px;
  position: absolute;
  top: -30px;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #333;
  border-radius: 10px;
}

/* The circle on the bottom of the device */
.smartphone:after {
  content: '';
  display: block;
  width: 35px;
  height: 35px;
  position: absolute;
  left: 50%;
  bottom: -65px;
  transform: translate(-50%, -50%);
  background: #333;
  border-radius: 50%;
}

/* The screen (or content) of the device */
.smartphone .content {
  width: 238px;
  height: 360px;
  background: transparent;
}

#call-button {
  width: 50px;
  position: absolute;
  left: 40%;
    bottom: 20px;
}
#phone-input {
  width: 100%; 
  text-align: center; 
  font-size: 22px;
  z-index: 99;
  position: absolute;
  top: 3px;
  border: 0;
  background-color: transparent;
}
#phone-pad {
  width: 238px;
  position: absolute;
  left: 0px;
  top: 0px;
  z-index: 2;
}

#phone-history {
  width: 238px;
  height: 360px;
  overflow-y: auto;
  overflow-x: hidden;
  position: absolute;
  left: 0px;
  top: 0px;
  z-index: 4;
  padding: 5px;
}
.history-call-btn {
  position: absolute; 
  bottom: 20px; 
  left: 30px; 
  z-index: 2; 
  width: 30px; 
  color: gray
}

.history-call-btn:hover {
  color: aqua;
  cursor: pointer;
}

.numpad-btn:hover {
  color: aqua;
  cursor: pointer;
}

.historytable {
    background-color: white;
    padding: 5px;
}

.historytable > tbody > tr > td {
    padding: .4em 1em .3em 0;
    text-align: left;
    font-size: 1em;
    font-weight: lighter;
    color: #787878;
    border-bottom: 1px solid #e1e1e1;
}
</style>
<div class="container-fluid" style="min-height: 95vh">
  <div class="row" style="padding-top: 20px">
    <div class="smartphone" id="phone-contain">
      <div class="content" data-bind="visible: numpadVisible">
        <div class="form-group">
          <input class="form-control" data-bind="value: phone, events: {change: changePhoneInput}" id="phone-input">
        </div>
      </div>
      <img data-bind="visible: numpadVisible" id="phone-pad" src="<?= STEL_PATH . 'img/phone-pad.png' ?>" usemap="#phonepad">
      <map name="phonepad">
        <area alt="Button call" title="Call" href="javascript:void(0)" coords="124,343,32" shape="circle" data-bind="click: call">
        <area alt="Button 1" title="1" href="javascript:void(0)" coords="51,76,32" shape="circle" data-bind="click: number">
        <area alt="Button 2" title="2" href="javascript:void(0)" coords="126,75,32" shape="circle" data-bind="click: number">
        <area alt="Button 3" title="3" href="javascript:void(0)" coords="200,74,32" shape="circle" data-bind="click: number">
        <area alt="Button 4" title="4" href="javascript:void(0)" coords="52,143,32" shape="circle" data-bind="click: number">
        <area alt="Button 5" title="5" href="javascript:void(0)" coords="126,145,32" shape="circle" data-bind="click: number">
        <area alt="Button 6" title="6" href="javascript:void(0)" coords="200,146,32" shape="circle" data-bind="click: number">
        <area alt="Button 7" title="7" href="javascript:void(0)" coords="52,210,32" shape="circle" data-bind="click: number">
        <area alt="Button 8" title="8" href="javascript:void(0)" coords="126,210,32" shape="circle" data-bind="click: number">
        <area alt="Button 9" title="9" href="javascript:void(0)" coords="200,211,32" shape="circle" data-bind="click: number">
        <area alt="Button #" title="#" href="javascript:void(0)" coords="199,277,32" shape="circle" data-bind="click: number">
        <area alt="Button 0" title="0" href="javascript:void(0)" coords="125,279,32" shape="circle" data-bind="click: number">
        <area alt="Button *" title="*" href="javascript:void(0)" coords="51,278,32" shape="circle" data-bind="click: number">
      </map>
      <div style="position: absolute; bottom: 20px; right: 30px; z-index: 3;" data-bind="visible: numpadVisible">
        <img style="width: 30px; cursor: pointer;" src="<?= STEL_PATH . 'img/backspace.png' ?>" data-bind="click: backspace, visible: phone">
      </div>
      <i class="fa fa-clock-o fa-2x history-call-btn" data-bind="click: showHistoryCall, visible: numpadVisible"></i>
      <div id="phone-history" data-bind="invisible: numpadVisible">
        <div class="btn-group btn-group-xs" style="margin: 6px 7px 8px">
            <button class="btn btn-alt btn-default" data-bind="click: allCall, css: {active: allCallActive}">@All@</button>
            <button class="btn btn-alt btn-default" data-bind="click: missCall, css: {active: missCallActive}">@Misscall@</button>
        </div>
        <i class="fa fa-calculator pull-right numpad-btn" style="font-size: 18px; margin-top: 9px; margin-right: 10px" data-bind="click: showNumpad, invisible: numpadVisible"></i>
        <div>
          <table class="historytable">
            <tbody data-template="phonecall-history-template" data-auto-bind="false"
                 data-bind="source: dataSource"></tbody>
            <tfoot data-bind="visible: visibleShowMore">
              <tr>
                <td colspan="4" class="text-center" style="padding: 5px 0 7px"><a href="javascript:void(0)" data-bind="click: showMore">@Show more@</a></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script id="phonecall-history-template" type="text/x-kendo-template">
  <tr>
    <td>
      # if(typeof direction != 'undefined' && typeof callduration != 'undefined') { if(direction == "outbound") { #
        <i class="gi gi-unshare text-success" data-role="tooltip" title="Call Out" style="vertical-align: 0"></i>
      # } else if(direction == "inbound" && !callduration) { #
        <i class="gi gi-share text-danger" data-role="tooltip" title="Misscall" style="vertical-align: 0"></i>
      # } else { #
        <i class="gi gi-share text-info" data-role="tooltip" title="Call In" style="vertical-align: 0"></i>
      # } } #
    </td>
    <td style="text-align: left; width: 140px">
      <a href="javascript:void(0)" data-bind="text: customernumber, click: callHistoryPhone" title="Call now"></a>
    </td>
    <td>
      <span data-bind="text: callduration"></span><span>s</span>
    </td>
    <td style="text-align: right">
      <span data-role="tooltip" title="#: kendo.toString(new Date(starttime*1000), "dd/MM/yy") #" style="cursor: default">#if(typeof starttime != 'undefined'){##:kendo.toString(new Date(starttime*1000), "H:mm:ss")##}#</span>
    </td>
  </tr>
</script>
<script type="text/javascript">
  $("#phone-input").focus().on("keyup", function(e) {
    if(e.keyCode == 13)
    {
      phoneObservable.phone = $(this).val();
      phoneObservable.call();
    } else playSound("btn-click");
  });

  var clipboard = document.body.dataset.clipboard;
  var phoneObservable = {
    phone: !isNaN(clipboard) ? clipboard : "",
    changePhoneInput: function() {
      $("#phone-input").focus();
      playSound("btn-click");
    },
    number: function(e) {
      var number = e.currentTarget.title;
      this.set("phone", this.get("phone") + number);
      this.changePhoneInput();
    },
    backspace: function() {
      var phone = this.get("phone");
      this.set("phone", phone.substr(0, phone.length -1) );
      this.changePhoneInput();
    },
    numpadVisible: true,
    showHistoryCall: function() {
      this.set("numpadVisible", false);
      this.allCall();
    },
    showNumpad: function() {
      this.set("numpadVisible", true);
    },
    dataSource: new kendo.data.DataSource({
      serverFiltering: true,
      serverPaging: true,
      pageSize: 10,
      filter: {field: "userextension", operator: "eq", value: ENV.extension},
      transport: {
        read: ENV.restApi + "cdr",
        parameterMap: parameterMap
      },
      schema: {
        data: "data",
        total: "total",
      }
    }),
    allCallActive: false,
    missCallActive: false,
    allCall: function() {
      if(!this.get("allCallActive")) {
        this.dataSource.filter([
          {field: "userextension", operator: "eq", value: ENV.extension}
        ]);
        this.set("allCallActive", true);
        this.set("missCallActive", false);
        this.dataSource.read().then(() => {
          this.checkShowMore()
        });
      }
    },
    missCall: function() {
      if(!this.get("missCallActive")) {
        this.dataSource.filter([
          {field: "glide_extension", operator: "eq", value: ENV.extension},
          {field: "direction", operator: "eq", value: "inbound"},
          {field: "callduration", operator: "eq", value: 0}
        ]);
        this.set("allCallActive", false);
        this.set("missCallActive", true);
        this.dataSource.read().then(() => {
          this.checkShowMore()
        });
      }
    },
    visibleShowMore: false,
    checkShowMore() {
      var total     = this.dataSource.total(),
          pageSize  = this.dataSource.pageSize();
      if(total > pageSize) this.set("visibleShowMore", true);
      else this.set("visibleShowMore", false);
    },
    showMore: function() {
      this.dataSource.pageSize(this.dataSource.pageSize() + 10);
      setTimeout(() => {
        this.checkShowMore();
      }, 500);
    },
    callHistoryPhone: function(e) {
      makeCallWithDialog(e.currentTarget.textContent);
    },
    call: function() {
        makeCall(this.phone);
        playSound("dial");
    }
  }
  kendo.bind("#right-form", kendo.observable(phoneObservable));
</script>