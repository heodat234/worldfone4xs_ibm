if(sessionStorage.getItem('pingId') == null) {
	sessionStorage.setItem('pingId', Date.now());
}
const TABSCACHE = "Tabs";
// enable warnings
// Function to check tab current open
function intervalTabsData() {
	var tabsData = localStorage.getItem(TABSCACHE);
	if(!tabsData) {
		// Khoi tao
		tabsData = {data: [], activeId: sessionStorage.getItem('pingId')};
		tabsData.data.push({id: sessionStorage.getItem('pingId'), lastpingtime: Date.now(), currentUri: ENV.currentUri, active: true});
		localStorage.setItem(TABSCACHE, JSON.stringify(tabsData));
	} else {
		tabsData = JSON.parse(tabsData);
		var checkExists = false;
		var minId = 0;
		tabsData.data.map(ele => {
			if(ele.id == sessionStorage.getItem('pingId')) {
				// Cap nhat lastpingtime
				ele.lastpingtime = Date.now();
				ele.active = checkExists = true;
				ele.currentUri = ENV.currentUri;
			} else {
				if(ele.lastpingtime < Date.now() - 5000) {
					ele.active = false;
				}
			}
			if(ele.active) {
				id = Number(ele.id);
				if(!minId) minId = id;
				else minId = (id  > minId) ? minId : id;
			}
		})

		tabsData.data.forEach(ele => {
			if(Number(ele.id) == minId) {
				tabsData.activeId = ele.id;
			}
		})
		if(!checkExists) {
			tabsData.data.push({id: sessionStorage.getItem('pingId'), lastpingtime: Date.now(), currentUri: ENV.currentUri, active: true});
		}
		localStorage.setItem(TABSCACHE, JSON.stringify(tabsData));

		var fetch = (tabsData.activeId == sessionStorage.getItem('pingId')) ? 1 : 0;
		if(!fetch) {
			if(window.beforeFetch === 1) {
				// 1 -> 0
				if(typeof reConnectTimeOut != 'undefined') clearTimeout(reConnectTimeOut);
				if(window.es) window.es.close();
			}
			var data = localStorage.getItem("ping_event_data");
			var e = {data : data};
			if(typeof onlineWidget != 'undefined') onlineWidget(e);
			if(typeof clockWidget != 'undefined') clockWidget(e);

			data = localStorage.getItem("agent_status_event_data");
			if(data) {
				var e = {data : data};
				if(typeof agentStatusWidget != 'undefined') agentStatusWidget(e);
				if(typeof queueWidget != 'undefined') queueWidget(e);
			}
			data = localStorage.getItem("call_event_data");
			if(data) {
				var e = {data : data};
				if(typeof executeCall != 'undefined') executeCall(e);
			}
			data = localStorage.getItem("menu_notifications_event_data")
			if(data) {
				var e = {data : data};
				if(typeof showMenuNotifications != 'undefined') showMenuNotifications(e);
			}
			data = localStorage.getItem("chat_notifications_event_data")
			if(data) {
				var e = {data : data};
				if(typeof chatNotifications != 'undefined') chatNotifications(e);
			}
			data = localStorage.getItem("header_notifications_event_data")
			if(data) {
				var e = {data : data};
				if(typeof notificationWidget != 'undefined') notificationWidget(e);
			}
			data = localStorage.getItem("chat_status_event_data")
			if(data) {
				var e = {data : data};
				if(typeof chatStatusWidget != 'undefined') chatStatusWidget(e);
			}
			data = localStorage.getItem("wait_in_queue_event_data")
			if(data) {
				var e = {data : data};
				if(typeof waitInQueueWidget != 'undefined') waitInQueueWidget(e);
			}
		} else {
			// 0 -> 1
			if(window.beforeFetch === 0) {
				if(typeof reConnectTimeOut != 'undefined') clearTimeout(reConnectTimeOut);
				if(window.es) window.es.close();
				ping();
			}
		}
		window.beforeFetch = fetch;
	}
}
intervalTabsData();

setInterval(intervalTabsData, 1000);

