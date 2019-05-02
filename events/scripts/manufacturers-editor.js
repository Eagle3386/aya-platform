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

var displayName = 'Hersteller';

function getPayload() {
  return {
    ManufacturerID: $('#manufacturer-form').data('manufacturer-id'),
    Name: $('#manufacturer-name').val(),
    Keywords: $('#manufacturer-keywords').val()
  };
}

function loadedCallback() {
  attachEventHandler('.form-control');
}
