<?php
/**********
 * File:    post-event.php - create event for AYA admin
 * Version: 1.12
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
      $insert = $db->prepare('INSERT
                              INTO aya_events (Name, LocationID, Date, Description, ClassLimits)
                              VALUES (:name, :locationId, :date, :description, :limits)');
      $insert->bindValue(':name', $_POST['Name'], PDO::PARAM_STR);
      $insert->bindValue(':locationId', $_POST['LocationID'], PDO::PARAM_INT);
      $insert->bindValue(':date', date('Y-m-d H:i:s', strtotime($_POST['Date'])), PDO::PARAM_STR);
      $insert->bindValue(':description', (empty($_POST['Description']) ? null : $_POST['Description']), PDO::PARAM_STR);
      $insert->bindValue(':limits', $_POST['ClassLimits'], PDO::PARAM_STR);
      $insert->execute();
      echo $insert->rowCount();
      $insert = null;
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