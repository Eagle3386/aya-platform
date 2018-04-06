<?php
/**********
 * File:    put-event.php - update event for AYA admin
 * Version: 1.11
 * Date:    2018-01-07
 * Author:  Martin Baranski, TroubleZone.Net Productions
 * Licence: Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)
 *          (see: https://creativecommons.org/licenses/by-sa/4.0/ for details)
 **********/

require_once('../db-initialization.php');

if (!$isAdmin)
{
  die('Only administrators beyond this point! Sorry.');
}

try
{
  $check = $db->prepare('SELECT COUNT(*)
                         FROM aya_events
                         WHERE EventID != :id
                           AND Name LIKE :name
                           AND Date LIKE :date');
  $check->bindValue(':id', $_POST['EventID'], PDO::PARAM_INT);
  $check->bindValue(':name', $_POST['Name'], PDO::PARAM_STR);
  $check->bindValue(':date', $_POST['Date'], PDO::PARAM_STR);
  $check->execute();
  $exists = $check->fetchColumn();
  $check = null;
}
catch (PDOException $exception)
{
  print 'Error: ' . $exception->getMessage() . '<br />';
}

if ($exists < 1)
{
  if (!empty($_POST['Name']) && !empty($_POST['LocationID']) && !empty($_POST['Date']))
  {
    try
    {
      $update = $db->prepare('UPDATE aya_events
                              SET Name = :name, LocationID = :locationId, Date = :date, Description = :description, ClassLimits = :limits
                              WHERE EventID = :eventId');
      $update->bindValue(':name', $_POST['Name'], PDO::PARAM_STR);
      $update->bindValue(':locationId', $_POST['LocationID'], PDO::PARAM_INT);
      $update->bindValue(':date', date('Y-m-d H:i:s', strtotime($_POST['Date'])), PDO::PARAM_STR);
      $update->bindValue(':description', (empty($_POST['Description']) ? null : $_POST['Description']), PDO::PARAM_STR);
      $update->bindValue(':limits', $_POST['ClassLimits'], PDO::PARAM_STR);
      $update->bindValue(':eventId', $_POST['EventID'], PDO::PARAM_INT);
      $update->execute();
      echo $update->rowCount();
      $update = null;
    }
    catch (PDOException $exception)
    {
      print 'Error: ' . $exception->getMessage() . '<br />';
    }
  }
  else
  {
    echo 'MISSING_DATA';
  }
}
else
{
  echo 'ALREADY_EXISTS';
}

$db = null;
?>