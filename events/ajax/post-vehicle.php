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
                         WHERE Deleted = FALSE
                           AND VehicleID != :id
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
      $insert = $db->prepare('INSERT
                              INTO aya_vehicles (phpBBUserID, ManufacturerID, Model, Color, RegistrationNumber, Components)
                              VALUES (:userId, :manufacturerID, :model, :color, :number, :components)');
      $insert->bindValue(':userId', $phpBBUserID, PDO::PARAM_INT);
      $insert->bindValue(':manufacturerID', $_POST['ManufacturerID'], PDO::PARAM_INT);
      $insert->bindValue(':model', $_POST['Model'], PDO::PARAM_STR);
      $insert->bindValue(':color', $_POST['Color'], PDO::PARAM_STR);
      $insert->bindValue(':number', $_POST['RegistrationNumber'], PDO::PARAM_STR);
      $insert->bindValue(':components', (empty($_POST['Components']) ? null : str_replace(array("\r\n", "\r", "\n"), ' ', $_POST['Components'])),
                         PDO::PARAM_STR);
      $insert->execute();
      echo $insert->rowCount();
      $insert = null;
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
