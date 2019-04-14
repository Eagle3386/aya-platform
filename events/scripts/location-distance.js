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

// shortDistanceResult must be set inside HTML/PHP file which includes this file
function initializeDistances() {
  let userLocation = $('#aya-user').dataset.location;

  $('.aya-event').each(function() {
    let that = $(this);
    let ayaLocation = $("address", that).text();

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
        let result = response.rows[0].elements[0];
        let distance = result.distance.text;
        let duration = result.duration.text;

        if (shortDistanceResult) {
          let time = (result.duration.value / 3600);
          let hours = Math.floor(time);
          let minutes = Math.floor((time - hours) * 60);
          if ((hours === 0) && (minutes === 0)) {
            minutes = 1;
          }

          duration = (hours > 0 ? (hours + ' h') : '') + ((hours > 0) && (minutes > 0) ? ', ' : '') + (minutes > 0 ? (minutes + ' min') : '');
        }

        $("span.distance", that).html(distance + ' (ca. ' + duration + ')');
      }
    });
  });
}
