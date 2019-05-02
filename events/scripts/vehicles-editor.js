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

var color = $('#vehicle-color');
var components = $('#vehicle-components');
var componentsPlaceholder = 'Nur in Klassen mit Preisbegrenzung angeben!';
var deleteButton = $(delete_);
var displayName = 'Fahrzeug';
var inputsPanel = $('#' + editor.slice(1) + inputs_);
var manufacturerSelector = $('#vehicle-manufacturer');
var model = $('#vehicle-model');
var registrationNumber = $('#vehicle-registration-number');
var saveButton = $(save);
var vehicleSelector = $('#vehicle-selector');

function clearInputs() {
  color.prop('placeholder', 'Firespark Red').val('');
  components.prop('placeholder', componentsPlaceholder).val('');
  manufacturerSelector.selectpicker('val', '');
  model.prop('placeholder', 'Golf V R32').val('');
  registrationNumber.prop('placeholder', 'A YA ' + new Date().getFullYear()).val('');
}

function getPayload() {
  return {
    Color: color.val(),
    Components: components.val(),
    ManufacturerID: manufacturerSelector.selectpicker('val'),
    Model: model.val(),
    RegistrationNumber: registrationNumber.val(),
    VehicleID: vehicleSelector.selectpicker('val')
  };
}

function loadedCallback() {
  attachEventHandler('.form-control');
  enableDelete(vehicleSelector);

  $.ajax({
    dataType: 'script',
    url: scriptsFolder + 'bootstrap-tooltip.js'
  });
  $.ajax({
    dataType: 'script',
    url: scriptsFolder + 'bootstrap-typeahead.js'
  });

  components.on('keypress', function(event) {
    if (event.which == '13') {
      return false;
    }
  }).on('paste', function(event) {
    // TODO: Implement prevention
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

  manufacturerSelector.selectpicker()
    .on('loaded.bs.select', function(event, clickedIndex, newValue, oldValue) {
      let that = $(this);
      $.getJSON(ajaxFolder + 'get-manufacturers.php', function(response) {
        that.html('');
        $.each(response, function(key, value) {
          that.append('<option ' + ((value.Keywords != null) && (value.Keywords.length > 0) ? 'data-tokens="' + value.Keywords + '" ' : '')
            + 'value="' + value.ManufacturerID + '">' + value.Name + '</option>');
        });
        that.selectpicker('refresh');
      }).done(function() {
        that.selectpicker('val', that[0].dataset.initialManufacturer);
      });
    }).on('changed.bs.select', function(event, clickedIndex, newValue, oldValue) {
      enableSaveOnModified(event.target.value, this.dataset.initialManufacturer);
    });

  vehicleSelector.selectpicker()
    .on('loaded.bs.select', function(event, clickedIndex, newValue, oldValue) {
      let that = $(this);
      that.selectpicker('val', that.data('initial-vehicle'));
      enableCreateOnDefault(that, getVehicle);
      enableDeleteOnNonDefaults(that);
    }).on('changed.bs.select', function(event, clickedIndex, newValue, oldValue) {
      clearInputs();
      let that = $(this);
      that.data('initial-vehicle', event.target.value);
      enableCreateOnDefault(that, getVehicle);
      enableDeleteOnNonDefaults(that);
      enableSaveOnModified(event.target.value, that.data('initial-vehicle'));
    }).on('refreshed.bs.select', function(event, clickedIndex, newValue, oldValue) {
      enableDeleteOnNonDefaults($(this));
    });
};

function getVehicle() {
  $.ajax({
    cache: false,
    data: {
      VehicleID: vehicleSelector.selectpicker('val')
    },
    method: 'POST',
    url: ajaxFolder + 'get-vehicle.php'
  }).done(function(response) {
    if (response.result !== false) {
      manufacturerSelector.selectpicker('val', response.ManufacturerID).data('initial-manufacturer', response.ManufacturerID);
      model.prop('placeholder', response.Model).val(response.Model);
      color.prop('placeholder', response.Color).val(response.Color);
      registrationNumber.prop('placeholder', response.RegistrationNumber).val(response.RegistrationNumber);

      if (response.Components === null) {
        components.prop('placeholder', componentsPlaceholder).val('');
      } else {
        components.prop('placeholder', response.Components).val(response.Components);
      }
    }
  });
}
