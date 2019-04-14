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

if (!$isAdmin)
{
  die('Only administrators beyond this point! Sorry.');
}

try
{
  $check = $db->prepare('SELECT COUNT(*)
                         FROM aya_events
                         WHERE EventID != :id
                           AND Name LIKE :name
                           AND Date LIKE :date');
  $check->bindValue(':id', $_POST['EventID'], PDO::PARAM_INT);
  $check->bindValue(':name', $_POST['Name'], PDO::PARAM_STR);
  $check->bindValue(':date', $_POST['Date'], PDO::PARAM_STR);
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
  if (!empty($_POST['Name']) && !empty($_POST['LocationID']) && !empty($_POST['Date']))
  {
    try
    {
      $update = $db->prepare('UPDATE aya_events
                              SET Name = :name, LocationID = :locationId, Date = :date, Description = :description, ClassLimits = :limits
                              WHERE EventID = :eventId');
      $update->bindValue(':name', $_POST['Name'], PDO::PARAM_STR);
      $update->bindValue(':locationId', $_POST['LocationID'], PDO::PARAM_INT);
      $update->bindValue(':date', date('Y-m-d H:i:s', strtotime($_POST['Date'])), PDO::PARAM_STR);
      $update->bindValue(':description', (empty($_POST['Description']) ? null : $_POST['Description']), PDO::PARAM_STR);
      $update->bindValue(':limits', $_POST['ClassLimits'], PDO::PARAM_STR);
      $update->bindValue(':eventId', $_POST['EventID'], PDO::PARAM_INT);
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
