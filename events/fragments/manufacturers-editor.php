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
  $manufacturer = $db->prepare('SELECT ManufacturerID, Name, Keywords
                                FROM aya_manufacturers
                                WHERE Deleted = FALSE
                                  AND ManufacturerID = :id');
  $manufacturer->bindValue(':id', (empty($_POST['ManufacturerID']) ? 0 : $_POST['ManufacturerID']), PDO::PARAM_INT);
  $manufacturer->execute();
  $ayaManufacturer = $manufacturer->fetch(PDO::FETCH_ASSOC);
  $manufacturer = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

echo '<div id="manufacturers-editor-dialog" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button class="close" data-dismiss="modal" type="button" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title">Herstellerverwaltung</h3>
      </div>
      <div class="modal-body">
        <form id="manufacturer-form" data-manufacturer-id="' . (empty($ayaManufacturer['ManufacturerID']) ? 0 : $ayaManufacturer['ManufacturerID'])
          . '" data-toggle="validator">
          <fieldset>
            <legend>Herstellerdaten</legend>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-event">Herstellername</div>
                    <input id="manufacturer-name" autocomplete="off" class="form-control aya-typeahead-vehicle" data-autoSelect="true" data-delay="0"
                           data-items="5" data-minLength="0" data-provide="typeahead" data-showHintOnFocus="true" data-toggle="tooltip" maxlength="15"
                           placeholder="' . (empty($ayaManufacturer['Name']) ? 'VW' : $ayaManufacturer['Name']) . '" required="required"
                           title="Ohne &bdquo;GmbH&rdquo; u. ä. Zusätze." type="text"
                           value="' . (empty($ayaManufacturer['Name']) ? '' : $ayaManufacturer['Name']) . '" />
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
                    <div class="input-group-addon aya-label aya-label-event">Suchbegriffe</div>
                    <input id="manufacturer-keywords" autocomplete="off" class="form-control aya-typeahead-vehicle" data-autoSelect="true"
                           data-delay="0" data-items="5" data-minLength="0" data-provide="typeahead" data-showHintOnFocus="true" data-toggle="tooltip"
                           maxlength="50" placeholder="' . (empty($ayaManufacturer['Keywords']) ? 'VW Volkswagen' : $ayaManufacturer['Keywords']) . '"
                           required="required" title="Mit Leerzeichen getrennte Liste." type="text"
                           value="' . (empty($ayaManufacturer['Keywords']) ? '' : $ayaManufacturer['Keywords']) . '" />
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
