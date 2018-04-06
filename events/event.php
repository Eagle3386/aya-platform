<?php
/**********
 * File:    event.php - AYA event page
 * Version: 6.34
 * Date:    2018-01-30
 * Author:  Martin Baranski, TroubleZone.Net Productions
 * Licence: Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)
 *          (see: https://creativecommons.org/licenses/by-sa/4.0/ for details)
 **********/

require_once('db-initialization.php');

try
{
  $event = $db->prepare("SELECT E.EventID, E.Name, E.Date, E.Description AS EventDescription, E.LastUpdate,
                           CONCAT_WS(' ', CONCAT(CONCAT_WS(' ', L.Street, L.StreetNumber), ','), L.Zip, L.City) AS Address,
                           L.Description AS LocationDescription, AsText(L.Coordinates) AS Coordinates, E.ClassLimits
                         FROM aya_events E
                         JOIN aya_locations L
                           ON E.LocationID = L.LocationID
                         WHERE EventID = :id");
  $event->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
  $event->execute();
  $ayaEvent = $event->fetch(PDO::FETCH_ASSOC);
  $event = null;
}
catch (PDOException $exception)
{
  print 'Error: ' . $exception->getMessage() . '<br />';
}

$title = $ayaEvent['Name'] . ' — AYA-Teilnahme 4.1';
require_once('fragments/header.php');

$isEventPage = true;
$showDistance = true;
$showDistanceShortened = false;
$showListingLink = true;
$showMap = true;
require_once('fragments/navigation.php');

$classesPerRow = 5;
?>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-5">
      <div class="panel panel-aya">
        <div class="panel-heading">
          <span class="glyphicon glyphicon-calendar"></span> Termin: <?=$ayaEvent['Name'] . ' — ' . date('d.m.Y, H:i', strtotime($ayaEvent['Date']));?>
        </div>
        <div class="panel-body">
          <p>
<?php
              $eventDescription = preg_replace('/(?:https?:\/\/)|([a-zA-Z0-9@\.+-_]*[a-zA-Z-\.]{1,63}[a-zA-Z-]{2,63}\.[a-zA-Z0-9]{2,})/i',
                '<a href="//$1">$1</a>', $ayaEvent['EventDescription']);
              echo nl2br($eventDescription, true);
?>
          </p>
          <button class="attendance btn btn-aya btn-lg btn-block"<?=(($phpBBUserID < 2) || (date('Y-m-d H:i:s') > date($ayaEvent['Date']))
            ? ' disabled="disabled"' : '');?> data-event-id="<?=$ayaEvent['EventID'];?>" type="button"><?=((date('Y-m-d H:i:s') > date($ayaEvent['Date']))
            ? 'Anmeldung abgeschlossen' : 'Teilnehmen');?></button>
        </div>
        <div class="panel-footer">
          <span class="glyphicon glyphicon-time"></span> Letzte Aktualisierung: <?=date('d.m.Y, H:i', strtotime($ayaEvent['LastUpdate']));?>
        </div>
      </div>
    </div>
    <div class="col-md-5">
      <div class="panel panel-aya">
        <div class="panel-heading">
          <span class="glyphicon glyphicon-flag"></span> Austragungsort: <span class="aya-location"><?=$ayaEvent['Address'];?></span>
        </div>
        <div class="panel-body">
          <p><?=nl2br($ayaEvent['LocationDescription'], true);?></p>
          <p id="location-map"></p>
        </div>
<?php
if (($phpBBUserID > 1) && $showDistance)
{
  echo '
        <div class="panel-footer aya-event">
          <address title="' . $ayaEvent['Address'] . '">
            <span class="glyphicon glyphicon-road"></span> Entfernung: <span class="distance"></span>
          </address>
        </div>';
}
?>
      </div>
    </div>
    <div class="col-md-1"></div>
  </div>
  <div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-10">
      <div class="panel panel-aya">
        <div class="panel-heading">
<?php
$attendees = 0;
try
{
  $attendeesTotal = $db->prepare('SELECT COUNT(*)
                                  FROM aya_attendees
                                  WHERE EventID = :id
                                    AND Deleted = FALSE');
  $attendeesTotal->bindValue(':id', $ayaEvent['EventID'], PDO::PARAM_INT);
  $attendeesTotal->execute();
  $attendees = $attendeesTotal->fetchColumn();
  $attendeesTotal = null;
}
catch (PDOException $exception)
{
  print 'Error: ' . $exception->getMessage() . '<br />';
}
?>
          <span class="glyphicon glyphicon-list"></span> Teilnehmer: <?=$attendees;?> — Stand: <?=date('d.m.Y, H:i');?>
        </div>
        <div class="panel-body">
          <div class="row">
<?php
try
{
  $classes = $db->prepare('SELECT ClassID, Name, PriceLimited
                           FROM aya_classes
                           WHERE Deleted = FALSE
                           ORDER BY SortKey ASC');
  $classes->execute();
  $classLimits = json_decode($ayaEvent['ClassLimits'], true);
  $i = 1;
  while ($class = $classes->fetch(PDO::FETCH_ASSOC))
  {
    $attendeesClass = 0;
    try
    {
      $classTotal = $db->prepare('SELECT COUNT(*)
                                  FROM aya_attendees
                                  WHERE EventID = :eventId
                                    AND Deleted = FALSE
                                    AND ClassID = :classId');
      $classTotal->bindValue(':eventId', $ayaEvent['EventID'], PDO::PARAM_INT);
      $classTotal->bindValue(':classId', $class['ClassID'], PDO::PARAM_INT);
      $classTotal->execute();
      $attendeesClass = $classTotal->fetchColumn();
      $classTotal = null;
    }
    catch (PDOException $exception)
    {
      print 'Error: ' . $exception->getMessage() . '<br />';
    }

    $attendeesLimit = $classLimits[($class['ClassID'])] + 0; // Workaround for undefined class limits // TODO: Check, if workaround is still needed!
    $slotsFree = ($attendeesLimit - $attendeesClass);
    echo '<div class="col-md-5ths">
            <fieldset>
              <legend>
                <div class="text-nowrap">' . $class['Name'] . '</div>
                <div class="progress">
                  <div class="progress-bar progress-bar-' . ($slotsFree > 3 ? 'success' : ($slotsFree > 0 ? 'warning' : 'danger'))
                    . ' progress-bar-striped active" role="progressbar" aria-valuenow="' . ((float)$attendeesClass / $attendeesLimit * 100)
                    . '" aria-valuemin="0" aria-valuemax="100">
                    <span>' . $attendeesClass . ' / ' . $attendeesLimit . ' belegt</span>
                  </div>
                </div>
              </legend>';

    if ($phpBBUserID > 1)
    {
      echo '<ul class="list-group" data-class-id="' . $class['ClassID'] . '">';

      try
      {
        $attendees = $db->prepare('SELECT A.AttendeeID, U.username AS Username, P.pf_teamname AS TeamName
                                   FROM aya_attendees A
                                   JOIN phpbb_users U
                                     ON A.phpBBUserID = U.user_id
                                   JOIN phpbb_profile_fields_data P
                                     ON A.phpBBUserID = P.user_id
                                   WHERE A.EventID = :eventId
                                     AND A.Deleted = FALSE
                                     AND A.ClassID = :classId
                                   ORDER BY U.username ASC');
        $attendees->bindValue(':eventId', $ayaEvent['EventID'], PDO::PARAM_INT);
        $attendees->bindValue(':classId', $class['ClassID'], PDO::PARAM_INT);
        $attendees->execute();

        while ($attendee = $attendees->fetch(PDO::FETCH_ASSOC))
        {
          echo '<li class="list-group-item" data-attendee-id="' . $attendee['AttendeeID'] . '">';
          if (empty($attendee['TeamName']))
          {
            echo $attendee['Username'];
          }
          else
          {
            echo '<div class="row">
  <div class="col-md-7">' . $attendee['Username'] . '</div>
  <div class="col-md-5">
    <span class="label" title="' . $attendee['TeamName'] . '">' . $attendee['TeamName'] . '</span>
  </div>
</div>';
          }
          echo '</li>';
        }
        $attendees = null;
      }
      catch (PDOException $exception)
      {
        print 'Error: ' . $exception->getMessage() . '<br />';
      }

      echo '</ul>';
    }

    echo '</fieldset></div>';

    if ($i++ % $classesPerRow == 0)
    {
      echo '</div><div class="row">';
    }
  }
}
catch (PDOException $exception)
{
  print 'Error: ' . $exception->getMessage() . '<br />';
}

$classes = null;
$db = null;
?>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-1"></div>
  </div>
</div>
<?php
require_once('fragments/footer.php');
?>
</body>
</html>