function pingDefault() {
	var reConnectTimeOut = undefined,
		tabsData = JSON.parse(localStorage.getItem(TABSCACHE));

	if(!tabsData) {
		intervalTabsData();
		return;
	}

	var fetch = (tabsData.activeId == sessionStorage.getItem('pingId')) ? 1 : 0;
			
		if(fetch) {
			document.title = window.currentTitle = originalTitle + " (Main)";
		} else document.title = window.currentTitle = originalTitle;

	if(typeof(EventSource) !== "undefined") {
		if(fetch) {
			var es = window.es = new EventSource(ENV.vApi + 'ping/sse');
			es.onmessage = function(e) {
			};
			es.addEventListener("ping", (e) => {
				localStorage.setItem("ping_event_data", e.data);
				if(typeof clockWidget != 'undefined') clockWidget(e);
				if(typeof onlineWidget != 'undefined') onlineWidget(e);
				var tabs = [];
				tabsData.data.forEach(doc => {
					if(doc.active)
						tabs.push({id: doc.id, currentUri: doc.currentUri})
				});
				$.ajax({
					url: `${ENV.vApi}ping/update_ping`,
					type: "PUT",
					global: false,
					contentType: "application/json; charset=utf-8",
					data: JSON.stringify({tabs: tabs})
				});
			}, false);

			es.addEventListener("agent_status", function(e) {
				localStorage.setItem("agent_status_event_data", e.data);
				if(typeof agentStatusWidget != 'undefined') agentStatusWidget(e);
				if(typeof queueWidget != 'undefined') queueWidget(e);
			}, false);

			es.addEventListener("call", (e) => {
				localStorage.setItem("call_event_data", e.data);
				if(typeof executeCall != 'undefined') executeCall(e);
			}, false);

			es.addEventListener("menu_notifications", (e) => {
				localStorage.setItem("menu_notifications_event_data", e.data);
				if(typeof showMenuNotifications != 'undefined') showMenuNotifications(e); 
			}, false);

			es.addEventListener("chat_notifications", (e) => {
				localStorage.setItem("chat_notifications_event_data", e.data);
				if(typeof chatNotifications != 'undefined') chatNotifications(e); 
			}, false);

			es.addEventListener("header_notifications", (e) => {
				localStorage.setItem("header_notifications_event_data", e.data);
				if(typeof notificationWidget != 'undefined') notificationWidget(e); 
			}, false);

			es.addEventListener("chat_status", (e) => {
				localStorage.setItem("chat_status_event_data", e.data);
				if(typeof chatStatusWidget != 'undefined') chatStatusWidget(e); 
			}, false);

			es.addEventListener("wait_in_queue", (e) => {
				localStorage.setItem("wait_in_queue_event_data", e.data);
				if(typeof waitInQueueWidget != 'undefined') waitInQueueWidget(e); 
			}, false);

			es.onerror = (e) => {
				if(typeof reConnectTimeOut != 'undefined') clearTimeout(reConnectTimeOut);
				reConnectTimeOut = window.reConnectTimeOut = setTimeout(() => {
					es.close();
					pingDefault();
					// Ping check online || session expire
					$.ajax({
						url: ENV.vApi + 'ping/json',
						dataType: 'json',
						timeout: 3000,
						error: function(jqXHR) {
							var ping_event_data = null;
							if(jqXHR.status == 200) {
								// Response with http redirect, session expired
								// Remove ping id session storage
								// sessionStorage.removeItem("pingId");
								// sessionStorage.setItem('pingId', Date.now());
							} 
							else
							{
								if(typeof reConnectTimeOut != 'undefined') {
									// Disconnect
									ping_event_data = JSON.stringify({time: 0});
								}
							}
							localStorage.setItem("ping_event_data", ping_event_data);
							if(typeof onlineWidget != 'undefined') onlineWidget({data: ping_event_data});
						}
					})
				}, 10000)
			};

			window.onbeforeunload = function (e) {
				es.close();
			}
		}	
	} else {
	    swal("Sorry! Your web browser not compatible with this application")
	}
}

function pingByWorker() {
	var tabsData = JSON.parse(localStorage.getItem(TABSCACHE));

	if(!tabsData) {
		intervalTabsData();
		return;
	}

	var fetch = (tabsData.activeId == sessionStorage.getItem('pingId')) ? 1 : 0;
			
	if(fetch) {
		document.title = window.currentTitle = originalTitle + " (Main)";
	} else document.title = window.currentTitle = originalTitle;

	if(fetch) {
		const pingWorker = new Worker(ENV.baseUrl + "public/stel/js/ping.worker.js");
		pingWorker.onmessage = function (event) {

	        if(event.data.ping !== undefined) {
	        	var e = {data: event.data.ping};
	        	localStorage.setItem("ping_event_data", e.data);
				if(typeof clockWidget != 'undefined') clockWidget(e);
				if(typeof onlineWidget != 'undefined') onlineWidget(e);
				var tabs = [];
				tabsData.data.forEach(doc => {
					if(doc.active)
						tabs.push({id: doc.id, currentUri: doc.currentUri})
				});
				pingWorker.postMessage({tabs: tabs});
	        }

	        if(event.data.agent_status !== undefined) {
	        	var e = {data: event.data.agent_status};
	        	localStorage.setItem("agent_status_event_data", e.data);
				if(typeof agentStatusWidget != 'undefined') agentStatusWidget(e);
				if(typeof queueWidget != 'undefined') queueWidget(e);
	        }

	        if(event.data.call !== undefined) {
	        	var e = {data: event.data.call};
	        	localStorage.setItem("call_event_data", e.data);
				if(typeof executeCall != 'undefined') executeCall(e);
	        }

	        if(event.data.menu_notifications !== undefined) {
	        	var e = {data: event.data.menu_notifications};
	        	localStorage.setItem("menu_notifications_event_data", e.data);
				if(typeof showMenuNotifications != 'undefined') showMenuNotifications(e); 
	        }

	        if(event.data.chat_notifications !== undefined) {
	        	var e = {data: event.data.chat_notifications};
	        	localStorage.setItem("chat_notifications_event_data", e.data);
				if(typeof chatNotifications != 'undefined') chatNotifications(e);
	        }

	        if(event.data.header_notifications !== undefined) {
	        	var e = {data: event.data.header_notifications};
	        	localStorage.setItem("header_notifications_event_data", e.data);
				if(typeof notificationWidget != 'undefined') notificationWidget(e); 
	        }

	        if(event.data.chat_status !== undefined) {
	        	var e = {data: event.data.chat_status};
	        	localStorage.setItem("chat_status_event_data", e.data);
				if(typeof chatStatusWidget != 'undefined') chatStatusWidget(e);
	        }

	        if(event.data.wait_in_queue !== undefined) {
	        	var e = {data: event.data.wait_in_queue};
	        	localStorage.setItem("wait_in_queue_event_data", e.data);
				if(typeof waitInQueueWidget != 'undefined') waitInQueueWidget(e);
	        }
	    };
	    pingWorker.onerror = function (err) {
	        console.log(err.message, err);
	    };
	    pingWorker.onerrormessage = function (err) {
		    console.log(err.message, err);
		};
	}
}

function ping() {
	if(typeof(Worker) !== "undefined" && ENV.use_worker) {
		pingByWorker();
	} else {
		pingDefault();
	}
};

ping();