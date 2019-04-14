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

$attendeeID = $_POST['AttendanceID'];
$classID = $_POST['ClassID'];
$eventID =  $_POST['EventID'];
$vehicleID = $_POST['VehicleID'];

if (!empty($attendeeID) && !empty($classID) && !empty($eventID) && !empty($vehicleID))
{
  try
  {
    $check = $db->prepare('SELECT COUNT(*)
                           FROM aya_attendances
                           WHERE Deleted = FALSE
                             AND AttendanceID != :attendeeId
                             AND EventID = :eventId
                             AND VehicleID = :vehicleId');
    $check->bindValue(':attendeeId', $attendeeID, PDO::PARAM_INT);
    $check->bindValue(':eventId', $eventID, PDO::PARAM_INT);
    $check->bindValue(':vehicleId', $vehicleID, PDO::PARAM_INT);
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
    try
    {
      $check = $db->prepare('SELECT COUNT(A.AttendanceID) AS Attendees, E.ClassLimits
                             FROM aya_attendances A
                             JOIN aya_events E ON E.EventID = A.EventID
                             WHERE A.Deleted = FALSE
                               AND A.EventID = :eventId
                               AND A.ClassID = :classId');
      $check->bindValue(':eventId', $eventID, PDO::PARAM_INT);
      $check->bindValue(':classId', $classID, PDO::PARAM_INT);
      $check->execute();
      $usage = $check->fetch(PDO::FETCH_ASSOC);
      $check = null;
    }
    catch (PDOException $exception)
    {
      ShowException($exception);
    }

    $classLimits = json_decode($usage['ClassLimits'], true);
    if (($usage['Attendees'] + 0) < ($classLimits[$classID] + 0))
    {
      try
      {
        $attendance = $db->prepare('UPDATE aya_attendances
                                    SET ClassID = :classId, VehicleID = :vehicleId, Remark = :remark
                                    WHERE phpBBUserID = :userId
                                      AND AttendanceID = :attendanceId');
        $attendance->bindValue(':classId', $classID, PDO::PARAM_INT);
        $attendance->bindValue(':vehicleId', $vehicleID, PDO::PARAM_INT);
        $attendance->bindValue(':remark', (empty($_POST['Remark']) ? null : $_POST['Remark']), PDO::PARAM_STR);
        $attendance->bindValue(':userId', $phpBBUserID, PDO::PARAM_INT);
        $attendance->bindValue(':attendanceId', $attendeeID, PDO::PARAM_INT);
        $attendance->execute();
        echo $attendance->rowCount();
        $attendance = null;
      }
      catch (PDOException $exception)
      {
        ShowException($exception);
      }
    }
    else
    {
      echo 'CLASS_FULL';
    }
  }
  else
  {
    echo 'ALREADY_ATTENDING';
  }
}
else
{
  echo 'MISSING_DATA';
}

$db = null;
?>
