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

let ajaxFolder = 'ajax/';
let scriptsFolder = 'scripts/';

let delete_ = '#delete';
let editor = '-editor';
let inputs_ = '-inputs'; // "input" is used by bootstrap-select library
let fragmentTarget = '#fragment-modal';
let mode = '';
let result = '#result';
let save = '#save';

let createLabel = 'Hinzufügen';
let updateLabel = 'Aktualisieren';

let defaultResponses = {
  AlreadyDeleted: 'bereits gelöscht',
  AlreadyExists: 'existiert bereits',
  AlreadyUsed: 'bereits verwendet',
  CountMismatch: 'abweichende Anzahl',
  MissingData: 'fehlende Angaben',
  NonExistent: 'existiert nicht',
  Unchanged: 'keine Änderung',
  Unknown: 'unbekannter Fehler'
};

function attachEventHandler(selector) {
  $(selector).each(function() {
    $(this).on('input', function(event) {
      let initialValue = '';
      if (event.target.type === 'date' || event.target.type === 'textarea' || event.target.type === 'time') {
        initialValue = event.target.defaultValue;
      } else if (event.target.value !== '') {
        initialValue = event.target.placeholder;
      }
      enableSaveOnModified(event.target.value, initialValue);
    });
  });
}

function createMessage(mode, isSuccess, isUnchanged, response) {
  let message = '<span class="glyphicon glyphicon-';
  let prefix = '';
  if (isSuccess) {
    message += 'ok';
  } else if (isUnchanged) {
    message += 'info';
    prefix = 'Hinweis';
  } else {
    message += 'exclamation';
    prefix = 'Fehler';
  }
  // NOTE: displayName is defined in each *-editor.js
  message += '-sign" aria-hidden="true"></span> ' + (isSuccess ? '' : prefix + ': ') + displayName + ' ' + (isSuccess ? 'erfolgreich' : 'nicht') + ' ';
  switch (mode) {
    case 'delete':
      message += 'gelöscht';
      break;

    case 'post':
      message += 'angelegt';
      break;

    case 'put':
      message += 'aktualisiert';
      break;

    default:
      message += 'möglich';
      break;
  }
  message += (isSuccess ? '' : getResponseDetail(response)) + '.' + (isSuccess ? '' : ' Bitte korrigieren!');

  return message;
}

function enableCreateOnDefault(selector, nonDefaultCallback) {
  if (selector.selectpicker('val') == 0) {
    setEditorMode(createLabel, updateLabel);
  } else {
    if (typeof (nonDefaultCallback) !== 'undefined' && $.isFunction(nonDefaultCallback)) {
      nonDefaultCallback();
    }
    setEditorMode(updateLabel, createLabel);
  }
}

function enableDelete(selector) {
  let deleteButton = $(delete_);
  deleteButton.click(function() {
    deleteButton.prop('disabled', 'disabled');

    $.ajax({
      cache: false,
      data: {
        'Entities': JSON.stringify([selector.selectpicker('val')]),
        'Type': selector.attr('id').split('-', 1)[0] + 's'
      },
      method: 'POST',
      url: ajaxFolder + 'delete-entities.php'
    }).done(function(response) {
      showMessage('delete', response, null, false);
      if (response > 0) {
        let currentSelection = selector.children('option').filter(':selected');
        selector.selectpicker('val', currentSelection.siblings('[value != ""]').first().val());
        currentSelection.remove();
        selector.selectpicker('refresh');
        selector.trigger('changed.bs.select');
        deleteButton.prop('disabled', false);
      }
    });
  });
}

function enableDeleteOnNonDefaults(selector) {
  $(delete_).prop(
    'disabled',
    ((selector.selectpicker('val') == 0) || (selector.children('option').filter(':selected').siblings().length < 3) ? 'disabled' : false));
}

function enableSaveOnModified(currentValue, initialValue) {
  $(save).prop('disabled', (currentValue !== initialValue ? false : 'disabled')).text(mode === 'post' ? createLabel : updateLabel);
}

function enableSelectionByRowClick(panel) {
  $('#' + panel + ' > tbody > tr').click(function(event) {
    if (event.target.type !== 'checkbox') {
      $(this).find('td > input:checkbox').prop('checked', function(index, value) {
        return !value;
      });
    }
  });
}

