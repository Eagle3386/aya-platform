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
                         FROM aya_classes
                         WHERE ClassID != :id
                           AND Name LIKE :name');
  $check->bindValue(':id', $_POST['ClassID'], PDO::PARAM_INT);
  $check->bindValue(':name', $_POST['Name'], PDO::PARAM_STR);
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
  if (!empty($_POST['Name']) && !empty($_POST['SortKey']))
  {
    try
    {
      $update = $db->prepare('UPDATE aya_classes
                              SET Name = :name, PriceLimited = :limited, SortKey = :key
                              WHERE ClassID = :id');
      $update->bindValue(':name', $_POST['Name'], PDO::PARAM_STR);
      $update->bindValue(':limited', ($_POST['PriceLimited'] === 'true' ? 1 : 0), PDO::PARAM_INT); // PDO fails if using real BOOL -.-
      $update->bindValue(':key', $_POST['SortKey'], PDO::PARAM_INT);
      $update->bindValue(':id', $_POST['ClassID'], PDO::PARAM_INT);
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
