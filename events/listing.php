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

$title = 'AYA — Wettbewerbsübersicht 3.15';
require_once('fragments/header.php');

$showDistance = true;
$showDistanceShortened = true;
$showListingLink = false;
$showMap = false;
require_once('fragments/navigation.php');
?>
<div class="container-fluid">
  <div class="row">
<?php
try
{
  $events = $db->prepare("SELECT E.EventID, E.Date, E.Name, L.HostUrl,
                            CONCAT_WS(' ', CONCAT(CONCAT_WS(' ', L.Street, L.StreetNumber), ','), L.ZIP, L.City) AS Address
                          FROM aya_events E
                          JOIN aya_locations L ON L.LocationID = E.LocationID
                          WHERE E.Deleted = FALSE
                          ORDER BY DATEDIFF(E.Date, CURDATE()) < 0 ASC, E.Date ASC");
  $events->execute();

  $isFirst = true;
  while ($event = $events->fetch(PDO::FETCH_ASSOC))
  {
    $isFinished = (date('Y-m-d H:i:s') > date($event["Date"]));
    if ($isFinished && $isFirst)
    {
      echo '<div class="clearfix"></div>
              <div class="col-md-12">
                <fieldset>
                  <legend class="text-center">Archivierte Veranstaltungen</legend>
                </fieldset>
              </div>';

      $isFirst = false;
    }

    echo '<div class="col-md-3">
      <div class="thumbnail' . ($isFinished ? ' archived' : '') . ' aya-event">
        <a href="event.php?id=' . $event["EventID"] . '">';

    $timestamp = strtotime($event["Date"]);
    $imagePath = 'images/' . date('Y-m-d', $timestamp) . '.jpg';
    if (!file_exists($_SERVER["DOCUMENT_ROOT"] . '/events/' . $imagePath))
      $imagePath = 'images/placeholder.jpg';

    echo '<img src="' . $imagePath . '" alt="AYA-Wettbewerbslogo — ' . date('d.m.Y, H:i', $timestamp) . '" />
        </a>
        <div class="caption">
          <p>' . date('d.m.Y, H:i', $timestamp) . '</p>
          <h3 title="' . $event["Name"] . '">' . $event["Name"] . '</h3>
          <div class="address">
            <address>' . $event["Address"] . '</address>
            ' . ((!empty($ayaUserLocation) && $showDistance) ? 'Entfernung: <span class="distance"></span>' : '') . '
          </div>
          <button class="attendance btn btn-aya"' . ($isFinished || ($phpBBUserID < 2) ? ' disabled="disabled"' : '') . ' data-event-id="'
            . $event["EventID"] . '" type="button">' . ($isFinished ? 'Abgeschlossen' : 'Teilnehmen') . '</button>
          ' . (empty($event["HostUrl"]) ? '' : '<a class="btn btn-aya-default" href="//' . $event["HostUrl"]
            . '/" rel="external" role="button" target="_blank">Veranstalter <span class="glyphicon glyphicon-new-window"></span></a>') . '
        </div>
      </div>
    </div>';
  }
}
catch (PDOException $exception)
{
  ShowException($exception);
}

$events = null;
$db = null;
?>
  </div>
</div>
<?php
require_once('fragments/footer.php');
?>
</body>
</html>
