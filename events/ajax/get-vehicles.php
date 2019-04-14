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
  $vehicles = $db->prepare('SELECT VehicleID, ManufacturerID, Model, Color, RegistrationNumber, Components
                            FROM aya_vehicles
                            WHERE Deleted = FALSE
                              AND phpBBUserID = :id
                            ORDER BY RegistrationNumber ASC');
  $vehicles->bindValue(':id', $phpBBUserID, PDO::PARAM_INT);
  $vehicles->execute();
  $ayaVehicles = $vehicles->fetchAll(PDO::FETCH_ASSOC);
  $vehicles = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

$db = null;

echo json_encode($ayaVehicles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
?>
