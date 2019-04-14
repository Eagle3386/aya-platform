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

let actionCreate = 'create';
let actionDelete = 'delete';
let actionExport = 'export';
let actionUpdate = 'update';
let panelEvents = 'events';
let panelEventsYear = '#' + panelEvents + '-year';

function createFragmentLoader(panel, action) {
  switch (action) {
    case actionCreate:
      return function() {
        fragmentLoader(panel, 'post', null, null);
      };

    case actionDelete:
      return function() {
        deleteItems(panel, null);
      };

    case actionExport:
      return panel === panelEvents
        ? function() {
            let exportEvents = $(this);
            exportEvents.prop('disabled', 'disabled');
            $('#' + panel + ' input:checked').each(function() {
              window.open(ajaxFolder + 'get-attendances.php?EventID=' + $(this).data(panelEvents.slice(0, -1) + '-id'), '_blank');
              /* If the running webserver has enough power (and most importantly memory), try this instead:
              let eventId = $(this).data(panelEvents.slice(0, -1) + '-id');
              $.ajax({
                data: {
                  EventID: eventId
                },
                dataType: 'native',
                url: ajaxFolder + 'get-attendances.php',//?EventID=' + eventId,
                xhrFields: {
                  responseType: 'blob'
                },
                success: function(blob) {
                  console.log(JSON.stringify(blob.size));
                  let link = document.createElement('a');
                  link.href = window.URL.createObjectURL(blob);
                  link.download = "Teilnehmerliste - " + new Date() + " - " + TITLE + ".xlsx";
                  link.click();
                }
              });*/
            });
            exportEvents.prop('disabled', false);
          }
        : function() {
          };

    case actionUpdate:
      return function() {
        fragmentLoader(panel, 'put', panel, null);
      };
  }
}

function createPanelActions(panels, actions) {
  $.each(panels, function(index, panel) {
    if (panel !== panelEvents) {
      enableSelectionByRowClick(panel);
    }

    $.each(actions, function(index, action) {
      $('#' + panel + '-' + action).click(createFragmentLoader(panel, action));
    });
  });
}

function deleteItems(panel, finishCallback) {
  let deletableItems = [];
  $('#' + panel + ' input:checked').each(function() {
    let item = $(this);
    deletableItems.push({
      'Id': item.data(panel.slice(0, (panel.endsWith('es') ? -2 : -1)) + '-id'),
      'Name': item.closest('tr').find('td:nth-child(' + (panel === panelEvents ? 3 : 2) + ')').text()
    });
  });

  fragmentLoader('deletions', 'delete', {
      'entities': deletableItems,
      'type': panel
    },
    function() {
      $.each(deletableItems, function() {
        $(this).closest('tr').remove();
      });
    });
}

function getEvents(yearSelector) {
  let selectedYear = yearSelector.selectpicker('val');
  $.ajax({
    cache: false,
    data: {
      'EventYear': selectedYear,
      'ShowDeleted': true
    },
    method: 'POST',
    url: ajaxFolder + 'get-' + panelEvents + '.php'
  }).done(function(response) {
    let currentDate = new Date(Date.now());
    let events = $('#' + panelEvents + ' > tbody');
    events.html('');
    $(panelEventsYear).text(selectedYear);
    let isSeparatorShown = false;
    $.each(response, function(index, event) {
      if (currentDate.getFullYear() == selectedYear && currentDate > Date.parse(event.Date) && !isSeparatorShown) {
        events.append('<tr><td colspan="5"><fieldset><legend><p class="text-center">Archivierte Veranstaltungen</p></legend></fieldset></td></tr>');
        isSeparatorShown = true;
      }

      events.append('<tr><td class="text-center"><input data-event-id="' + event.EventID + '" type="checkbox" /></td>'
        + '<td class="text-center text-nowrap">' + (event.Deleted ? '<del>' : '') + event.Date + (event.Deleted ? '</del>' : '') + '</td>'
        + '<td class="text-center">' + (event.Deleted ? '<del>' : '') + event.Name + (event.Deleted ? '</del>' : '') + '</td>'
        + '<td class="text-center">' + (event.Deleted ? '<del>' : '') + event.City + (event.Deleted ? '</del>' : '') + '</td>'
        + '<td class="text-center text-nowrap">' + (event.Deleted ? '<del>' : '') + event.LastUpdate + (event.Deleted ? '</del>' : '')
        + '</td></tr>');
    });
    yearSelector.selectpicker('refresh');
  }).done(function() {
    enableSelectionByRowClick(panelEvents);
  });
}

$(document).ready(function() {
  createPanelActions([ 'classes', 'colors', panelEvents, 'locations', 'manufacturers' ], [ actionCreate, actionDelete, actionExport, actionUpdate ]);

  $(panelEventsYear + '-selector').selectpicker()
    .on('loaded.bs.select', function(event, clickedIndex, newValue, oldValue) {
      getEvents($(this));
    }).on('changed.bs.select', function(event, clickedIndex, newValue, oldValue) {
      getEvents($(this));
    });

  $("[class$=-input-components]")
    .on('keypress', function(event) {
      if (event.which == '13') {
        return false;
      }
    }).on('paste', function(event) {
      // TODO: Implement prevention
    });
});
