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
  $location = $db->prepare('SELECT LocationID, Name, LocationID, Street, StreetNumber, Zip, City, ST_X(Coordinates) AS Latitude,
                              ST_Y(Coordinates) AS Longitude, HostUrl, Description
                            FROM aya_locations
                            WHERE Deleted = FALSE
                              AND LocationID = :id');
  $location->bindValue(':id', (empty($_POST['LocationID']) ? 0 : $_POST['LocationID']), PDO::PARAM_INT);
  $location->execute();
  $ayaLocation = $location->fetch(PDO::FETCH_ASSOC);
  $location = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

echo '<div id="locations-editor-dialog" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button class="close" data-dismiss="modal" type="button" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title">Ortsverwaltung</h3>
      </div>
      <div class="modal-body">
        <form id="location-form" data-location-id="' . (empty($ayaLocation['LocationID']) ? 0 : $ayaLocation['LocationID']) . '" data-toggle="validator">
          <fieldset>
            <legend>Ortsangaben</legend>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-event">Name</div>
                    <input id="location-name" class="form-control" maxlength="30"
                           placeholder="' . (empty($ayaLocation['Name']) ? 'WoofAYA' : $ayaLocation['Name']) . '"
                           value="' . (empty($ayaLocation['Name']) ? '' : $ayaLocation['Name']) . '" type="text" />
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
                    <div class="input-group-addon aya-label aya-label-event">Website</div>
                    <input id="location-host-url" class="form-control" maxlength="255"
                           placeholder="' . (empty($ayaLocation['HostUrl']) ? 'https://www.mrwoofa.de/' : $ayaLocation['HostUrl']) . '" type="text"
                           value="' . (empty($ayaLocation['HostUrl']) ? '' : $ayaLocation['HostUrl']) . '" />
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-event">Beschreibung</div>
                    <textarea id="location-description" class="form-control" maxlength="1000" minlength="0"
                              placeholder="' . (empty($ayaLocation['Description']) ? 'Ortsinfos' : $ayaLocation['Description']) . '"
                              rows="5" type="text">' . (empty($ayaLocation['Description']) ? '' : $ayaLocation['Description']) . '</textarea>
                  </div>
                </div>
              </div>
            </div>
        </fieldset>
        <fieldset>
          <legend>Google Maps</legend>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label">Breitengrad</div>
                    <input id="location-latitude" class="form-control text-right" max="180" min="0"
                           placeholder="' . (empty($ayaLocation['Latitude']) ? '10.6655' : $ayaLocation['Latitude']) . '" step="0.0001" type="number"
                           value="' . (empty($ayaLocation['Latitude']) ? '' : $ayaLocation['Latitude']) . '" />
                    <div class="input-group-addon">
                      <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label">Längengrad</div>
                    <input id="location-longitude" class="form-control text-right" max="180" min="0"
                           placeholder="' . (empty($ayaLocation['Longitude']) ? '48.9903' : $ayaLocation['Longitude']) . '" step="0.0001" type="number"
                           value="' . (empty($ayaLocation['Longitude']) ? '' : $ayaLocation['Longitude']) . '" />
                    <div class="input-group-addon">
                      <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-8">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label">Straße</div>
                    <input id="location-street" class="form-control" maxlength="50"
                           placeholder="' . (empty($ayaLocation['Street']) ? 'Zum Bauerbrink' : $ayaLocation['Street']) . '" type="text"
                           value="' . (empty($ayaLocation['Street']) ? '' : $ayaLocation['Street']) . '" />
                    <div class="input-group-addon">
                      <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label">Hausnummer</div>
                    <input id="location-street-number" class="form-control text-right" maxlength="4"
                           placeholder="' . (empty($ayaLocation['StreetNumber']) ? '23' : $ayaLocation['StreetNumber']) . '" type="text"
                           value="' . (empty($ayaLocation['StreetNumber']) ? '' : $ayaLocation['StreetNumber']) . '" />
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label">PLZ</div>
                    <input id="location-zip" class="form-control text-right" max="99998" min="01001"
                           placeholder="' . (empty($ayaLocation['Zip']) ? '32369' : $ayaLocation['Zip']) . '" step="1" type="number"
                           value="' . (empty($ayaLocation['Zip']) ? '' : $ayaLocation['Zip']) . '" />
                    <div class="input-group-addon">
                      <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label">Ort</div>
                    <input id="location-city" class="form-control" maxlength="50"
                           placeholder="' . (empty($ayaLocation['City']) ? 'Rahden' : $ayaLocation['City']) . '" type="text"
                           value="' . (empty($ayaLocation['City']) ? '' : $ayaLocation['City']) . '" />
                    <div class="input-group-addon">
                      <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                    </div>
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
        <button id="save" class="btn btn-aya" type="button">Hinzufügen</button>
      </div>
    </div>
  </div>
</div>';

$db = null;
?>
