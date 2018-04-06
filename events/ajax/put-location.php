<?php
/**********
 * File:    put-location.php - update location for AYA admin
 * Version: 1.9
 * Date:    2018-02-25
 * Author:  Martin Baranski, TroubleZone.Net Productions
 * Licence: Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)
 *          (see: https://creativecommons.org/licenses/by-sa/4.0/ for details)
 **********/

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
    print 'Error: ' . $exception->getMessage() . '<br />';
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
      print 'Error: ' . $exception->getMessage() . '<br />';
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
