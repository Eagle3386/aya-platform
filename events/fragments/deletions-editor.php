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

echo '<div id="deletions-editor-dialog" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button class="close" data-dismiss="modal" type="button" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title">Löschbestätigung</h3>
      </div>
      <div class="modal-body">
        <form id="deletion-form" data-toggle="validator">
          <fieldset>
            <legend>Zu löschende ';

$type = $_POST['Type'];
switch ($type)
{
  case 'attendances':
    echo 'Teilnahmen';
    break;

  case 'classes':
    echo 'Klassen';
    break;

  case 'colors':
    echo 'Farben';
    break;

  case 'events':
    echo 'Wettbewerbe';
    break;

  case 'flaws':
    echo 'Einbau-Mängel';
    break;

  case 'locations':
    echo 'Austragungsorte';
    break;

  case 'manufacturers':
    echo 'Hersteller';
    break;

  case 'vehicles':
    echo 'Fahrzeuge';
    break;
}

echo '</legend>
            <div class="row">
              <div class="col-md-12">
                <ul id="deletion-entities" data-entities-type="' . $type . '">';

$entities = json_decode($_POST['Entities'], true);
foreach ($entities as $entity)
{
  echo '<li data-entity-id="' . $entity['Id'] . '">' . $entity['Name'] . ' (#' . $entity['Id'] . ')</li>';
}

echo '                </ul>
              </div>
            </div>
          </fieldset>
        </form>
        <div id="result" class="alert aya-alert-ajax" role="alert"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-aya-default" data-dismiss="modal" type="button">Abbrechen</button>
        <button id="save" class="btn btn-aya-default btn-danger' . (empty($entities) ? ' disabled' : '') . '"
                type="button">Löschen</button>
      </div>
    </div>
  </div>
</div>';

$db = null;
?>
