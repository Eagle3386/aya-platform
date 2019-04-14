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

require_once('db-connection.php');

$phpBBUserID = 1;
try
{
  $session = $db->prepare('SELECT session_user_id, session_ip
                           FROM phpbb_sessions
                           WHERE session_id = :id
                             AND session_ip = :ip');
  $session->bindValue(':id', $_COOKIE['PHPBB_SID_COOKIE_NAME'], PDO::PARAM_STR);
  $session->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
  $session->execute();
  $session->bindColumn('session_user_id', $phpBBUserID, PDO::PARAM_INT);
  $session->bindColumn('session_ip', $sessionIP, PDO::PARAM_STR);
  $session->fetch(PDO::FETCH_BOUND);
  $session = null;
}
catch (PDOException $exception)
{
  ShowException($exception);
}

$isAdmin = false;
$isJuror = false;
if ($phpBBUserID > 1)
{
  try
  {
    $user = $db->prepare('SELECT U.username, P.pf_anschrift, MAX(CASE WHEN (G.group_id = 12 OR G.group_id = 22) THEN true ELSE false END) AS IsAdmin,
                            MAX(CASE WHEN (G.group_id = 10 OR G.group_id = 22) THEN true ELSE false END) AS IsJuror
                          FROM phpbb_users U
                          JOIN phpbb_profile_fields_data P ON P.user_id = U.user_id
                          JOIN phpbb_user_group G ON G.user_id = U.user_id
                          WHERE U.user_id = :id');
    $user->bindValue(':id', $phpBBUserID, PDO::PARAM_INT);
    $user->execute();
    $user->bindColumn('username', $ayaUsername, PDO::PARAM_STR);
    $user->bindColumn('pf_anschrift', $ayaUserLocation, PDO::PARAM_STR);
    $user->bindColumn('IsAdmin', $isAdmin, PDO::PARAM_BOOL);
    $user->bindColumn('IsJuror', $isJuror, PDO::PARAM_BOOL);
    $user->fetch(PDO::FETCH_BOUND);
    $user = null;
  }
  catch (PDOException $exception)
  {
    ShowException($exception);
  }
}
?>
