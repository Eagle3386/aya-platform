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
  $showDeleted = $_POST['ShowDeleted'] === 'true';
  $query = "SELECT E.EventID, " . ($showDeleted ? "E.Deleted, " : "") . "E.Name, DATE_FORMAT(E.Date, '%Y-%m-%d %H:%i') AS Date, ";
  if (!$showDeleted)  // Address is shown on listing page only
  {
    $query .= "CONCAT(CONCAT_WS(' ', L.Street, L.StreetNumber), ', ', CONCAT_WS(' ', L.ZIP, L.City)) AS Address";
  }
  else
  {
    $query .= 'E.LastUpdate, L.City';
  }
  $query .= ' FROM aya_events E JOIN aya_locations L ON L.LocationID = E.LocationID WHERE ';
  if (!$showDeleted)
  {
    $query .= 'E.Deleted = FALSE';
  }
  else
  {
    $query .= 'YEAR(E.Date) = ' . (is_numeric($_POST['EventYear']) ? $_POST['EventYear'] : date('Y'));
  }
  $query .= ' ORDER BY DATEDIFF(E.Date, CURDATE()) < 0 ASC, ' . (!$showDeleted ? 'E.Deleted ASC, ' : '') . 'E.Date ' . ($showDeleted ? 'DESC' : 'ASC');
  $events = $db->prepare($query);
  $events->execute();
  $ayaEvents = $events->fetchAll(PDO::FETCH_ASSOC);
  $events = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

$db = null;

echo json_encode($ayaEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
?>
