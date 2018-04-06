<?php
/**********
 * File:    post-attendance.php - create attendance for AYA event
 * Version: 2.17
 * Date:    2018-03-18
 * Author:  Martin Baranski, TroubleZone.Net Productions
 * Licence: Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)
 *          (see: https://creativecommons.org/licenses/by-sa/4.0/ for details)
 **********/

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
