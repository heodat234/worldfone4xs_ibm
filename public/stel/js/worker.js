function ping() {
	var reConnectTimeOut = undefined;

	if(typeof(EventSource) !== "undefined") {
		if(fetch) {
			var es = new EventSource('/api/v1/ping/sse');
			es.onmessage = function(e) {
			};
			es.addEventListener("ping", (e) => {
				postMessage(JSON.parse(e.data));
			}, false);

			es.onerror = (e) => {
				if(typeof reConnectTimeOut != 'undefined') clearTimeout(reConnectTimeOut);
				reConnectTimeOut = setTimeout(() => {
					es.close();
					ping();
					// Ping check online || session expire
					var jqXHR = new XMLHttpRequest();
					jqXHR.open('GET', '/api/v1/ping/json', true);

					jqXHR.onload = function () {
					  	// Request finished. Do processing here.
					  	var ping_event_data = null;
					  	if(jqXHR.status == 200) {
						} 
						else
						{
							if(typeof reConnectTimeOut != 'undefined') {
								// Disconnect
								ping_event_data = JSON.stringify({time: 0});
							}
						}
						postMessage(ping_event_data);
					};

					jqXHR.send(null);
				}, 10000)
			};
		}	
	}
}
ping();