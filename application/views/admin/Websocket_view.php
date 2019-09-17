<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.2.0/socket.io.js"></script>
<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>Admin</li>
    <li>Websocket Client</li>
</ul>
<!-- END Table Styles Header -->
<div class="containter-fluid">
	<div class="row">
		<div class="col-sm-6 col-sm-offset-3">
			<h1 class="text-center">WebSocket Client test</h1>
			<!-- WebSocket Connection Parameters Table -->
			<table>
			    <tr>
			        <td width="200px">WS Protocol</td>
			        <td>
			            <select id="protocol" class="form-control">
			                <option value="ws" selected="selected">ws</option>
			                <option value="wss">wss</option>
			            </select>
			        </td>
			    </tr>
			    <tr>
			        <td>WS Hostname</td>
			        <td><input type="text" id="hostname" class="form-control"/></td>
			    </tr>
			    <tr>
			        <td>WS Port</td>
			        <td><input type="text" id="port" class="form-control"/></td>
			    </tr>
			    <tr>
			        <td>WS Endpoint</td>
			        <td><input type="text" id="endpoint" class="form-control"/></td>
			    </tr>
			    <tr>
			        <td></td>
			        <td>
			            <input id="btnConnect"    type="button" value="Connect"    onclick="onConnectClick()" class="btn btn-default">&nbsp;&nbsp;
			            <input id="btnDisconnect" type="button" value="Disconnect" onclick="onDisconnectClick()" disabled="disabled" class="btn btn-default">
			        </td>
			    </tr>
			</table><br/>
			<!-- Send Message Table -->
			<table>
			    <tr>
			        <td width="200px">Message</td>
			        <td><input type="text" id="message" class="form-control"/></td>
			    </tr>
			    <tr>
			        <td></td>
			        <td>
			            <input id="btnSend" type="button" value="Send Message" disabled="disabled" onclick="onSendClick()" class="btn btn-default">
			        </td>
			    </tr>
			</table><br/>
			<textarea id="incomingMsgOutput" class="form-control" rows="10" cols="20" disabled="disabled"></textarea>
		</div>
	</div>
</div>
<script type="text/javascript">
    var webSocket   = null;
var ws_protocol = null;
var ws_hostname = null;
var ws_port     = null;
var ws_endpoint = null;
/**
 * Event handler for clicking on button "Connect"
 */
function onConnectClick() {
    var ws_protocol = document.getElementById("protocol").value;
    var ws_hostname = document.getElementById("hostname").value;
    var ws_port     = document.getElementById("port").value;
    var ws_endpoint = document.getElementById("endpoint").value;
    openWSConnection(ws_protocol, ws_hostname, ws_port, ws_endpoint);
}
/**
 * Event handler for clicking on button "Disconnect"
 */
function onDisconnectClick() {
	console.log("openWSConnection::Disconnect");
	document.getElementById("btnSend").disabled       = true;
    document.getElementById("btnConnect").disabled    = false;
    document.getElementById("btnDisconnect").disabled = true;
    socket.destroy();
}
/**
 * Open a new WebSocket connection using the given parameters
 */
function openWSConnection(protocol, hostname, port, endpoint) {
    var webSocketURL = null;
    webSocketURL = protocol + "://" + hostname + ":" + port + endpoint;
    console.log("openWSConnection::Connecting to: " + webSocketURL);
    try {
    	var socket = window.socket = io(webSocketURL);
		  socket.on('connect', function(){
		  	document.getElementById("btnSend").disabled       = false;
            document.getElementById("btnConnect").disabled    = true;
            document.getElementById("btnDisconnect").disabled = false;
		  });
		  socket.on('chat message', function(wsMsg){
		  	if (wsMsg.indexOf("error") > 0) {
                document.getElementById("incomingMsgOutput").value += "error: " + wsMsg + "\r\n";
            } else {
                document.getElementById("incomingMsgOutput").value += "message: " + wsMsg + "\r\n";
            }
		  });
		  socket.on('disconnect', function(closeEvent){
		  	console.log(closeEvent);
		  });

		  socket.on('error', function(exception) {
			  console.log('SOCKET ERROR');
			  socket.destroy();
			})

			socket.on('close', function(exception) {
				document.getElementById("btnSend").disabled       = true;
            document.getElementById("btnConnect").disabled    = false;
            document.getElementById("btnDisconnect").disabled = true;
			  console.log('SOCKET CLOSED');
			})
    } catch (exception) {
        console.error(exception);
    }
}
/**
 * Send a message to the WebSocket server
 */
function onSendClick() {
    /*if (webSocket.readyState != WebSocket.OPEN) {
        console.error("webSocket is not open: " + webSocket.readyState);
        return;
    }*/
    var msg = document.getElementById("message").value;
    socket.emit('chat message', msg);
    //webSocket.send(msg);
}

</script>