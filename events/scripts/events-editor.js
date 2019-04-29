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

var attendanceSelector = $('#attendance-selector');
var displayName = 'Wettbewerb';
var locationSelector = $('#location-selector');

function getPayload() {
  return {
    ClassLimits: function() {
      var limits = {};
      $("input[id^='class-limit-']").each(function() {
        limits[this.id.slice(12)] = parseInt(this.value, 10);
      });

      return JSON.stringify(limits);
    },
    Date: $('#event-date').val() + ' ' + $('#event-time').val(),
    Description: $('#event-description').val(),
    EventID: $('#event-form')[0].dataset.eventId,
    LocationID: locationSelector.selectpicker('val'),
    Name: $('#event-name').val()
  };
}

function loadedCallback() {
  attachEventHandler(".form-control");
  enableDelete(attendanceSelector, 1);

  attendanceSelector.selectpicker()
    .on('loaded.bs.select', function(event, clickedIndex, newValue, oldValue) {
      enableDeleteOnNonDefaults($(this));
    }).on('changed.bs.select', function(event, clickedIndex, newValue, oldValue) {
      enableDeleteOnNonDefaults($(this));
    });

  locationSelector.selectpicker()
    .on('changed.bs.select', function(event, clickedIndex, newValue, oldValue) {
      enableSaveOnModified(event.target.value, this.dataset.initialLocation);
    });
}
