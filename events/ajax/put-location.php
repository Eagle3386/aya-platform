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

$city = $_POST['City'];
$coordinates = null;
if (preg_match('/^([0-9]{1,3}\.[0-9]{3,4}),([0-9]{1,3}\.[0-9]{3,4})$/', $_POST['Coordinates'], $coordinates) !== 1)
{
  $coordinates = false;
}
$locationID = $_POST['LocationID'];
$name = $_POST['Name'];
$street = $_POST['Street'];

if (!empty($city) && !empty($coordinates[1]) && !empty($coordinates[2]) && !empty($locationID) && !empty($name) && !empty($street))
{
  try
  {
    $check = $db->prepare('SELECT COUNT(*)
                           FROM aya_locations
                           WHERE LocationID != :id
                             AND Name LIKE :name');
    $check->bindValue(':id', $locationID, PDO::PARAM_INT);
    $check->bindValue(':name', $name, PDO::PARAM_STR);
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
      $update = $db->prepare('UPDATE aya_locations
                              SET Name = :name, Street = :street, StreetNumber = :number, Zip = :zip, City = :city,
                                Coordinates = POINT(:latitude, :longitude), HostUrl = :url, Description = :description
                              WHERE LocationID = :id');
      $update->bindValue(':name', $name, PDO::PARAM_STR);
      $update->bindValue(':street', $street, PDO::PARAM_STR);
      $update->bindValue(':number', (empty($_POST['StreetNumber']) ? null : $_POST['StreetNumber']), PDO::PARAM_STR);
      $update->bindValue(':zip', $_POST['Zip'], PDO::PARAM_INT);
      $update->bindValue(':city', $city, PDO::PARAM_STR);
      $update->bindValue(':latitude', $coordinates[1], PDO::PARAM_STR);
      $update->bindValue(':longitude', $coordinates[2], PDO::PARAM_STR);
      $update->bindValue(':url', (empty($_POST['HostUrl']) ? null : $_POST['HostUrl']), PDO::PARAM_STR);
      $update->bindValue(':description', (empty($_POST['Description']) ? null : $_POST['Description']), PDO::PARAM_STR);
      $update->bindValue(':id', $locationID, PDO::PARAM_INT);
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
    echo 'ALREADY_EXISTS';
  }
}
else
{
  echo 'MISSING_DATA';
}

$db = null;
?>
