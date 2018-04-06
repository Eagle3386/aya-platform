<?php
/**********
 * File:    put-vehicle.php - update vehicle for AYA event
 * Version: 2.7
 * Date:    2018-01-05
 * Author:  Martin Baranski, TroubleZone.Net Productions
 * Licence: Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)
 *          (see: https://creativecommons.org/licenses/by-sa/4.0/ for details)
 **********/

require_once('../db-initialization.php');

try
{
  $check = $db->prepare('SELECT COUNT(*)
                         FROM aya_vehicles
                         WHERE VehicleID != :id
                           AND RegistrationNumber LIKE :number');
  $check->bindValue(':id', $_POST['VehicleID'], PDO::PARAM_INT);
  $check->bindValue(':number', $_POST['RegistrationNumber'], PDO::PARAM_STR);
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
  if (!empty($_POST['Model']) && !empty($_POST['Color']) && !empty($_POST['RegistrationNumber']))
  {
    try
    {
      $update = $db->prepare('UPDATE aya_vehicles
                              SET ManufacturerID = :manufacturerId, Model = :model, Color = :color, RegistrationNumber = :number, Components = :components
                              WHERE phpBBUserID = :userId
                                AND VehicleID = :vehicleId');
      $update->bindValue(':manufacturerId',  $_POST['ManufacturerID'], PDO::PARAM_INT);
      $update->bindValue(':model', $_POST['Model'], PDO::PARAM_STR);
      $update->bindValue(':color', $_POST['Color'], PDO::PARAM_STR);
      $update->bindValue(':number', $_POST['RegistrationNumber'], PDO::PARAM_STR);
      $update->bindValue(':components', (empty($_POST['Components']) ? null : str_replace(array("\r\n", "\r", "\n"), ' ', $_POST['Components'])),
                         PDO::PARAM_STR);
      $update->bindValue(':userId', $phpBBUserID, PDO::PARAM_INT);
      $update->bindValue(':vehicleId', $_POST['VehicleID'], PDO::PARAM_INT);
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
