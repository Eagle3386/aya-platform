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
  ShowException($exception);
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
      $update->bindValue(':number', strtoupper($_POST['RegistrationNumber']), PDO::PARAM_STR);
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
      ShowException($exception);
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
