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

function ShowException($exception)
{
  return print 'Error: ' . $exception->getMessage() . '<br />';
}

try
{
  $db = new PDO('mysql:host=IP.AD.DR.ESS;dbname=DATABASE;charset=utf8',
                'USERNAME',
                'PASSWORD',
                array(PDO::ATTR_EMULATE_PREPARES   => false,
                      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                      PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'"));
}
catch (PDOException $exception)
{
  die(ShowException($exception));
}
?>