function fragmentLoader(fragment, ajaxMode, source, finishCallback) {
  $(fragmentTarget).load('fragments/' + fragment + editor + '.php',
    getRequestData(fragment, source),
    function() {
      let saveButton = $(save);
      mode = ajaxMode;
      if (mode === 'put') {
        saveButton.text(updateLabel);
      }
      $.ajax({
        dataType: 'script',
        url: scriptsFolder + fragment + editor + '.js'
      }).done(function() {
        if (typeof(loadedCallback) !== 'undefined' && $.isFunction(loadedCallback)) {
          loadedCallback();
        }
      });

      saveButton.click(function() {
        saveButton.prop('disabled', 'disabled');

        $.ajax({
          cache: false,
          data: getPayload(),
          method: 'POST',
          url: getRequestUrl(mode, fragment)
        }).done(function(response) {
          showMessage(mode, response, finishCallback);
          saveButton.prop('disabled', (response > 0));
        });
      });

      $('#' + fragment + editor + '-dialog').modal('show');
    });
}

function getRequestData(fragment, source) {
  switch (fragment) {
    case 'attendances':
      return {
        'EventID': source
      };

    case 'flaws':
      let item = $('#' + source + ' input:checked').first();
      return {
        'ClassID': item.data('class-id'),
        'UserID': item.data('user-id'),
        'VehicleID': item.data('vehicle-id')
      };

    case 'deletions':
      return {
        'Entities': JSON.stringify(source.entities),
        'Type': source.type
      };

    default:
      fragment = fragment.slice(0, (fragment.endsWith('es') ? -2 : -1));
      return { // ECMA6 computed property below (unsupported in IE11, but nobody cares for IE anymore)
        [ fragment.charAt(0).toUpperCase() + fragment.slice(1) + 'ID' ]: $('#' + source + ' input:checked').first().data(fragment + '-id')
      };
  }
}

function getRequestUrl(mode, fragment) {
  let url = ajaxFolder + mode + '-';
  switch(fragment) {
    case 'classes':
      url += fragment.slice(0, -2);
      break;

    case 'deletions':
      url += 'entities';
      break;

    default:
      url += fragment.slice(0, -1);
      break;
  }
  url += '.php';

  return url;
}

function getResponseDetail(response) {
  let detail = ' (';
  if (typeof(responseCallback) !== 'undefined' && $.isFunction(responseCallback)) {
    detail += responseCallback(response);
  } else {
    switch (response) {
      case '0':
        detail += defaultResponses.Unchanged;
        break;

      case 'ALREADY_DELETED':
        detail += defaultResponses.AlreadyDeleted;
        break;

      case 'ALREADY_EXISTS':
        detail += defaultResponses.AlreadyExists;
        break;

      case 'ALREADY_USED':
        detail += defaultResponses.AlreadyUsed;
        break;

      case 'COUNT_MISMATCH':
        detail += defaultResponses.CountMismatch;
        break;

      case 'MISSING_DATA':
        detail += defaultResponses.MissingData;
        break;

      case 'NON_EXISTENT':
        detail += defaultResponses.NonExistent;
        break;

      default:
        detail += defaultResponses.Unknown;
        break;
    }
  }
  detail += ')';

  return detail;
}

function hideModal(finishCallback) {
  setTimeout(function() {
    $(fragmentTarget + ' > div.modal')
      .modal('hide')
      .on('hidden.bs.modal', function(event) {
        if (finishCallback && typeof (finishCallback) === 'function') {
          finishCallback();
        }
      });
  }, 2000);
}

function setEditorMode(newLabel, oldLabel) {
  mode = newLabel === createLabel ? 'post' : 'put';
  inputsPanel.text(inputsPanel.text().replace(oldLabel.toLowerCase(), newLabel.toLowerCase()));
}

function showMessage(mode, response, finishCallback, autoClose = true) {
  let resultPanel = $(result);
  let isSuccess = response > 0;
  let isUnchanged = response == 0;

  resultPanel.attr('class', 'alert aya-alert-ajax alert-' + (isUnchanged ? 'info' : (isSuccess ? 'success' : 'danger')));
  resultPanel.html(createMessage(mode, isSuccess, isUnchanged, response));
  resultPanel.slideDown();

  if (autoClose && isSuccess) {
    hideModal(finishCallback);
  }
}

$(document).ready(function() {
  let attendances = 'attendances';

  $('.' + attendances.slice(0, -1)).each(function() {
    let that = $(this);
    that.click(function() {
      that.prop('disabled', 'disabled');
      fragmentLoader(attendances, 'post', that.data('event-id'), null);
      that.prop('disabled', false);
    });
  });

  $('#' + attendances + editor).click(function() {
    fragmentLoader(attendances, 'put', null, null);
  });

  $('#vehicles' + editor).click(function() {
    fragmentLoader('vehicles', 'put', null, null);
  });

  $('tbody').each(function() {
    $(this).on('click', 'tr', function(event) {
      if (event.target.type === 'checkbox') {
        return;
      }

      $(this).find('th > input').prop('checked', function(index, value) {
        return !value;
      });
    });
  });
});
