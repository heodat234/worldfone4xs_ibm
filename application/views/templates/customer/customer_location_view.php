<div id="allPopup-location">
	<div id="window-location" data-role="window"
	                 data-title="Customer Location"
	                 data-width="1200"
	                 data-actions="['Minimize', 'Maximize' , 'Close']"
	                 data-position="{'top': 20}"
	                 data-visible="false"
	                 data-bind="events: {open: openPopup, close: closePopup}" style="padding: 2px">
		<div class="container-fluid">
			<div class="row">
				<div id="popup-tabstrip" style="margin-top: 2px">			       			 
						
					<div>
						<div class="container-fluid">
							<div class="row form-horizontal">
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label col-xs-4">Người liên hệ</label>
										<div class="col-xs-8">
											<input class="k-textbox upper-case-input" name="name" data-bind="value: item.name" style="width: 100%">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-xs-4">Số điện thoại</label>
										<div class="col-xs-8">
											<input class="k-textbox" name="phone" data-bind="value: item.phone" style="width: 100%">
										</div>
									</div>
								</div>
								<div class="col-sm-8">
									<div class="form-group">
										<div class="col-xs-8">
											<button id="sendSmsLocationBtn" class="k-button" data-bind="events: {click: sendSmsLocation}">Send SMS</button>											
										</div>
									</div>		

									
									<!-- <button data-bind="events: {click: getGDVLocation}">Track GDV Location</button> -->
								</div>
							</div>
							<div class="row form-horizontal">
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label col-xs-4">Vị trí 1</label>
										<div class="col-xs-8">
											<input readonly="true" class="k-textbox" name="position1" data-bind="value: item.position1" style="width: 100%">
										</div>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<div class="col-xs-8">
											<span id="trackingIcon" data-bind="visible: isTracking"><i class="fa fa-spinner fa-spin" style="font-size: 18px" title="tracking"></i></span>	

											<span id="trackedIcon" data-bind="visible: isTracked"><i class="fa fa-check" style="font-size: 18px; color: green" title="tracked"></i></span>
											<button id="getCustomerLocationBtn" class="k-button" data-bind="invisible: isTracking, events: {click: getCustomerLocation}">Track Customer Location</button>
										</div>
									</div>
		                        </div>
								<div class="col-sm-4">
								</div>
							</div>
							<div class="row form-horizontal">
								
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label col-xs-4">Vị trí 2</label>
										<div class="col-xs-8" id="pac-card" style="z-index: 999999; top: 100">
											<div id="pac-container">
												<input id="pac-input" name="position2" data-bind="value: item.position2" class="k-textbox controls"
								            		placeholder="Enter a location" style="width: 100%" autocomplete="off">
								        	</div>
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-xs-4">Mô tả</label>
										<div class="col-xs-8">										
											<input id="case_description" name="case_description" data-bind="value: case_description" class="k-textbox controls" style="width: 100%" autocomplete="off"/>
										</div>
									</div>

									<div class="form-group">
		                                <label class="control-label col-xs-4">GDV</label>
		                                <div class="col-xs-8">
		                                    <select id="gdvDDL" data-role="dropdownlist" name="gdv"
		                                    	data-value-field="gdv_id"
		                                    	data-text-field="name"
		                                    	data-template="gdvDDLTemplate"
		                                        data-value-primitive="false"
		                                        data-bind="value: item.gdv" style="width: 100%"/>
		                                    </select>
		                                </div>
		                            </div>

		                            <div class="form-group">
		                            	<div class="col-xs-4"></div>
		                            	<div class="col-xs-8">
											<button class="k-button" data-bind="click: assignGDV" style="background: yellow;">CREATE CASE & ASSIGN</button>
										</div>								
									</div>

								</div>
								<div class="col-sm-8">
								    <div id="map" style="width: 100%; height: 400px"></div>
								    <div id="infowindow-content">
								      <img src="" width="16" height="16" id="place-icon">
								      <span id="place-name"  class="title"></span><br>
								      <span id="place-address"></span>
								    </div>
								</div>
							</div>							
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>
	
</div>

<script id="gdvDDLTemplate" type="text/x-kendo-template">
    #: data.name# - #: data.phone#
</script>

<script type="text/javascript">
function removeTab(ele) {
    var tabstrip = $("#popup-tabstrip").data("kendoTabStrip");
    var item = $(ele).closest('.k-item');
    tabstrip.remove(item.index());

    if (tabstrip.items().length > 1 && item.hasClass('k-state-active')) {
        tabstrip.select(2);
    }
}
var phone = <?= json_encode($phone) ?>,
	date =  new Date();

