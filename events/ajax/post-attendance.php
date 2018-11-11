<?php
/*
 * Copyright 2016-2018 Martin Arndt, TroubleZone.Net Productions
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
 *
 * File:    post-attendance.php - create attendance for AYA event
 * Version: 2.17
 * Date:    2018-03-18
 */

require_once('../db-initialization.php');

$classID = $_POST['ClassID'];
$eventID =  $_POST['EventID'];
$vehicleID = $_POST['VehicleID'];

if (!empty($classID) && !empty($eventID) && !empty($vehicleID))
{
  try
  {
    $check = $db->prepare('SELECT COUNT(*)
                           FROM aya_attendees
                           WHERE Deleted = FALSE
                             AND EventID = :eventId
                             AND VehicleID = :vehicleId');
    $check->bindValue(':eventId', $eventID, PDO::PARAM_INT);
    $check->bindValue(':vehicleId', $vehicleID, PDO::PARAM_INT);
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
    try
    {
      $check = $db->prepare('SELECT COUNT(A.AttendeeID) AS Attendees, E.ClassLimits
                             FROM aya_attendees A
                             JOIN aya_events E
                               ON A.EventID = E.EventID
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
      print 'Error: ' . $exception->getMessage() . '<br />';
    }

    $classLimits = json_decode($usage['ClassLimits'], true);
    if (($usage['Attendees'] + 0) < ($classLimits[$classID] + 0))
    {
      try
      {
        $attendance = $db->prepare('INSERT
                                    INTO aya_attendees (phpBBUserID, EventID, ClassID, VehicleID, Remark)
                                    VALUES (:userId, :eventId, :classId, :vehicleId, :remark)');
        $attendance->bindValue(':userId', $phpBBUserID, PDO::PARAM_INT);
        $attendance->bindValue(':eventId', $eventID, PDO::PARAM_INT);
        $attendance->bindValue(':classId', $classID, PDO::PARAM_INT);
        $attendance->bindValue(':vehicleId', $vehicleID, PDO::PARAM_INT);
        $attendance->bindValue(':remark', (empty($_POST['Remark']) ? null : $_POST['Remark']), PDO::PARAM_STR);
        $attendance->execute();
        echo $attendance->rowCount();
        $attendance = null;
      }
      catch (PDOException $exception)
      {
        print 'Error: ' . $exception->getMessage() . '<br />';
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
