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
var classSelector = $('#class-selector');
var displayName = 'Teilnahme';
var inputsPanel = $('#' + editor.slice(1) + inputs_);
var remark = $('#attendee-remark');
var remarkPlaceholder = 'Muss leider 15:30 wieder los.';
var resultPanel = $(result);
var saveButton = $(save);
var vehicleSelector = $('#vehicle-selector');

function addAttendance(attendance) {
  $('.list-group[data-class-id=\'' + attendance.ClassId + '\']')
    .fadeOut(200, function() {
      let attendee = '<li class="list-group-item" data-attendee-id="' + attendance.Id + '">';
      if (attendance.TeamName.length < 1) {
        attendee += attendance.NickName;
      } else {
        attendee += '<div class="row"><div class="col-md-7">' + attendance.NickName + '</div><div class="col-md-5"><span class="label" title="'
          + attendance.TeamName + '">' + attendance.TeamName + '</span></div></div>';
      }
      attendee += '</li>';
      $(this).append(attendee).slideDown();
    });
}

function clearInputs() {
  classSelector.selectpicker('val', '');
  remark.prop('placeholder', remarkPlaceholder).val('');
  vehicleSelector.selectpicker('val', '');
}

function deleteAttendance(attendanceId) {
  $('.list-group-item[data-attendee-id=\'' + attendanceId + '\']')
    .fadeOut(200, function() {
      $(this).slideUp().remove();
    });
}

function finishCallback() {
  let attendance = {
    ClassId: classSelector.selectpicker('val'),
    Id: attendanceSelector.selectpicker('val'),
    NickName: $('#attendee-name-nick').val(),
    TeamName: $('#attendee-name-team').val()
  };
  switch (mode) {
    case 'delete':
      deleteAttendance(attendance.Id);
      break;

    case 'post':
      addAttendance(attendance);
      break;

    case 'put':
      deleteAttendance(attendance.Id);
      addAttendance(attendance);
      break;

    default:
      break;
  }
}

function getAttendance() {
  $.ajax({
    cache: false,
    data: {
      AttendeeID: attendanceSelector.selectpicker('val')
    },
    method: 'POST',
    url: ajaxFolder + 'get-attendance.php'
  }).done(function(response) {
    if (response.result !== false) {
      vehicleSelector.selectpicker('val', response.VehicleID).data('initial-vehicle', response.VehicleID);
      classSelector.selectpicker('val', response.ClassID).data('initial-class', response.ClassID);

      if (response.Remark === null) {
        remark.prop('placeholder', remarkPlaceholder).val('');
      } else {
        remark.prop('placeholder', response.Remark).val(response.Remark);
      }
    }
  });
}

function getPayload() {
  return {
    AttendeeID: attendanceSelector.selectpicker('val'),
    ClassID: classSelector.selectpicker('val'),
    EventID: attendanceSelector[0].selectedOptions[0].dataset.eventId,
    VehicleID: vehicleSelector.selectpicker('val'),
    Remark: remark.val()
  };
}

function loadedCallback() {
  attachEventHandler(".form-control");
  enableDelete(attendanceSelector);

  $.ajax({
    dataType: 'script',
    url: scriptsFolder + 'bootstrap-tooltip.js'
  });

  $('[data-toggle="tooltip"]').tooltip({
    animation: true,
    container: 'body',
    html: false,
    placement: 'auto top',
    selector: false,
    title: '',
    trigger: 'hover focus',
    viewport: {
      padding: 0,
      selector: 'body'
    }
  });

  if ($('#attendee-phone-number').val().length < 15) {
    saveButton.prop('disabled', 'disabled');
    showMessage('unused', 'MISSING_PHONE', finishCallback, false);
  }

  attendanceSelector.selectpicker()
    .on('loaded.bs.select', function(event, clickedIndex, newValue, oldValue) {
      let that = $(this);
      that.selectpicker('val', that.data('initial-attendance'));
      getAttendance();
      enableDeleteOnNonDefaults(that);
    }).on('changed.bs.select', function(event, clickedIndex, newValue, oldValue) {
      clearInputs();
      let that = $(this);
      that.data('initial-attendance', event.target.value);
      enableDeleteOnNonDefaults(that);
      enableSaveOnModified(event.target.value, that.data('initial-attendance'));
      if (that.selectpicker('val') == 0) {
        setEditorMode(createLabel, updateLabel);
      } else {
        getAttendance();
        setEditorMode(updateLabel, createLabel);
      }
    }).on('refreshed.bs.select', function(event, clickedIndex, newValue, oldValue) {
      enableDeleteOnNonDefaults($(this));
    });

  classSelector.selectpicker()
    .on('changed.bs.select', function(event, clickedIndex, newValue, oldValue) {
      if (event.target.selectedOptions[0].dataset.priceLimited === 'true') {
        $.ajax({
          cache: false,
          data: {
            VehicleID: vehicleSelector.selectpicker('val')
          },
          method: 'POST',
          url: ajaxFolder + 'get-components.php'
        }).done(function(response) {
          if (response.result === false) {
            showMessage('unused', 'MISSING_COMPONENTS', null, false);
          }
        });
      } else {
        resultPanel.slideUp();
      }
      enableSaveOnModified(event.target.value, (vehicleSelector.selectpicker('val') != vehicleSelector.data('initial-vehicle') ? -1 : this.dataset.initialClass));
    });

  vehicleSelector.selectpicker()
    .on('changed.bs.select', function(event, clickedIndex, newValue, oldValue) {
      classSelector.selectpicker('val', '');
      resultPanel.slideUp();
      enableSaveOnModified(event.target.value, this.dataset.initialVehicle);
    });
}

function responseCallback(response) {
  switch (response) {
    case '0':
      return defaultResponses.Unchanged;

    case 'ALREADY_ATTENDING':
      return 'Fahrzeug bereits angemeldet';

    case 'CLASS_FULL':
      return 'Klasse belegt';

    case 'MISSING_COMPONENTS':
      return 'keine Komponenten-Infos';

    case 'MISSING_DATA':
      return defaultResponses.MissingData;

    case 'MISSING_PHONE':
      return 'keine Handy-Nummer';

    default:
      return defaultResponses.Unknown;
  }
}
