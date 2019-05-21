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

$type = $_POST['Type'];
if (!$isAdmin && !empty($type) && $type !== 'attendances' && $type !== 'vehicles')
{
  die('Only administrators beyond this point! Sorry.');
}

$entities = json_decode($_POST['Entities'], true);
if (!empty($entities))
{
  $placeholders = null;
  foreach ($entities as $index => $entityId)
  {
    $placeholders .= ':id' . $index . ', ';
    $entityIds[(':id' . $index)] = $entityId;
  }
  $placeholders = rtrim($placeholders, ', ');
  $typeId = '`' . str_replace('`', '``', ucfirst(substr($type, 0, (stripos($type, 'ses') > 3 ? -2 : -1))) . 'ID') . '`';
  $type = '`' . str_replace('`', '``', 'aya_' . $type) . '`';
  try
  {
    $check = $db->prepare('SELECT COUNT(*)
                           FROM ' . $type . '
                           WHERE Deleted = FALSE
                             ' . ($isAdmin ? '' : 'AND phpBBUserID = ' . $phpBBUserID) . '
                             AND ' . $typeId . ' IN (' . $placeholders . ')');
    $check->execute($entityIds);
    $exists = $check->fetchColumn();
    $check = null;
  }
  catch (PDOException $exception)
  {
    ShowException($exception);
  }

  if ($exists > 0)
  {
    $IsUsed = 0;
    if ($type === 'aya_vehicles')
    {
      try
      {
        $check = $db->prepare("SELECT COUNT(*)
                               FROM aya_attendances
                               WHERE Deleted = FALSE
                                 AND ConfirmationDate BETWEEN CONCAT(YEAR(CURDATE()), '-01-01') AND CONCAT(YEAR(CURDATE()), '-12-31')
                                 AND " . $typeId . " IN (" . $placeholders . ")");
        $check->execute($entityIds);
        $IsUsed = $check->fetchColumn();
        $check = null;
      }
      catch (PDOException $exception)
      {
        ShowException($exception);
      }
    }

    if ($IsUsed < 1)
    {
      try
      {
        $delete = $db->prepare('UPDATE ' . $type . '
                                SET Deleted = TRUE
                                WHERE ' . ($isAdmin ? '' : 'phpBBUserID = ' . $phpBBUserID . '
                                  AND ') . $typeId . ' IN (' . $placeholders . ')');
        $delete->execute($entityIds);
        $rows = $delete->rowCount();
        echo (count($entities) === $rows ? $rows : 'COUNT_MISMATCH');
        $delete = null;
      }
      catch (PDOException $exception)
      {
        ShowException($exception);
      }
    }
    else
    {
    echo 'ALREADY_USED';
    }
  }
  else
  {
    echo 'ALREADY_DELETED';
  }
}
else
{
  echo 'MISSING_DATA';
}

$db = null;
?>
