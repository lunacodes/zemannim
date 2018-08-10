<?php
/**
* Plugin Name: Shabbat Zemmanim
 * Plugin URI: https://lunacodesdesign.com/
 * Description: Displays Zemannim (times) according to Sepharadic tradition.
 *   Uses the DB-IP API and the Google Maps API for geographic information.
 *   Uses the Sun-Calc Library (https://github.com/mourner/suncalc) for sunrise/sunset information.
 * Version: 1.1
 * Author: Luna Lunapiena
 * Author URI: https://lunacodesdesign.com/
 * License: GPL3+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: shabbat_zemanim_widget_domain
 * Change Record:
 * ***********************************
 * 2018- - initial creation
 *
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation,version 3
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   For details about the GNU General Public License, see <http://www.gnu.org/licenses/>.
 *   For details about this program, see the readme file.
*/

/**
 * Issues:
 * ***********************************
 * shabbatGetGeoDetails: var state needs For Loop, instead of just being set to null
 * improve code logic with promises?
 * Why Did I need to change outputZemanim to outputShabbatZemannim
 * shabbatDateStr isn't outputting
 * Needs to be refactored using Classes to avoid conflicts w/ other Zemanim plugin
   * Is there a way to check if we already have some of the info from the other plugin???
 * shabbatCheckForDST() isn't being used - delete?
 * Replace unxiTimestampToDate w/ PHP
 * Change fridayDateInfo to key => value array?
 * Refactor all js time functions, since Date/Time info can be generated faster w/ php 
 * Is it possible to replace Hebcal Shabbat API with local code?
   * What's the license on their code?
 * Test
*/

class Shabbat_Zemannim_Widget extends WP_Widget {

  /**
   * Register widget with WordPress
   */
  public function __construct() {
    parent::__construct(
      'shabbat_zemanim_widget', // Base ID
      __('Shabbat Zemannim', 'shabbat_zemanim_widget_domain'), // Name
      array( 'description' => __( "Displays Zemannim (times) according to Sepharadic tradition", 'shabbat_zemanim_widget_domain' ),  ) //Args
    );

  add_action( 'widgets_init', function() {register_widget( 'Shabbat_Zemannim_Widget' ); } );
  }