var startPopup = async function () {
	var customer = await $.get("customers/api/getCustomerByPhone/" + phone);
		customer = customer ? customer : {phone: phone, contacts :[]};		

	var observable = window.popupObservable = kendo.observable({

        phone: phone,
        
        openPopup: function(e) {
        	e.sender.wrapper.css({ top: 20 });
           
        },
        closePopup: function(){
        	var dialog = $("#window-location").data("kendoWindow");
            dialog.destroy();
       

            if(window.getGDVLocationInterval) {
	            clearInterval(window.getGDVLocationInterval)
	        }
	        if(window.getLocationInterval) {
	            clearInterval(window.getLocationInterval)
	        }
            flag_popup=false;            
            $("#popup-locate").empty();
        },
        item: customer,
        case_description: "",
        customerMarker: null,
        manualCustomerMarker: null,
        gdvMarkers: [],
       
		sendSmsLocation: function() {						
			$.ajax({
				url: "customers/api/sendSmsLocation",
				type: "POST",
				data: {phone: this.get("item.phone") }, // calluuid: getDataPopup.calluuid},				
				success: (response) => {
					if(response.status) popupNotification.show("Success", "success");
					else popupNotification.show(response.message, "error");

					this.getCustomerLocation()
				}	
			})
		},
		isTracking: false,
		isTracked: false,
		getCustomerLocation: function () {				
			if (window.getLocationInterval) {
				clearInterval(getLocationInterval)
			}
			this.set("isTracking", true)
			this.set("isTracked", false)
			window.getLocationInterval = setInterval(() => {
				$.ajax({
					url: "customers/api/getLocationFromCustomer",
					type: "POST",
					data: {phone: this.get("item.phone")}, //calluuid: getDataPopup.calluuid
					success: (res) => {
						if (res.status == 1) {									
							clearInterval(getLocationInterval)
							this.set("isTracking", false)
							this.set("isTracked", true)

							if (this.customerMarker != null) {
								this.customerMarker.setMap(null)
							}

							this.customerMarker = new google.maps.Marker({
							    position: {lat: parseFloat(res.position.latitude), lng: parseFloat(res.position.longitude)},
							    title:"Customer Location",
							    icon: 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png',
							 //    label: {
								//     color: 'blue',
								//     fontWeight: 'bold',
								//     text: 'Customer Location'
								// }
							});

							map.setCenter(this.customerMarker.position)									
							this.customerMarker.setMap(map)

							//reverse geocoding
							var geocoder = new google.maps.Geocoder;
							geocoder.geocode({'location': this.customerMarker.position}, (results, status) => {
						      	if (status === 'OK') {
						        	if (results[0]) {						        		
						          		this.set("item.position1", results[0].formatted_address)
						        	}
						      	}
						    });

							this.getGDVLocation()
						}
					}
				})
			}, 3000)			
		},
		getGDVLocation: function (manualLocation = null) {
			if (window.getGDVLocationInterval) {
				clearInterval(getGDVLocationInterval)
			}
			window.getGDVLocationInterval = setInterval(() => {
				$.ajax({
					url: "customers/api/getGDVLocation",
					type: "POST",
					data: {},
					success: (res) => {
						if (res.status == 1) {
							clearInterval(getGDVLocationInterval)

							let gdvData = this.handleGDVLocationData(res.data, manualLocation)
							if (gdvData.length <= 0) {
								return
							}
							this.getGDVList(gdvData.map((v) => {
								return v.gdv_id
							}))

							if (typeof this.gdvMarkers != 'undefined') {
								for (var i = 0; i < this.gdvMarkers.length; i++) {
						          this.gdvMarkers[i].setMap(null);
						        }
							}

							let marker
							this.gdvMarkers = []
							for (let i = 0; i < gdvData.length; i++) {
								if (gdvData[i].distanceFromCustomer > 70000) {									
									continue
								}
								marker = new google.maps.Marker({
								    position: {lat: parseFloat(gdvData[i].position.coords.latitude), lng: parseFloat(gdvData[i].position.coords.longitude)},
								    title: gdvData[i].gdv_id,
								    map: map,
								    label: {
									    color: 'black',
									    fontWeight: 'bold',
									    text: gdvData[i].gdv_id
									}
								});
								this.gdvMarkers.push(marker)
							}		

							var bounds = new google.maps.LatLngBounds();
							for (var i = 0; i < this.gdvMarkers.length; i++) {
								bounds.extend(this.gdvMarkers[i].getPosition());
							}

							if (this.customerMarker != null) {
								bounds.extend(this.customerMarker.getPosition());	
							}

							if (typeof window.manualMarker != 'undefined') {
								bounds.extend(window.manualMarker.getPosition());	
							}

							map.fitBounds(bounds);					
						}
					}
				})
			}, 3000)
		},
		handleGDVLocationData: function (data, manualLocation = null) {
			let gdvPosList = []

			let customerLocation = (manualLocation != null) ? manualLocation : this.customerMarker.position			

			if (typeof customerLocation == 'undefined' || customerLocation == null) {
				return data
			}

			for (let i = 0; i < data.length; i++) {
				let gdvPos = new google.maps.LatLng(parseFloat(data[i].position.coords.latitude), parseFloat(data[i].position.coords.longitude))
				distance = google.maps.geometry.spherical.computeDistanceBetween(gdvPos, customerLocation)

				data[i].distanceFromCustomer = distance

				gdvPosList.push(data[i])
			}

			//sort

			sortedGdvPosList = this.bubbleSortLocationData(gdvPosList)
			
			return sortedGdvPosList.slice(0, 5);
		},
		bubbleSortLocationData: function (arr){
		   	var len = arr.length;
		   	for (var i = len-1; i>=0; i--){
		     	for(var j = 1; j<=i; j++){
		       		if(arr[j-1].distanceFromCustomer > arr[j].distanceFromCustomer){
		           		var temp = arr[j-1];
		           		arr[j-1] = arr[j];
		           		arr[j] = temp;
		        	}
		     	}
		   }

		   return arr;
		},
		getGDVList: function (idArr) {
			gdvListDataSource = new kendo.data.DataSource({
				transport: {
					read: {
						type: 'POST',
						url: "customers/api/getGDVList",
						data: {idArr: idArr},
					}
				}
			})

			var gdvDDL = $("#gdvDDL").data("kendoDropDownList");

			gdvDDL.setDataSource(gdvListDataSource)
		},
		assignGDV: function () {
			let _position1 = this.get("customerMarker")
			let position1LatLng = (_position1 == null) ? null : {lat: _position1.position.lat(), lng: _position1.position.lng(), address: this.get("item.position1")}

			let _position2 = this.get("manualCustomerMarker")
			let position2LatLng = (_position2 == null) ? null : {lat: _position2.lat(), lng: _position2.lng(), address: this.get("item.position2")}			
			$.ajax({
				method: "post",
				url: "customers/api/assignGDV",
				data: {
					//calluuid: getDataPopup.calluuid,
					customer_id: this.get("item._id.$id"),
					name: this.get("item.name"),
					phone: this.get("item.phone"),
					assign: this.get("item.gdv.gdv_id"),
					position1: position1LatLng,					
					position2: position2LatLng,		
					description: this.get("case_description"),
				},
				success: (res) => {
					if(res.status == 1) {
						popupNotification.show("Success", "success")
					} else {
						popupNotification.show(res.message, "error")
					}
				},
				error: (xhr) => {
					console.log(xhr)
				}
			})
		},
    })

	kendo.bind($("#allPopup-location"), observable);
	if($("#window-location").length) {
        dialog = $("#window-location").data("kendoWindow");
        if(dialog) dialog.center().open();        
    }
  
}();


