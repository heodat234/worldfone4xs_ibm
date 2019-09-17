self.onmessage = (e) => {
    var jqXHR = new XMLHttpRequest();
	jqXHR.open('PUT', '/api/v1/ping/update_ping', true);
	jqXHR.setRequestHeader('Content-Type', 'application/json');
	jqXHR.send(JSON.stringify(e.data));
}
function ping() {
	var reConnectTimeOut = undefined;

	if(typeof(EventSource) !== "undefined") {
		if(fetch) {
			var es = new EventSource('/api/v1/ping/sse');
			es.onmessage = function(e) {
			};
			es.addEventListener("ping", (e) => {
				postMessage({ping: e.data});
			}, false);

			es.addEventListener("agent_status", function(e) {
				postMessage({agent_status: e.data});
			}, false);

			es.addEventListener("call", (e) => {
				postMessage({call: e.data});
			}, false);

			es.addEventListener("menu_notifications", (e) => {
				postMessage({menu_notifications: e.data}); 
			}, false);

			es.addEventListener("chat_notifications", (e) => {
				postMessage({chat_notifications: e.data});
			}, false);

			es.addEventListener("header_notifications", (e) => {
				postMessage({header_notifications: e.data});
			}, false);

			es.addEventListener("chat_status", (e) => {
				postMessage({chat_status: e.data});
			}, false);

			es.addEventListener("wait_in_queue", (e) => {
				postMessage({wait_in_queue: e.data});
			}, false);

			es.onerror = (e) => {
				if(typeof reConnectTimeOut != 'undefined') clearTimeout(reConnectTimeOut);
				reConnectTimeOut = setTimeout(() => {
					es.close();
					ping();
					// Ping check online || session expire
					var jqXHR = new XMLHttpRequest();
					jqXHR.open('GET', '/api/v1/ping/json', true);
					jqXHR.timeout = 3000;
					jqXHR.onload = function () {
					  	// Request finished. Do processing here.
					  	var ping_event_data = null;
						postMessage({ping: ping_event_data});
					};

					jqXHR.ontimeout = function (e) {
						// XMLHttpRequest timed out. Do something here.
						var ping_event_data = null;
						if(typeof reConnectTimeOut != 'undefined') {
							// Disconnect
							ping_event_data = JSON.stringify({time: 0});
						}
						postMessage({ping: ping_event_data});
					};

					jqXHR.send(null);
				}, 10000)
			};
		}	
	}
}
ping();