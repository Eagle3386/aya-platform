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
  $class = $db->prepare('SELECT ClassID, Name, PriceLimited, SortKey
                         FROM aya_classes
                         WHERE Deleted = FALSE
                           AND ClassID = :id');
  $class->bindValue(':id', (empty($_POST['ClassID']) ? 0 : $_POST['ClassID']), PDO::PARAM_INT);
  $class->execute();
  $ayaClass = $class->fetch(PDO::FETCH_ASSOC);
  $class = null;

  $ayaNextKey = 99;
  $nextKey = $db->prepare('SELECT MAX(SortKey) + 1 AS NextKey
                           FROM aya_classes');
  $nextKey->execute();
  $nextKey->bindColumn('NextKey', $ayaNextKey, PDO::PARAM_INT);
  $nextKey->fetch(PDO::FETCH_BOUND);
  $nextKey = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

echo '<div id="classes-editor-dialog" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button class="close" data-dismiss="modal" type="button" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title">Klassenverwaltung</h3>
      </div>
      <div class="modal-body">
        <form id="class-form" data-class-id="' . (empty($ayaClass['ClassID']) ? 0 : $ayaClass['ClassID']) . '" data-toggle="validator">
          <fieldset>
            <legend>Klassendaten</legend>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-event">Klassenname</div>
                    <input id="class-name" class="form-control" maxlength="30"
                           placeholder="' . (empty($ayaClass['Name']) ? 'Premier 5.000' : $ayaClass['Name']) . '" type="text"
                           value="' . (empty($ayaClass['Name']) ? '' : $ayaClass['Name']) . '" />
                    <div class="input-group-addon">
                      <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-7">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label aya-label-event">Preisbegrenzung</div>
                    <div id="class-price" class="btn-group" data-toggle="buttons">';

$isLimited = !empty($ayaClass['PriceLimited']);
echo '<label class="btn btn-aya-default btn-aya-toggle' . ($isLimited ? ' active' : '') . '">
                        <input id="class-price-limited" checked="' . ($isLimited ? 'checked' : 'false') . '" data-initial-value="'
                                 . ($isLimited ? 'on' : 'off') . '" name="priceLimitOptions" type="radio" /> Ja
                      </label>
                      <label class="btn btn-aya-default' . ($isLimited ? '' : ' active') . '">
                        <input id="class-price-unlimited" checked="' . ($isLimited ? 'false' : 'checked') . '" data-initial-value="'
                                 . ($isLimited ? 'off' : 'on') . '" name="priceLimitOptions" type="radio" /> Nein
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-5">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon aya-label">Sortierung</div>
                    <input id="class-sort-key" class="form-control text-right" max="99" min="0" placeholder="' . $ayaNextKey . '" step="1"
                           title="Zahl zwischen 1 und 99." type="number" value="' . (empty($ayaClass['SortKey']) ? $ayaNextKey : $ayaClass['SortKey']) . '" />
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
        <button id="save" class="btn btn-aya" disabled="disabled" type="button">Hinzufügen</button>
      </div>
    </div>
  </div>
</div>';

$db = null;
?>