  /**
   * Front-end display of widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget Arguments.
   * @param array $instance Saved values from database   */
  public function widget( $args, $instance ) {
    wp_enqueue_script( 'suncalc-master', plugins_url( '/suncalc-master/suncalc.js?ver=4.9.4', __FILE__ ) );

    $title = apply_filters( 'widget_title', $instance['title'] );

    echo $args['before_widget'];
    if ( ! empty( $title ) ) {
      echo $args['before_title'] . $title . $args['after_title'];
    }

  function getShabbatDate() {
    /* Get Shabbat Date */

    // If Friday or Sat, don't get next week's date
    if (date('N') == 5) {
      $friday = strtotime("now");
    } elseif (date('N') == 6) {
      $friday = strtotime("yesterday");
    } else {
      $friday = strtotime("next friday");
    }

    // Get separate m-d-y to feed into hebcal
    // $friday_ms = $friday * 1000;
    $friday_str = date(DATE_ISO8601, $friday);
    // echo("Friday Str: $friday_str <br>");
    // echo("Friday int: $friday_int <br>Friday ms: $friday_ms <br>");
    $friday_m = idate("m", $friday);
    $friday_d = idate("j", $friday);
    $friday_y = idate("Y", $friday);
    $jdate = gregoriantojd($friday_m, $friday_d, $friday_y);
    $jd2 = jdtojewish($jdate, true, CAL_JEWISH_ADD_GERESHAYIM);
    
    $hebDateStr = mb_convert_encoding("$jd2", "utf-8", "ISO-8859-8");

    // Debugging Friday Details
    // echo("$friday_int <br>$friday_m <br>$friday_d <br>$friday_y <br>$jdate <br>$jd2 <br>$hebDateStr");

    // Get the name of the month
    $dateObj   = DateTime::createFromFormat('!m', $friday_m);
    $monthName = $dateObj->format('F'); // March
    $fridayDateStr = "$monthName $friday_d, $friday_y";

    $fridayDateInfo = [$fridayDateStr, $hebDateStr, $friday_str];
    // Debugging
    // echo("Date Info: ");
    // var_dump($fridayDateInfo);
    // echo("$yesterday <br> $date <br> $friday <br> $friday_mdy <br> $friday_d <br> $friday_m <br> $friday_y <br> $hebDateUrl <br> $fridayDateStr");

    return $fridayDateInfo;
  }

  $ShabbatDateInfo = getShabbatDate();
  // echo("Shabbat Date Info: $ShabbatDateInfo[0], $ShabbatDateInfo[1]");

  function outputShabbatZemannim($dateInfo) {
    // echo("Date Info: $dateInfo");
    // Get Friday's Hebrew Date via hebcal
    $shabbatDateStr = $dateInfo[0];
    $hebDateStr = $dateInfo[1];
    // echo("Shabbat Date: $shabbatDateStr <br> Heb Date: $hebDateStr");
    ?>

      <div id="shabbat_zemanim_container">
          <div id="shabbat_zemanim_display">
              <span id="shabbat_zemanim_date">Shabbat Times for <?php echo($shabbatDateStr) ?><br></span>
              <span id="shabbat_zemanim_city"></span>
              <span id="shabbat_zemanim_hebrew"><?php echo($hebDateStr); ?><br>
              </span>
              <span id="shabbat_zemanim_shema">Latest Shema: <br></span>
              <span id="shabbat_zemanim_minha">Earliest Minḥa:  <br></span>
              <span id="shabbat_zemanim_peleg">Peleḡ HaMinḥa:  <br></span>
              <span id="shabbat_zemanim_sunset">Sunset: <br></span>
              <span id="shabbat_zemanim_habdala">Haḇdala: <br></span>
          </div>
      </div>

  <?php
  }
outputShabbatZemannim($ShabbatDateInfo);
?>

<script type="text/javascript" defer>
  // var z_date = document.getElementById("shabbat_zemanim_date");
  var zemanim = document.getElementById("shabbat_zemanim_container");
  var shabbat_z_city = document.getElementById("shabbat_zemanim_city");
  var shabbat_z_shema = document.getElementById("shabbat_zemanim_shema");
  var shabbat_z_minha = document.getElementById("shabbat_zemanim_minha");
  var shabbat_z_peleg = document.getElementById("shabbat_zemanim_peleg");
  var shabbat_z_sunset = document.getElementById("shabbat_zemanim_sunset");
  var shabbat_z_habdala = document.getElementById("shabbat_zemanim_habdala");

  /**
   * shabbatGetLocation - gets user's lat & long via HTML5 Geolocation API sends to shabbatGetGeoDetails
   * @return {(number|Array)} [lat, long] coordinates
   */
  function shabbatGetLocation() {
    var options = {
      enableHighAccuracy: true,
      maximumAge: 0
    };

    function error(err) {
      console.warn(`ERROR(${err.code}): ${err.message}`);
    zemanim.innerHtml = "Please enable location services to display the most up-to-date Zemanim";
          shabbatGetAddrDetailsByIp();
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(shabbatGetLatLngByGeo, error, options);
      }
  }

  /**
   * shabbatGetLatLngByGeo - separate [lat, long] int lat & long vars, pass to Google Maps API via shabbatGetGeoDetails
   * @param  {number|Array} position [lat, long]
   * @return {[type]}          [description]
   */
  function shabbatGetLatLngByGeo(position) {
    var pos = position;
    var lat = pos.coords.latitude;
    var long = pos.coords.longitude;

    shabbatGetGeoDetails(lat, long);
  }

  function shabbatGetAddrDetailsByIp() {
    let urlStr = 'https://api.db-ip.com/v2/free/self';
    fetch(urlStr)
      .then(function(response) {
        return response.json();
      })
      .then(function(res) {
        let ip = res["ipAddress"];
        let apiKey = 'AIzaSyDFrCM7Ao83pwu_avw-53o7cV0Ym7eLqpc';
        let city = res["city"];
        let state = res["stateProv"];
        let country = res["countryCode"];
        let address = city + "+" + state + "+" + "&components=" + country;
        let urlBase = 'https://maps.googleapis.com/maps/api/geocode/json?';
        let url = urlBase + "&address=" + address + "&components=" + country + "&key=" + apiKey;
        // use regEx to replace all spaces with plus signs
        let urlStr = url.replace(/\s+/g, "+");
        shabbetGetLatLongByAddr(urlStr);
      });
  }

  function shabbetGetLatLongByAddr(urlStr) {
    let url = urlStr;
    fetch(url)
      .then((response) => {
        return response.json();
      })
      .then((res) => {
        let data = new Array(res.results[0]);
        let lat = data[0].geometry.location.lat;
        let long = data[0].geometry.location.lng;
        shabbatGetGeoDetails(lat, long);
      });
  }

  /**
   * [shabbatGetGeoDetails feed lat & long coords into Google Maps API to obtain City, State info and pass to generateTimes ]
   * @param  {[float]} lat_crd  [user's lattitude]
   * @param  {[float]} long_crd [user's longitude]
   * @return {[string]} cityStr [user's City, State]
   */
  function shabbatGetGeoDetails(lat_crd, long_crd) {
    let lat = lat_crd;
    let long = long_crd;
    var point = new google.maps.LatLng(lat, long);        new google.maps.Geocoder().geocode({'latLng': point}, function (res, status) {

      if (res[0]) {
        for (var i = 0; i < res.length; i++) {
          if (res[i].types[0] === "locality") {
            var city = res[i].address_components[0].short_name;
          } // end if loop 2

          if (res[i].types[0] === "administrative_area_level_1") {
            var state = res[i].address_components[0].short_name;
          } // end if loop 2
        } // end for loop
      } // end if loop 1

      if (state == null) {
        var cityStr = city;
      } else {
        var cityStr =  city + ", " + state;
      }

      shabbatGenerateTimes(lat, long, cityStr);
    });
  }

  function shabbatFormatTime(x) {
    var reformattedTime = x.toString();
    reformattedTime = ("0" + x).slice(-2);
    return reformattedTime;
  }

  function shabbatGenerateTimeStrings(timeObj) {
    var year = timeObj.getFullYear();
    var month = shabbatFormatTime(timeObj.getMonth() + 1);
    var day = shabbatFormatTime(timeObj.getDate());
    var hour = shabbatFormatTime(timeObj.getHours());
    var min = shabbatFormatTime(timeObj.getMinutes());
    var sec = shabbatFormatTime(timeObj.getSeconds());
    var buildTimeStr = year + "-" + month + "-" + day + " " + hour + ":" + min;
    return buildTimeStr;
  }

  // function generateDateString(timeObj) {
  //   var monthInt = timeObj.getMonth();
  //   var monthList = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  //   var month = monthList[monthInt];
  //   var day = shabbatFormatTime(timeObj.getDate());
  //   var year = timeObj.getFullYear();
  //   var buildDateStr = '<span id="zemanin_date">' + "Times for " + month + " " + day + ", " + year + '</span>';
  //   return buildDateStr;
  // }

  function shabbatGenerateTimes(lat, long, city) {
    var dateInfo = <?php echo("'$ShabbatDateInfo[2]'"); ?>;
    // console.log("Date Info: " + dateInfo); 
    // console.log("Lat: " + lat);
    // console.log("Long: " + long);

    var cityStr = city;
    var times = SunCalc.getTimes(new Date(dateInfo), lat, long);
    var sunriseObj = times.sunrise;
    var offSet = sunriseObj.getTimezoneOffset() / 60;
    var offSetSec = offSet * 3600;
    // var dateObj = new Date();
    // var dateStr = generateDateString(dateObj);
    var sunriseStr = shabbatGenerateTimeStrings(sunriseObj);
    var sunsetObj = times.sunset;
    var sunsetStr = shabbatGenerateTimeStrings(sunsetObj);
    var habdalaOffset = 1200;
    // console.log("Generated Times:");
    // console.log(cityStr);
    // console.log(times);
    // console.log(sunriseObj);
    // console.log(offSet);
    // console.log(offSetSec);
    // console.log(dateObj);
    // console.log(dateStr);
    // console.log(sunriseStr);
    // console.log(sunsetObj);
    // console.log(sunsetStr);

    var SunriseDateTimeInt = parseFloat((new Date(sunriseStr).getTime() / 1000) - offSetSec);
    var SunsetDateTimeInt = parseFloat((new Date(sunsetStr).getTime() / 1000) - offSetSec);
    var sunriseSec = SunriseDateTimeInt - offSet;
    var sunsetSec = SunsetDateTimeInt - offSet;
    var habdalaSec = SunsetDateTimeInt + habdalaOffset;

    var latestShemaStr = '<span id="shabbat_zemanim_shema">Latest Shema: </span>' + shabbatCalculateLatestShema(sunriseSec, sunsetSec, offSetSec);
    var earliestMinhaStr = '<span id="shabbat_zemanim_minha">Earliest Minḥa: </span>' + shabbatCalculateEarliestMinha(sunriseSec, sunsetSec, offSetSec);
    var pelegHaMinhaStr = '<span id="shabbat_zemanim_peleg">Peleḡ HaMinḥa: </span>' + shabbatCalculatePelegHaMinha(sunriseSec, sunsetSec, offSetSec);
    var displaySunsetStr = '<span id="shabbat_zemanim_sunset">Sunset: </span>' + shabbatUnixTimestampToDate(SunsetDateTimeInt+offSetSec);
    var habdalaStr = '<span id="shabbat_zemanim_habdala">Haḇdala (20 min): </span>' + shabbatUnixTimestampToDate(habdalaSec + offSetSec);

    shabbatDisplayTimes(cityStr, latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, displaySunsetStr, habdalaStr);
  }

  function shabbatUnixTimestampToDate(timestamp) {
    var date = new Date(timestamp * 1000);
    var hours = date.getHours();
    var ampm = "AM";
    var minutes = "0" + date.getMinutes();

    if (hours > 12) {
      hours -= 12;
      ampm = "PM";
    }
    else if (hours === 0) {
      hours = 12;
    }
    var formattedTime = hours + ':' + minutes.substr(-2);
    return formattedTime + " " + ampm;
  }

  function shabbatCalculateLatestShema(sunriseSec, sunsetSec, offSetSec) {
    var halakhicHour = Math.abs((sunsetSec - sunriseSec) / 12);
    var shemaInSeconds = sunriseSec + (halakhicHour * 3) + offSetSec;
    var latestShema = shabbatUnixTimestampToDate(shemaInSeconds);

    return latestShema;
  }

  function shabbatCalculateEarliestMinha(sunriseSec, sunsetSec, offSetSec) {
    var halakhicHour = (sunsetSec - sunriseSec) / 12;
    var minhaInSeconds = sunriseSec + (halakhicHour * 6.5) + offSetSec;
    var earliestMinha = shabbatUnixTimestampToDate(minhaInSeconds);

    return earliestMinha;
  }

  function shabbatCalculatePelegHaMinha(sunriseSec, sunsetSec, offSetSec) {
    var halakhicHour = (sunsetSec - sunriseSec) / 12;
    var minhaInSeconds = sunsetSec - (halakhicHour * 1.25) + offSetSec;
    var pelegHaMinha = shabbatUnixTimestampToDate(minhaInSeconds);

    return pelegHaMinha;
  }

  function shabbatCalculateHabdala(sunset) {
    // body...
  }
  function shabbatDisplayTimes(city, shema, minha, peleg, sunset, habdala) {

    // z_date.innerHTML = date + "<br>";
    shabbat_z_city.innerHTML = city + "<br>";
    shabbat_z_shema.innerHTML = shema + "<br>";
    shabbat_z_minha.innerHTML = minha + "<br>";
    shabbat_z_peleg.innerHTML = peleg + "<br>";
    shabbat_z_sunset.innerHTML = sunset + "<br>";
    shabbat_z_habdala.innerHTML = habdala + "<br>";
  }

  // Make sure we're ready to run our script!
  jQuery(document).ready(function($) {
    shabbatGetLocation();
  });

</script>

<?php

  echo $args['after_widget'];

} // public function widget ends here

/**
 * Back-end widget form.
 *
 * @see WP_Widget::form()
 *
 * @param array $instance Previously saved values from database.
 */
  public function form( $instance ) {
    if ( isset( $instance[ 'title' ] ) ) {
      $title = $instance[ 'title' ];
    }
    else {
      $title = __( 'New title', 'shabbat_zemanim_widget_domain' );
    }

  // Widget admin form
?>
  <p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
  </p>
<?php
}

/**
 * Sanitize widget form values as they are saved.
 *
 * @see WP_Widget::update()
 *
 * @param array $new instance Values just sent to be saved from database.
 *
 * @return array Updated safe values to be saved.
 */
public function update( $new_instance, $old_instance ) {
$instance = array();
$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

return $instance;
}

} // class Shabbat_Zemannim_Widget

$lunacodes_shabbat_widget = new Shabbat_Zemannim_Widget();