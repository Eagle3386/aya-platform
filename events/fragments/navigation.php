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

echo '<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <button class="navbar-toggle collapsed" data-target="#aya-navbar-collapse" data-toggle="collapse" type="button" aria-expanded="false">
        <span class="sr-only">Menü umschalten</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand navbar-btn" href="//www.aya-forum.de/">
        <img alt="Are You Authentic? e. V." src="/styles/aya_prosilver/theme/images/logo_forum.png" />
      </a>
    </div>
    <div id="aya-navbar-collapse" class="collapse navbar-collapse">
      <ul class="nav navbar-nav">
        <li>
          <a class="btn btn-aya-default navbar-btn' . (strpos($_SERVER['SCRIPT_NAME'], 'listing') === false ? '' : ' active') . '" href="./listing.php"
             role="button">
            <span class="glyphicon glyphicon-th"></span> Wettbewerbe
          </a>
        </li>';

if ($isJuror)
{
  echo '<li>
  <a class="btn btn-aya-default navbar-btn' . (strpos($_SERVER['SCRIPT_NAME'], 'juror') === false ? '' : ' active') . '" href="./juror.php" role="button">
    <span class="glyphicon glyphicon-list-alt"></span> Jurorenbereich
  </a>
</li>';
}

if ($isAdmin)
{
  echo '<li>
  <a class="btn btn-aya-default navbar-btn' . (strpos($_SERVER['SCRIPT_NAME'], 'admin') === false ? '' : ' active') . '" href="./admin.php" role="button">
    <span class="glyphicon glyphicon-wrench"></span> Administration
  </a>
</li>';
}

if ($phpBBUserID > 1)
{
  echo '<li class="dropdown">
<a id="aya-user" class="btn btn-aya-default dropdown-toggle" data-user-id="' . $phpBBUserID . '" data-location="' . $ayaUserLocation . '" data-toggle="dropdown"
   href="#" role="button">
  <span class="glyphicon glyphicon-user"></span> ' . $ayaUsername . ' <span class="caret"></span>
</a>
<ul class="dropdown-menu">
  <li>
    <a id="attendances-editor" href="#">
      <span class="glyphicon glyphicon-calendar"></span> Teilnahmen
    </a>
  </li>
  <li>
    <a id="vehicles-editor" href="#">
      <span class="glyphicon glyphicon-bed"></span> Fahrzeuge
    </a>
  </li>
</ul>';
}
else
{
  echo '</ul>
<ul class="nav navbar-nav navbar-right">
  <li>
    <a id="aya-user" class="btn btn-aya-default navbar-btn" data-user-id="' . $phpBBUserID . '" data-location="' . $ayaUserLocation . '"
       href="/ucp.php?mode=login" role="button">
      <span class="glyphicon glyphicon-off"></span> Anmelden
    </a>';
}

echo '</li>
      </ul>
    </div>
  </div>
</nav>';
?>
