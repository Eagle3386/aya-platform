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
      $insert = $db->prepare('INSERT
                              INTO aya_events (Name, LocationID, Date, Description, ClassLimits)
                              VALUES (:name, :locationId, :date, :description, :limits)');
      $insert->bindValue(':name', $_POST['Name'], PDO::PARAM_STR);
      $insert->bindValue(':locationId', $_POST['LocationID'], PDO::PARAM_INT);
      $insert->bindValue(':date', date('Y-m-d H:i:s', strtotime($_POST['Date'])), PDO::PARAM_STR);
      $insert->bindValue(':description', (empty($_POST['Description']) ? null : $_POST['Description']), PDO::PARAM_STR);
      $insert->bindValue(':limits', $_POST['ClassLimits'], PDO::PARAM_STR);
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
