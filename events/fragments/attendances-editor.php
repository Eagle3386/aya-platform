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

$selectorDateFormat = 'd.m.: ';

try
{
  $event = $db->prepare('SELECT EventID, Date, Name
                         FROM aya_events
                         WHERE Deleted = FALSE
                           AND EventID = :id');
  $event->bindValue(':id', $_POST['EventID'], PDO::PARAM_INT);
  $event->execute();
  $ayaEvent = $event->fetch(PDO::FETCH_ASSOC);
  $event = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

echo '<div id="attendances-editor-dialog" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button class="close" data-dismiss="modal" type="button" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title">Teilnahmeverwaltung</h3>
      </div>
      <div class="modal-body">
        <form id="attendance-form" data-toggle="validator">
          <fieldset>
            <legend>Teilnahme auswählen</legend>
            <div class="row">
              <div class="col-md-9">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-attendee">Teilnahme</div>';

try
{
  $attendances = $db->prepare('SELECT A.AttendeeID, A.EventID, E.Date, E.Name AS EventName, C.Name AS ClassName, V.RegistrationNumber
                               FROM aya_attendees A
                               JOIN aya_events E ON E.EventID = A.EventID
                               JOIN aya_classes C ON C.ClassID = A.ClassID
                               JOIN aya_vehicles V ON V.VehicleID = A.VehicleID
                               WHERE A.Deleted = FALSE
                                 AND A.phpBBUserID = :id
                                 AND DATEDIFF(E.Date, CURDATE()) > -1
                               ORDER BY E.Date ASC, C.SortKey ASC');
  $attendances->bindValue(':id', $phpBBUserID, PDO::PARAM_INT);
  $attendances->execute();
  $ayaAttendances = $attendances->fetchAll(PDO::FETCH_ASSOC);
  $attendances = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

$attendancesList = '';
$initialAttendance = 0;
if (empty($ayaAttendances))
{
  $attendancesList = '<option id="no-attendances">Keine aktiven Teilnahmen vorhanden</option>';
}
else
{
  $isFirstMatch = false;
  foreach ($ayaAttendances as $attendance)
  {
    if (!$isFirstMatch && ($attendance['EventID'] === $ayaEvent['EventID'] || empty($ayaEvent['EventID'])))
    {
      $initialAttendance = $attendance['AttendeeID'];
      $isFirstMatch = true;
    }
    $attendancesList .= '<option data-event-id="' . $attendance['EventID'] . '" data-subtext="' . $attendance['ClassName'] . ' ('
      . $attendance['RegistrationNumber'] . ')" value="' . $attendance['AttendeeID'] . '">' . date($selectorDateFormat, strtotime($attendance['Date']))
      . $attendance['EventName'] . '</option>';
  }
}

echo '<select id="attendance-selector" class="form-control selectpicker show-menu-arrow show-tick" data-initial-attendance="' . $initialAttendance . '"
              data-size="10" data-width="100%" title="Bitte Teilnahme auswählen!">'
  . $attendancesList
  . (empty($ayaEvent['EventID']) ? '' : '<option id="post-attendance" data-event-id="' . $ayaEvent['EventID'] . '" data-subtext="' . $ayaEvent['Name']
    . '" value="0">' . date($selectorDateFormat, strtotime($ayaEvent['Date'])) . 'Neue Teilnahme hinzufügen</option>');

try
{
  $attendee = $db->prepare("SELECT U.username AS Nickname, CONCAT(P.pf_vor_nachname_, ', ', P.pf_vorname) AS RealName,
                            P.pf_teamname AS TeamName, P.pf_handynr AS PhoneNumber
                            FROM phpbb_users U
                            JOIN phpbb_profile_fields_data P ON P.user_id = U.user_id
                            WHERE U.user_id = :id");
  $attendee->bindValue(':id', $phpBBUserID, PDO::PARAM_INT);
  $attendee->execute();
  $ayaAttendee = $attendee->fetch(PDO::FETCH_ASSOC);
  $attendee = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

echo '              </select>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <div class="input-group">
                    <button id="delete" class="btn btn-aya" type="button">Löschen</button>
                  </div>
                </div>
              </div>
            </div>
          </fieldset>
          <fieldset>
            <legend id="editor-inputs">Teilnahmedaten ' . ($initialAttendance === 0 ? 'hinzufügen' : 'aktualisieren') . '</legend>
            <div class="row">
              <div class="col-md-12">
                <div class="alert alert-warning text-center" role="alert">
                  Inhalte schattierter Felder im <a class="alert-link" href="/ucp.php?i=173" target="_blank">Profil <span
                    class="glyphicon glyphicon-new-window"></span></a> anpassbar. Nummernformat beachten!
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-attendee">Realname</div>
                    <input id="attendee-name-full" class="form-control" value="' . $ayaAttendee['RealName'] . '" type="text" readonly="readonly" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-attendee">Nickname</div>
                    <input id="attendee-name-nick" class="form-control" value="' . $ayaAttendee['Nickname'] . '" type="text" readonly="readonly" />
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-attendee">Team-Name</div>
                    <input id="attendee-name-team" class="form-control" value="' . $ayaAttendee['TeamName'] . '" type="text" readonly="readonly" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-attendee">Handy-Nr.</div>
                    <input id="attendee-phone-number" class="form-control" value="' . $ayaAttendee['PhoneNumber'] . '" type="text" readonly="readonly" />
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-attendee">Fahrzeug</div>
                    <select id="vehicle-selector" class="form-control selectpicker show-menu-arrow show-tick" data-size="5" data-width="100%"
                            required="required" title="Bitte auswählen!">';

try
{
  $vehicles = $db->prepare("SELECT V.VehicleID, CONCAT(M.Name, ' ', V.Model, ' (', V.RegistrationNumber, ')') AS VehicleName
                            FROM aya_vehicles V
                            JOIN aya_manufacturers M ON M.ManufacturerID = V.ManufacturerID
                            WHERE V.Deleted = FALSE
                              AND V.phpBBUserID = :id
                            ORDER BY V.RegistrationNumber");
  $vehicles->bindValue(':id', $phpBBUserID, PDO::PARAM_INT);
  $vehicles->execute();
  $ayaVehicles = $vehicles->fetchAll(PDO::FETCH_ASSOC);
  $vehicles = null;

  foreach ($ayaVehicles as $vehicle)
  {
    echo '<option value="' . $vehicle["VehicleID"] . '">' . $vehicle["VehicleName"] . '</option>';
  }
}
catch (PDOException $exception)
{
  ShowException($exception);
}

echo '</select>
    </div>
  </div>
</div>
<div class="col-md-6">
  <div class="form-group">
    <div class="input-group">
      <div class="input-group-addon aya-label aya-label-attendee">Klasse</div>
      <select id="class-selector" class="form-control selectpicker show-menu-arrow show-tick" data-size="10" data-width="100%" required="required"
              title="Bitte auswählen!">';

try
{
  $classes = $db->prepare('SELECT ClassID, Name, PriceLimited
                           FROM aya_classes
                           WHERE Deleted = FALSE
                           ORDER BY SortKey ASC');
  $classes->execute();

  $previousGroup = '';
  while ($class = $classes->fetch(PDO::FETCH_ASSOC))
  {
    $currentGroup = explode(' ', $class['Name'], 2)[0];
    if ($previousGroup !== $currentGroup)
    {
      if (!empty($previousGroup))
      {
        echo '</optgroup>';
      }

      echo '<optgroup label="' . $currentGroup . '">';
    }

    echo '<option data-price-limited="' . (empty($class['PriceLimited']) ? 'false' : 'true') . '" value="' . $class['ClassID'] . '">' . $class['Name']
      . '</option>';

    $previousGroup = $currentGroup;
  }

  echo '</optgroup>';
}
catch (PDOException $exception)
{
  ShowException($exception);
}

$classes = null;
$db = null;

echo '              </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-attendee">Anmerkung</div>
                    <input id="attendee-remark" class="form-control" maxlength="50" placeholder="Muss leider 15:30 wieder los." type="text" />
                  </div>
                </div>
              </div>
            </div>
          </fieldset>
        </form>
        <div id="result" class="alert aya-alert-ajax" role="alert"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-aya-default" data-dismiss="modal" type="button">Abbrechen</button>
        <button id="save" class="btn btn-aya" disabled="disabled" type="button">Teilnehmen</button>
      </div>
    </div>
  </div>
</div>';
?>
