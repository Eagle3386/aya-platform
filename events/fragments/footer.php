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
?>
<div id="fragment-modal"></div>
<footer class="footer">
  <p class="text-muted text-center">
    Made with <span title="blood">💧&#xFE0E;</span>, <span title="sweat">💦&#xFE0E;</span> & <span title="love">💗&#xFE0E;</span> 2016-<?=date('y');?> for AYA e. V. by <a href="https://www.troublezone.net/">Martin Arndt</a>.
  </p>
</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js"
        integrity="sha256-nuL8/2cJ5NDSSwnKD8VqreErSWHtnEP9E7AySL+1ev4=" crossorigin="anonymous" async></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.10/js/bootstrap-select.min.js"
        integrity="sha256-FXzZGmaRFZngOjUKy3lWZJq/MflaMpffBbu3lPT0izE=" crossorigin="anonymous" async></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"
        integrity="sha256-LOnFraxKlOhESwdU/dX+K0GArwymUDups0czPWLEg4E=" crossorigin="anonymous" async></script>

<script src="scripts/bootstrap-dialogs.js"></script>
<?php
if ($isEventPage)
  echo '<script src="scripts/event.js"></script>';

if (($phpBBUserID > 1) && $showDistance && !$showMap)
{
  echo '<script src="https://maps.googleapis.com/maps/api/js?callback=initializeDistances" async defer></script>
<script src="scripts/location-distance.js"></script>';
}

if ($showMap)
{
  echo '<script src="https://maps.googleapis.com/maps/api/js?callback=initializeLocationMap" async defer></script>
<script src="scripts/location-map.js"></script>';
}

if ($isAdmin)
  echo '<script src="scripts/admin.js"></script>';

if ($isJuror)
  echo '<script src="scripts/juror.js"></script>';
?>

<script>
<?php
if ($showDistance)
{
  echo 'let shortDistanceResult = ' . ($showDistanceShortened ? 'true' : 'false') . ';';
}
?>
</script>
