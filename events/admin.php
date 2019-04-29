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

require_once('db-initialization.php');

if (!$isAdmin)
{
  header('Location: /events/listing.php', true, 303);
  die('Only administrators beyond this point! Sorry.');
}

$title = 'AYA — Wettbewerbsadministration 4.8';
require_once('fragments/header.php');

$showDistance = false;
$showDistanceShortened = false;
$showMap = true;
require_once('fragments/navigation.php');
?>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-10">
      <div class="panel panel-aya">
        <div class="panel-heading">
          <span class="glyphicon glyphicon-calendar"></span> Wettbewerbe <span id="events-year"><?=date('Y');?></span>
        </div>
        <div class="panel-body panel-scrollable">
          <div class="table-responsive">
            <table id="events" class="table table-hover table-striped">
              <thead>
                <tr>
                  <th class="text-center"><span class="glyphicon glyphicon-check"></span></th>
                  <th class="text-center">Datum</th>
                  <th class="text-center">Veranstaltungsname</th>
                  <th class="text-center">Austragungsort</th>
                  <th class="text-center">Stand</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
        <div class="panel-footer">
          <span class="glyphicon glyphicon-list-alt"></span> Aktionen:
          <button id="events-create" class="btn btn-aya" type="button"><span class="glyphicon glyphicon-plus"></span> Hinzufügen</button>
          <button id="events-update" class="btn btn-aya-default" type="button"><span class="glyphicon glyphicon-edit"></span> Aktualisieren</button>
          <button id="events-delete" class="btn btn-aya-default btn-danger" type="button"><span class="glyphicon glyphicon-remove"></span> Löschen</button>
          <button id="events-export" class="btn btn-aya-default btn-success" type="button"><span class="glyphicon glyphicon-export"></span> Exportieren</button>
          <select id="events-year-selector" class="form-control selectpicker show-menu-arrow show-tick" data-size="10" data-width="auto"
                  required="required" title="Jahr">
