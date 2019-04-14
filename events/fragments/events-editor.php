<?php
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

require_once('../db-initialization.php');

if (!$isAdmin)
{
  die('Only administrators beyond this point! Sorry.');
}

try
{
  $event = $db->prepare('SELECT EventID, Name, LocationID, Date, Description, ClassLimits
                         FROM aya_events
                         WHERE Deleted = FALSE
                           AND EventID = :id');
  $event->bindValue(':id', (empty($_POST['EventID']) ? 0 : $_POST['EventID']), PDO::PARAM_INT);
  $event->execute();
  $ayaEvent = $event->fetch(PDO::FETCH_ASSOC);
  $event = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

echo '<div id="events-editor-dialog" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button class="close" data-dismiss="modal" type="button" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title">Wettbewerbsverwaltung</h3>
      </div>
      <div class="modal-body">
        <form id="event-form" data-event-id="' . (is_numeric($ayaEvent['EventID']) ? $ayaEvent['EventID'] : 0) . '" data-toggle="validator">
          <fieldset>
            <legend>Wettbewerbsdaten</legend>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-event">Name</div>
                    <input id="event-name" class="form-control" maxlength="50"
                           placeholder="' . (empty($ayaEvent['Name']) ? 'WoofAYA' : $ayaEvent['Name']) . '" type="text"
                           value="' . (empty($ayaEvent['Name']) ? '' : $ayaEvent['Name']) . '" />
                    <div class="input-group-addon">
                      <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-event">Beschreibung</div>
                    <textarea id="event-description" class="form-control" maxlength="5000" minlength="20"
                              placeholder="' . (empty($ayaEvent['Description']) ? 'Wettbewerbsinfos' : $ayaEvent['Description']) . '"
                              rows="5">' . (empty($ayaEvent['Description']) ? '' : $ayaEvent['Description']) . '</textarea>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-event">Austragungsort</div>';
try
{
  $locations = $db->prepare("SELECT LocationID, Name, CONCAT(Street, ' ', StreetNumber, ', ', Zip, ' ', City) AS Address
                            FROM aya_locations
                            WHERE Deleted = FALSE
                            ORDER BY Name ASC");
  $locations->execute();
  $ayaLocations = $locations->fetchAll(PDO::FETCH_ASSOC);
  $locations = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

$locationsList = '';
foreach ($ayaLocations as $location)
{
  $locationsList .= '<option data-subtext="' . $location['Address'] . '" value="' . $location['LocationID'] . '"'
    . ($location['LocationID'] == $ayaEvent['LocationID'] ? ' selected="selected"' : '') . '>' . $location['Name'] . '</option>';
}
$location = reset($ayaLocations);

echo '<select id="location-selector" class="form-control selectpicker show-menu-arrow show-tick" data-initial-location="'
        . (is_numeric($ayaEvent['LocationID']) ? $ayaEvent['LocationID'] : 0) . '" data-size="10" data-width="false" required="required"
              title="Bitte Austragungsort auswählen">
                      ' . $locationsList . '
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <!-- TODO: Until <input type="datetime-local" /> is available on all major browsers, we stick with separate date and time inputs. -->
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label">Datum</div>
                    <input id="event-date" class="form-control" max="' . date('Y', strtotime('+1 year')) . '-12-31" min="' . date('Y') . '-01-01"
                           pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" required="required" step="1" type="date"
                           value="' . (empty($ayaEvent['Date']) ? date('Y') . '-02-27' : date('Y-m-d', strtotime($ayaEvent['Date']))) . '" />
                    <div class="input-group-addon">
                      <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label">Uhrzeit</div>
                    <input id="event-time" class="form-control" max="22:00" min="06:00" pattern="[0-9]{2}:[0-9]{2}" required="required" step="900"
                           type="time" value="' . (empty($ayaEvent['Date']) ? '09:30' : date('H:i', strtotime($ayaEvent['Date']))) . '" />
                    <div class="input-group-addon">
                      <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </fieldset>
          <fieldset>
            <legend>Klassengrenzen</legend>';

$ayaClasses = null;
try
{
  $classes = $db->prepare('SELECT ClassID, Name
                           FROM aya_classes
                           WHERE Deleted = FALSE
                           ORDER BY SortKey ASC');
  $classes->execute();
  $ayaClasses = $classes->fetchAll(PDO::FETCH_ASSOC);
  $classes = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

$classesCount = count($ayaClasses);
$classLimits = json_decode($ayaEvent['ClassLimits'], true);
$i = 0;
foreach ($ayaClasses as $class)
{
  if (($i % 2) == 0)
  {
    echo '<div class="row">';
  }

  echo '<div class="col-md-6">
  <div class="form-group">
    <div class="input-group">
      <div class="input-group-addon aya-label aya-label-event">' . $class['Name'] . '</div>
      <input id="class-limit-' . (is_numeric($class['ClassID']) ? $class['ClassID'] : 0) . '" class="form-control text-right" max="20" min="0"
             placeholder="' . (is_numeric($classLimits[$class['ClassID']]) ? $classLimits[$class['ClassID']] : 0) . '" step="1" type="number"
             value="' . (is_numeric($classLimits[$class['ClassID']]) ? $classLimits[$class['ClassID']] : 10) . '" />
      <div class="input-group-addon">
        <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
      </div>
    </div>
  </div>
</div>';

  if ((++$i % 2) == 0)
  {
    echo '</div>';
  }
}

echo '</fieldset>
        </form>
        <div id="result" class="alert aya-alert-ajax" role="alert"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-aya-default" data-dismiss="modal" type="button">Abbrechen</button>
        <button id="save" class="btn btn-aya" disabled="disabled" type="button">Hinzufügen</button>
      </div>
    </div>
  </div>
</div>';

$ayaClassLimits = null;
$db = null;
?>
