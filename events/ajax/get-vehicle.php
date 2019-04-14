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

header('Content-type: application/json');
require_once('../db-initialization.php');

try
{
  $vehicle = $db->prepare('SELECT M.ManufacturerID, V.Model, V.Color, V.RegistrationNumber, V.Components
                           FROM aya_vehicles V
                           JOIN aya_manufacturers M ON M.ManufacturerID = V.ManufacturerID
                           WHERE V.Deleted = FALSE
                             AND V.VehicleID = :id');
  $vehicle->bindValue(':id', $_POST['VehicleID'], PDO::PARAM_INT);
  $vehicle->execute();
  $ayaVehicle = $vehicle->fetch(PDO::FETCH_ASSOC);
  $vehicle = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

$db = null;

echo json_encode((empty($ayaVehicle) ? array('result' => false) : $ayaVehicle),
                 JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
?>
