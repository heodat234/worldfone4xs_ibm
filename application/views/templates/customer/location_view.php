<div class="container-fluid">
	<div class="row form-horizontal">
		<div class="col-sm-6">
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
		<div class="col-sm-6">
			<div class="form-group">
				<div class="col-xs-8">
					<button id="sendSmsLocationBtn" class="k-button" data-bind="events: {click: sendSmsLocation}">Send SMS</button>											
				</div>
			</div>		

			
			<!-- <button data-bind="events: {click: getGDVLocation}">Track GDV Location</button> -->
		</div>
	</div>
	<div class="row form-horizontal">
		<div class="col-sm-6">
			<div class="form-group">
				<label class="control-label col-xs-4">Vị trí 1</label>
				<div class="col-xs-8">
					<input readonly="true" class="k-textbox" name="position1" data-bind="value: item.position1" style="width: 100%">
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<div class="col-xs-8">
					<span id="trackingIcon" data-bind="visible: isTracking"><i class="fa fa-spinner fa-spin" style="font-size: 18px" title="tracking"></i></span>	

					<span id="trackedIcon" data-bind="visible: isTracked"><i class="fa fa-check" style="font-size: 18px; color: green" title="tracked"></i></span>
					<button id="getCustomerLocationBtn" class="k-button" data-bind="invisible: isTracking, events: {click: getCustomerLocation}">Track Customer Location</button>
				</div>
			</div>
        </div>
		<div class="col-sm-6">
		</div>
	</div>
	<div class="row form-horizontal">
		
		<div class="col-sm-6">
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

            <!-- <div class="form-group">
            	<div class="col-xs-4"></div>
            	<div class="col-xs-8">
					<button class="k-button" data-bind="click: assignGDV" style="background: yellow;">CREATE CASE & ASSIGN</button>
				</div>								
			</div> -->

		</div>
		<div class="col-sm-6">
		    <div id="map" style="width: 100%; height: 400px"></div>
		    <div id="infowindow-content">
		      <img src="" width="16" height="16" id="place-icon">
		      <span id="place-name"  class="title"></span><br>
		      <span id="place-address"></span>
		    </div>
		</div>
	</div>
	<div class="side-form-bottom">
		<div class="text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">Cancel</button>
			<button class="btn btn-sm btn-primary btn-save" onclick="closeForm()" data-bind="click: assignGDV"><b>CREATE CASE & ASSIGN</b></button>
		</div>
	</div>							
</div>

<script type="text/javascript">
	var initAutocomplete = function() {
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
        /*if (typeof trackingObservable.customerMarker != 'undefined' && trackingObservable.customerMarker != null) {
            trackingObservable.customerMarker.setMap(null)
        }
 
        trackingObservable.customerMarker = new google.maps.Marker({          
            icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
            title: place.name,
            position: place.geometry.location
        })
        trackingObservable.customerMarker.setMap(map)
        trackingObservable.getGDVLocation()

        markers.push(trackingObservable.customerMarker);*/

        markers.push(window.manualMarker = new google.maps.Marker({          
            icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
            title: place.name,
            position: place.geometry.location,
            map: map
        }))

        trackingObservable.getGDVLocation(place.geometry.location)

        trackingObservable.set("manualCustomerMarker", place.geometry.location)

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