initAutocomplete = function() {
    window.map = new google.maps.Map(document.getElementById('map'), {
      center: {lat: 10.7725133, lng: 106.70578479999999},
      zoom: 16
    });
    var card = document.getElementById('pac-card');
    var input = document.getElementById('pac-input');

    //map.controls[google.maps.ControlPosition.TOP_RIGHT].push(card);
    var searchBox = new google.maps.places.SearchBox(input);
    //var autocomplete = new google.maps.places.Autocomplete(input);

    map.addListener('bounds_changed', function() {
      searchBox.setBounds(map.getBounds());
    });

    var markers = [];
    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener('places_changed', function() {
      var places = searchBox.getPlaces();

      if (places.length == 0) {
        return;
      }

      // Clear out the old markers.
      markers.forEach(function(marker) {
        marker.setMap(null);
      });
      markers = [];

      // For each place, get the icon, name and location.
      var bounds = new google.maps.LatLngBounds();
      places.forEach(function(place) {
        if (!place.geometry) {
          console.log("Returned place contains no geometry");
          return;
        }
        
        //code chi hien thi 1 marker cho customer location        
        /*if (typeof popupObservable.customerMarker != 'undefined' && popupObservable.customerMarker != null) {
        	popupObservable.customerMarker.setMap(null)
        }
 
        popupObservable.customerMarker = new google.maps.Marker({          
          	icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
          	title: place.name,
          	position: place.geometry.location
        })
		popupObservable.customerMarker.setMap(map)
		popupObservable.getGDVLocation()

        markers.push(popupObservable.customerMarker);*/

        markers.push(window.manualMarker = new google.maps.Marker({          
          	icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
          	title: place.name,
          	position: place.geometry.location,
          	map: map
        }))

        popupObservable.getGDVLocation(place.geometry.location)

        popupObservable.set("manualCustomerMarker", place.geometry.location)

        if (place.geometry.viewport) {
          // Only geocodes have viewport.
          bounds.union(place.geometry.viewport);
        } else {
          bounds.extend(place.geometry.location);
        }
      });
      map.fitBounds(bounds);
    });
      
}

initAutocomplete();
</script>