<?php
try
{
  $max = $db->prepare('SELECT MAX(YEAR(Date))
                       FROM aya_events');
  $max->execute();
  $maxEventYear = $max->fetchColumn();
  $max = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

for ($year = 2017; $year <= $maxEventYear; $year++)
{
  echo '<option' . ($year == date('Y') ? ' selected="selected"' : '') . '>' . $year . '</option>';
}
?>
          </select>
        </div>
      </div>
    </div>
    <div class="col-md-1"></div>
  </div>
  <div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-5">
      <div class="panel panel-aya">
        <div class="panel-heading"><span class="glyphicon glyphicon-flag"></span> Austragungsorte</div>
        <div class="panel-body panel-scrollable">
          <div class="table-responsive">
            <table id="locations" class="table table-hover table-striped">
              <thead>
                <tr>
                  <th class="text-center"><span class="glyphicon glyphicon-check"></span></th>
                  <th class="text-center">Name</th>
                  <th class="text-center">Adresse</th>
                  <th class="text-center">Funktionen</th>
                </tr>
              </thead>
              <tbody>
<?php
try
{
  $locations = $db->prepare("SELECT LocationID, Deleted, Name, CONCAT(CONCAT_WS(' ', Street, StreetNumber), ',') AS Street,
                             CONCAT_WS(' ', ZIP, City) AS City, ST_X(Coordinates) AS Latitude, ST_Y(Coordinates) AS Longitude, HostUrl
                             FROM aya_locations
                             ORDER BY Deleted ASC, Name ASC");
  $locations->execute();

  while ($location = $locations->fetch())
  {
    echo '<tr>
            <td class="text-center"><input data-location-id="' . $location['LocationID'] . '" type="checkbox" /></td>
            <td class="text-center">' . ($location['Deleted'] ? '<del>' : '') . $location['Name'] . ($location['Deleted'] ? '</del>' : '') . '</td>
            <td class="text-center">' . ($location['Deleted'] ? '<del>' : '') . '<span class="text-nowrap">' . $location['Street']
              . '</span> <span class="text-nowrap">' . $location['City'] . '</span>' . ($location['Deleted'] ? '</del>' : '') . '</td>
            <td class="text-center">'
              . (empty($location['Latitude']) ? '' : '<a href="//www.google.de/maps/search/' . $location['Name'] . '/@' . $location['Latitude']
                . ',' . $location['Longitude'] . ',10z' . '"><span class="glyphicon glyphicon-map-marker"></span></a>')
              . (empty($location['HostUrl']) ? '' : '<a href="//' . $location['HostUrl'] . '"><span class="glyphicon glyphicon-globe"></span></a>') . '
            </td>
          </tr>';
  }

  $locations = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}
?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="panel-footer">
          <span class="glyphicon glyphicon-list-alt"></span> Aktionen:
          <button id="locations-create" class="btn btn-aya" type="button"><span class="glyphicon glyphicon-plus"></span> Hinzufügen</button>
          <button id="locations-update" class="btn btn-aya-default" type="button"><span class="glyphicon glyphicon-edit"></span> Aktualisieren</button>
          <button id="locations-delete" class="btn btn-aya-default btn-danger" type="button"><span class="glyphicon glyphicon-remove"></span> Löschen</button>
        </div>
      </div>
    </div>
    <div class="col-md-5">
      <div class="panel panel-aya">
        <div class="panel-heading">
          <span class="glyphicon glyphicon-th-list"></span> Klassen
        </div>
        <div class="panel-body panel-scrollable">
          <div class="table-responsive">
            <table id="classes" class="table table-hover table-striped">
              <thead>
                <tr>
                  <th class="text-center"><span class="glyphicon glyphicon-check"></span></th>
                  <th class="text-center">Name</th>
                  <th class="text-center">Preisbegrenzung</th>
                  <th class="text-center">Sortierung</th>
                </tr>
              </thead>
              <tbody>
<?php
try
{
  $classes = $db->prepare('SELECT ClassID, Deleted, Name, PriceLimited, SortKey
                           FROM aya_classes
                           ORDER BY Deleted ASC, SortKey ASC');
  $classes->execute();

  while ($class = $classes->fetch())
  {
    echo '<tr>
            <td class="text-center"><input data-class-id="' . $class['ClassID'] . '" type="checkbox" /></td>
            <td class="text-center">' . ($class['Deleted'] ? '<del>' : '') . $class['Name'] . ($class['Deleted'] ? '</del>' : '') . '</td>
            <td class="text-center">' . ($class['Deleted'] ? '<del>' : '')
              . (empty($class['PriceLimited']) ? '' : '<span class="glyphicon glyphicon-ok"></span>') . ($class['Deleted'] ? '</del>' : '') . '</td>
            <td class="text-center">' . ($class['Deleted'] ? '<del>' : '') . $class['SortKey'] . ($class['Deleted'] ? '</del>' : '') . '</td>
          </tr>';
  }

  $classes = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}
?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="panel-footer">
          <span class="glyphicon glyphicon-list-alt"></span> Aktionen:
          <button id="classes-create" class="btn btn-aya" type="button"><span class="glyphicon glyphicon-plus"></span> Hinzufügen</button>
          <button id="classes-update" class="btn btn-aya-default" type="button"><span class="glyphicon glyphicon-edit"></span> Aktualisieren</button>
          <button id="classes-delete" class="btn btn-aya-default btn-danger" type="button"><span class="glyphicon glyphicon-remove"></span> Löschen</button>
        </div>
      </div>
    </div>
    <div class="col-md-1"></div>
  </div>
  <div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-5">
      <div class="panel panel-aya">
        <div class="panel-heading">
          <span class="glyphicon glyphicon-bed"></span> Fahrzeughersteller
        </div>
        <div class="panel-body panel-scrollable">
          <div class="table-responsive">
            <table id="manufacturers" class="table table-hover table-striped">
              <thead>
                <tr>
                  <th class="text-center"><span class="glyphicon glyphicon-check"></span></th>
                  <th class="text-center">Name</th>
                  <th class="text-center">Suchbegriffe</th>
                </tr>
              </thead>
              <tbody>
<?php
try
{
  $manufacturers = $db->prepare('SELECT ManufacturerID, Deleted, Name, Keywords
                                 FROM aya_manufacturers
                                 ORDER BY Deleted ASC, Name ASC');
  $manufacturers->execute();

  while ($manufacturer = $manufacturers->fetch())
  {
    echo '<tr>
            <td class="text-center"><input data-manufacturer-id="' . $manufacturer['ManufacturerID'] . '" type="checkbox" /></td>
            <td class="text-center">' . ($manufacturer['Deleted'] ? '<del>' : '') . $manufacturer['Name'] . ($manufacturer['Deleted'] ? '</del>' : '') . '</td>
            <td class="text-center">' . ($manufacturer['Deleted'] ? '<del>' : '') . $manufacturer['Keywords'] . ($manufacturer['Deleted'] ? '</del>' : '') . '</td>
          </tr>';
  }

  $manufacturers = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}
?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="panel-footer">
          <span class="glyphicon glyphicon-list-alt"></span> Aktionen:
          <button id="manufacturers-create" class="btn btn-aya" type="button"><span class="glyphicon glyphicon-plus"></span> Hinzufügen</button>
          <button id="manufacturers-update" class="btn btn-aya-default" type="button"><span class="glyphicon glyphicon-edit"></span> Aktualisieren</button>
          <button id="manufacturers-delete" class="btn btn-aya-default btn-danger" type="button"><span class="glyphicon glyphicon-remove"></span> Löschen</button>
        </div>
      </div>
    </div>
    <div class="col-md-5">
      <div class="panel panel-aya">
        <div class="panel-heading">
          <span class="glyphicon glyphicon-tint"></span> Fahrzeugfarben
        </div>
        <div class="panel-body panel-scrollable">
          <div class="table-responsive">
            <table id="colors" class="table table-hover table-striped">
              <thead>
                <tr>
                  <th class="text-center"><span class="glyphicon glyphicon-check"></span></th>
                  <th class="text-center">Name</th>
                </tr>
              </thead>
              <tbody>
<?php
try
{
  $colors = $db->prepare('SELECT ColorID, Deleted, Name
                          FROM aya_colors
                          ORDER BY Deleted ASC, Name ASC');
  $colors->execute();

  while ($color = $colors->fetch())
  {
    echo '<tr>
            <td class="text-center"><input data-color-id="' . $color['ColorID'] . '" type="checkbox" /></td>
            <td class="text-center">' . ($color['Deleted'] ? '<del>' : '') . $color['Name'] . ($color['Deleted'] ? '</del>' : '') . '</td>
          </tr>';
  }

  $colors = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}
?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="panel-footer">
          <span class="glyphicon glyphicon-list-alt"></span> Aktionen:
          <button id="colors-create" class="btn btn-aya" type="button"><span class="glyphicon glyphicon-plus"></span> Hinzufügen</button>
          <button id="colors-update" class="btn btn-aya-default" type="button"><span class="glyphicon glyphicon-edit"></span> Aktualisieren</button>
          <button id="colors-delete" class="btn btn-aya-default btn-danger" type="button"><span class="glyphicon glyphicon-remove"></span> Löschen</button>
        </div>
      </div>
    </div>
    <div class="col-md-1"></div>
  </div>
</div>
<?php
$db = null;
require_once('fragments/footer.php');
?>
</body>
</html>
