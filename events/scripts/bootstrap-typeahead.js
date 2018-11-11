/*
 * Copyright 2016-2018 Martin Arndt, TroubleZone.Net Productions
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
 *
 * File:    bootstrap-typeahead.js - jQuery functions for the type-aheads of AYA event detail pages
 * Version: 1.0
 * Date:    2016-02-27
 */

$(document).ready(function() {
  $('#vehicle-color').typeahead({
    source: function(query) {
      $.getJSON('ajax/get-colors.php', function(data) {
        return data;
      })
    }
  });
});
