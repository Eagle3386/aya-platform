/*
 * Copyright Martin Arndt, TroubleZone.Net Productions
 *
 * Licensed under the EUPL, Version 1.2 only (the "Licence");
 * You may not use this work except in compliance with the Licence.
 * You may obtain a copy of the Licence at:
 *
 * https://joinup.ec.europa.eu/software/page/eupl
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the Licence is distributed on an "AS IS" basis,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the Licence for the specific language governing permissions and limitations under the Licence.
 */

function createDirectionsRenderer(map, position) {
  let directionsRenderer = new google.maps.DirectionsRenderer({
    draggable: true,
    hideRouteList: true,
    map: map,
    markerOptions: {
      animation: google.maps.Animation.DROP,
      clickable: true,
      crossOnDrag: true,
      cursor: 'pointer',
      draggable: true,
      //icon:
      opacity: 1.0,
      optimized: true,
      position: position,
      visible: true
    }
  });

  return directionsRenderer;
}

//function createGeoCoder(map, location, marker, bounce) {
function createGeoCoder(location) {
  let geoCoder = new google.maps.Geocoder();
  geoCoder.geocode({
      address: location
    },
    function(results, status) {
      //this.parseGeoCodeResult(results, status, map, marker, location, bounce);
      if (status == google.maps.GeocoderStatus.OK) {
        //this.setInfoBox(map, marker, results[0].geometry.location, address, bounce);
        return results[0].geometry.location;
      } else {
        alert("Geocode lookup for '" + address + "' unsuccessful: " + status);
      }
    });
}

function createMarker(map, location, label) {
  let marker = new google.maps.Marker({
    map: map,
    position: location
  });

  return marker;
}

function initializeLocationMap() {
  let ayaLocation = $('.aya-location').text();
  let userLocation = $('#aya-user').data('location');
  let map = new google.maps.Map(document.getElementById('location-map'), {
    center: new google.maps.LatLng(51, 10.333333), // geo-heart of Germany
    mapTypeControl: true,
    mapTypeId: google.maps.MapTypeId.HYBRID,
    overviewMapControl: true,
    panControl: true,
    rotateControl: true,
    scaleControl: true,
    streetViewControl: true,
    zoom: 7,
    zoomControl: true
  });

  let trafficLayer = new google.maps.TrafficLayer();
  trafficLayer.setMap(map);

  if ($('#aya-user').data('user-id') > 1 && userLocation.length > 2) {
    this.setDirections(map, ayaLocation, userLocation);
  } else {
    this.setLocation(map, ayaLocation);
  }

  let distanceService = new google.maps.DistanceMatrixService;
  distanceService.getDistanceMatrix({
    origins: [ userLocation ],
    destinations: [ ayaLocation ],
    travelMode: google.maps.TravelMode.DRIVING,
    unitSystem: google.maps.UnitSystem.METRIC,
    avoidHighways: false,
    avoidTolls: true
  }, function(response, status) {
    if (status !== google.maps.DistanceMatrixStatus.OK) {
      alert('Distance query for \'' + ayaLocation + '\' / \'' + userLocation + '\' failed: ' + status);
    } else {
      //let result = response.rows[0].elements[0];
      //let distance = result.distance.text;
      //
      //if (shortDistanceResult) {
      //  let time = (result.duration.value / 3600);
      //  let hours = Math.floor(time);
      //  let minutes = Math.ceil((time - hours) * 60);
      //  distance += ' (ca. ' + (hours > 0 ? (hours + ' h, ') : '') + ((hours === 0) && (minutes === 0) ? 1 : minutes) + ' min)';
      //} else {
      //  distance += ', ca. ' + result.duration.text;
      //}
      //
      //$(".distance").html(distance).attr('title', distance);
    }
  });
}

//function loadOnDemand() {
//  let script = document.createElement('script');
//  script.src = 'https://maps.googleapis.com/maps/api/js?callback=initializeLocationMap';
//  document.body.appendChild(script);
//}
//window.onload = loadOnDemand;

function parseGeoCodeResult(results, status, map, marker, address, bounce) {
}

function setDirections(map, ayaLocation, userLocation) {
  let directionsRenderer = this.createDirectionsRenderer(map, ayaLocation);

  //this.createGeoCoder(map, ayaLocation, directionsRenderer.markers[0], true);
  //this.createGeoCoder(map, userLocation, directionsRenderer.markers[1], false);

  let ayaCoordinates = this.createGeoCoder(ayaLocation);
  let ayaMarker = this.createMarker(map, ayaCoordinates, 'Z');
  let userCoordinates = this.createGeoCoder(userLocation);
  let userMarker = this.createMarker(map, userCoordinates, 'S');
  //ayaMarker.setLabel('Z');
  //console.log(ayaMarker);

  let directionsService = new google.maps.DirectionsService();
  directionsService.route({
    origin: userLocation,
    destination: ayaLocation,
    travelMode: google.maps.TravelMode.DRIVING,
    unitSystem: google.maps.UnitSystem.METRIC
  }, function(response, status) {
    if (status === google.maps.DirectionsStatus.OK) {
      directionsRenderer.setDirections(response);
    } else {
      window.alert('Directions request failed due to ' + status);
    }
  });
}

function setInfoBox(map, marker, markerLocation, address, bounce) {
  let infoBox = new google.maps.InfoWindow({
    content: address,
    position: markerLocation
  });
  infoBox.open(map, marker);

  if (bounce) {
    infoBox.open(map, marker);

    setTimeout(function() {
      marker.setAnimation(google.maps.Animation.BOUNCE);
    }, 700); // current duration of one bounce (as of version 3.13)
  }

  google.maps.event.addListener(marker, 'click', function() {
    infoBox.open(map, marker);
  });
}

function setLocation(map, ayaLocation) {
  let directionsRenderer = this.createDirectionsRenderer(map);
  this.createGeoCoder(map, ayaLocation, directionsRenderer.markers[0], true);
  this.setInfoBox(map, marker, markerLocation, address, bounce);
  map.setCenter(ayaLocation);
  map.setZoom(9);
}
