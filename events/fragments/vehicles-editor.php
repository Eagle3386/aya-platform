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

echo '<div id="vehicles-editor-dialog" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button class="close" data-dismiss="modal" type="button" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title">Fahrzeugverwaltung</h3>
      </div>
      <div class="modal-body">
        <form id="vehicle-form">
          <fieldset>
            <legend>Fahrzeug auswählen</legend>
            <div class="row">
              <div class="col-md-9">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-vehicle">Fahrzeug</div>';

try
{
  $vehicles = $db->prepare("SELECT V.VehicleID, M.ManufacturerID, M.Name, V.Model, V.Color, V.RegistrationNumber, V.Components,
                              CONCAT(M.Name, ' ', V.Model) AS VehicleName
                            FROM aya_vehicles V
                            JOIN aya_manufacturers M ON M.ManufacturerID = V.ManufacturerID
                            WHERE V.Deleted = FALSE
                              AND V.phpBBUserID = :id
                            ORDER BY V.RegistrationNumber ASC");
  $vehicles->bindValue(':id', $phpBBUserID, PDO::PARAM_INT);
  $vehicles->execute();
  $ayaVehicles = $vehicles->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $exception)
{
  ShowException($exception);
}

$vehicles = null;
$db = null;

$vehiclesList = '';
foreach ($ayaVehicles as $vehicle)
{
  $vehiclesList .= '<option data-manufacturer-id="' . (empty($vehicle['ManufacturerID']) ? '0' : $vehicle['ManufacturerID']) . '" data-subtext="'
                     . $vehicle['RegistrationNumber'] . '" value="' . (empty($vehicle['VehicleID']) ? '0' : $vehicle['VehicleID']) . '">'
                     . $vehicle['VehicleName'] . '</option>';
}
$vehicle = reset($ayaVehicles);

echo '<select id="vehicle-selector" class="form-control selectpicker show-menu-arrow show-tick"
              data-initial-vehicle="' . (empty($vehicle['VehicleID']) ? '0' : $vehicle['VehicleID']) . '" data-show-subtext="true" data-size="10"
              data-width="100%" title="' . (empty($ayaVehicles) ? 'Kein aktiver Eintrag vorhanden' : 'Bitte Fahrzeug auswählen') . '!">
                      ' . $vehiclesList . '
                      <option id="post-vehicle" value="0">Neues Fahrzeug hinzufügen</option>
                    </select>
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
            <legend id="editor-inputs">Fahrzeugdaten aktualisieren</legend>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-vehicle">Hersteller</div>
                    <select id="vehicle-manufacturer" class="form-control selectpicker show-menu-arrow show-tick"
                            data-initial-manufacturer="' . (empty($vehicle['ManufacturerID']) ? '0' : $vehicle['ManufacturerID']) . '"
                            data-live-search="true" data-live-search-normalize="true" data-live-search-placeholder="Suchen..."
                            data-live-search-style="startsWith" data-mobile="false" data-select-on-tab="true" data-show-icon="true"
                            data-show-tick="true" data-size="10" data-width="100%" required="required" title="Bitte auswählen!">
                    </select>
                    <div class="input-group-addon">
                      <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-vehicle">Modell</div>
                    <input id="vehicle-model" class="form-control" maxlength="15"
                           placeholder="' . (empty($vehicle['Model']) ? 'Golf V R32' : $vehicle['Model']) . '" required="required" type="text"
                           value="' . (empty($vehicle['Model']) ? '' : $vehicle['Model']) . '" />
                    <div class="input-group-addon">
                      <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-vehicle">Farbe</div>
                    <input id="vehicle-color" autocomplete="off" class="form-control aya-typeahead-vehicle" data-autoSelect="true" data-delay="0"
                           data-items="5" data-minLength="0" data-provide="typeahead" data-showHintOnFocus="true" data-toggle="tooltip"
                           placeholder="' . (empty($vehicle['Color']) ? 'Firespark Red' : $vehicle['Color']) . '" required="required"
                           title="Ohne &bdquo;Metallic&rdquo; u. ä. Zusätze." type="text"
                           value="' . (empty($vehicle['Color']) ? '' : $vehicle['Color']) . '" />
                    <div class="input-group-addon">
                      <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-vehicle">Kennzeichen</div>
                    <input id="vehicle-registration-number" class="form-control" data-toggle="tooltip" maxlength="11"
                           pattern="^[A-Z09]{1,3}[\s]{1}[A-Z0-9]{1,5}(?:[\s]{1}[A-Z0-9]{1,4})*$"
                           placeholder="' . (empty($vehicle['RegistrationNumber']) ? 'A YA ' . date('Y') : $vehicle['RegistrationNumber']) . '"
                           required="required" title="Format: Großbuchstaben, Zahlen und Leerzeichen statt Striche." type="text"
                           value="' . (empty($vehicle['RegistrationNumber']) ? '' : $vehicle['RegistrationNumber']) . '" />
                    <!-- DE: X[XX] X[X] 1[234], NL: X[XX]/1[11]-X[XX]/1[11]-X[XX]/1[11], PL: XX[X] X[XXXX]/1[1111] -->
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
                    <div class="input-group-addon aya-label aya-label-vehicle">Komponenten<br />inkl. UVP in €</div>
                    <textarea id="vehicle-components" class="form-control aya-monospace" data-toggle="tooltip"
                              placeholder="' . (empty($vehicle['Components']) ? 'Nur in Klassen mit Preisbegrenzung angeben!' : $vehicle['Components']) . '"
                              rows="5" title="Angaben (ohne Zeilenumbrüche!) nur bei Teilnahme in Klassen mit Preisbegrenzung.">'
                                . (empty($vehicle['Components']) ? '' : $vehicle['Components']) . '</textarea>
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
        <button id="save" class="btn btn-aya" disabled="disabled" type="button">Hinzufügen</button>
      </div>
    </div>
  </div>
</div>';
?>
