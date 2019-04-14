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
  $attendance = $db->prepare('SELECT VehicleID, ClassID, Remark
                              FROM aya_attendances
                              WHERE Deleted = FALSE
                                AND AttendeeID = :id');
  $attendance->bindValue(':id', $_POST['AttendeeID'], PDO::PARAM_INT);
  $attendance->execute();
  $ayaAttendance = $attendance->fetch(PDO::FETCH_ASSOC);
  $attendance = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

$db = null;

echo json_encode((empty($ayaAttendance) ? array('result' => false) : $ayaAttendance),
                 JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
?>
