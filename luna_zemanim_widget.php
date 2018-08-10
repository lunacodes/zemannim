<?php
/**
* Plugin Name: Daily Zemanim
 * Plugin URI: https://lunacodesdesign.com/
 * Description: Displays Zemannim (times) according to Sepharadic tradition.
 *   Uses the DB-IP API and the Google Maps API for geographic information.
 *   Uses the Sun-Calc Library (https://github.com/mourner/suncalc) for sunrise/sunset information.
 * Version: 1.2
 * Author: Luna Lunapiena
 * Author URI: https://lunacodesdesign.com/
 * License: GPL3+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: luna_zemanim_widget_domain
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
 * getGeoDetails: var state needs For Loop, instead of just being set to null
 * improve code logic with promises?
 * MAJOR: I NEED TO ADD DATE AND TIME CALCULATIONS FOR SATURDAY!!!
*/

class Luna_Zemanim_Widget extends WP_Widget {

  /**
   * Register widget with WordPress
   */
  public function __construct() {
    parent::__construct(
      'luna_zemanim_widget', // Base ID
      __('Daily Zemannim', 'luna_zemanim_widget_domain'), // Name
      array( 'description' => __( "Displays Zemannim (times) according to Sepharadic tradition", 'luna_zemanim_widget_domain' ),  ) //Args
    );

  add_action( 'widgets_init', function() {register_widget( 'Luna_Zemanim_Widget' ); } );
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

  function generateHebrewDate($date) {
    $month = idate("m", $date);
    $day = idate("j", $date);
    $year = idate("Y", $date);
    $jdate = gregoriantojd($month, $day, $year);
    $jd2 = jdtojewish($jdate, true, CAL_JEWISH_ADD_GERESHAYIM);

    $hebDateStr = mb_convert_encoding("$jd2", "utf-8", "ISO-8859-8");
    return $hebDateStr;
  }

  function generateDates() {
    $today = date("F, j, Y");
    $todayInt = strtotime("now");
    // echo("Today Int: $todayInt <br>");
    $dayOfWeek = date("N");
    if ($dayOfWeek == 5) {
          $friday = strtotime("now");
    } elseif ($dayOfWeek == 6) {
      $friday = strtotime("yesterday");
    } else {
      $friday = strtotime("next friday");
    }

    $todayStr = $today;
    $shabbatISO = date(DATE_ISO8601, $friday);    $todayHebStr = generateHebrewDate($todayInt);
    // echo("Today: $todayStr <br>");
    // echo("Today Heb: $todayHebStr <br>");
    $shabbatStr = date("F, j, Y", $friday);
    $shabbatHebStr = generateHebrewDate($friday);
    // echo("Shabbat: $shabbatStr <br>");
    // echo("Shabbat Heb: $shabbatHebStr <br>");
    $dates = [$todayStr, $todayHebStr, $shabbatStr, $shabbatHebStr, $shabbatISO];
    // echo("$todayStr<br>$todayHebStr<br>$shabbatStr<br>$shabbatHebStr<br>");
    return $dates;
  }
  $dates = generateDates();

  function outputZemanim($dates) {
    $today = $dates[0];
    $todayHeb = $dates[1];
    $shabbat = $dates[2];
    $shabbatHeb = $dates[3];
    ?>

      <div id="zemanim_container">
          <div id="zemanim_display">
              <span id="zemanim_date">Times for <?php echo($today) ?><br></span>
              <span id="zemanim_city"></span>
              <span id="zemanim_hebrew"><?php echo($todayHeb) ?><br></span>
              <span id="zemanim_shema">Latest Shema: <br></span>
              <span id="zemanim_minha">Earliest Minḥa:  <br></span>
              <span id="zemanim_peleg">Peleḡ HaMinḥa:  <br></span>
              <span id="zemanim_sunset">Sunset: <br></span>
          </div>
      </div>
      <br><br>
      <h6 class="widget_title">Shabbat Zemannim</h6>
      <div id="shabbat_zemanim_container">
          <div id="shabbat_zemanim_display">
              <span id="shabbat_zemanim_date">Shabbat Times for <?php echo($shabbat) ?><br></span>
              <span id="shabbat_zemanim_city"></span>
              <span id="shabbat_zemanim_hebrew"><?php echo($shabbatHeb); ?><br>
              </span>
              <!-- <span id="shabbat_zemanim_shema">Latest Shema: <br></span> -->
              <!-- <span id="shabbat_zemanim_minha">Earliest Minḥa:  <br></span> -->
              <!-- <span id="shabbat_zemanim_peleg">Peleḡ HaMinḥa:  <br></span> -->
              <span id="shabbat_zemanim_candles">Sunset: <br></span>
              <span id="shabbat_zemanim_sunset">Sunset: <br></span>
              <span id="shabbat_zemanim_habdala">Haḇdala: </span>
          </div>
      </div>

  <?php
  }
outputZemanim($dates);
?>

<script type="text/javascript" defer>
  // var z_date = document.getElementById("zemanim_date");
  var zemanim = document.getElementById("zemanim_container");
  var z_city = document.getElementById("zemanim_city");
  var z_shema = document.getElementById("zemanim_shema");
  var z_minha = document.getElementById("zemanim_minha");
  var z_peleg = document.getElementById("zemanim_peleg");
  var z_sunset = document.getElementById("zemanim_sunset");
  var shabbat_zemanim = document.getElementById("shabbat_zemanim_container");
  var sz_city = document.getElementById("shabbat_zemanim_city");
  var sz_candles =document.getElementById("shabbat_zemanim_candles"); 
  // var sz_shema = document.getElementById("shabbat_zemanim_shema");
  // var sz_minha = document.getElementById("shabbat_zemanim_minha");
  // var sz_peleg = document.getElementById("shabbat_zemanim_peleg");
  var sz_sunset = document.getElementById("shabbat_zemanim_sunset");
  var sz_habdala = document.getElementById("shabbat_zemanim_habdala");
  
  /**
   * getLocation - gets user's lat & long via HTML5 Geolocation API sends to getGeoDetails
   * @return {(number|Array)} [lat, long] coordinates
   */
  function getLocation() {
    var options = {
      enableHighAccuracy: true,
      maximumAge: 0
    };

    function error(err) {
      console.warn(`ERROR(${err.code}): ${err.message}`);
    zemanim.innerHtml = "Please enable location services to display the most up-to-date Zemanim";
          getAddrDetailsByIp();
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(getLatLngByGeo, error, options);
      }
  }

  /**
   * getLatLngByGeo - separate [lat, long] int lat & long vars, pass to Google Maps API via getGeoDetails
   * @param  {number|Array} position [lat, long]
   * @return {[type]}          [description]
   */
  function getLatLngByGeo(position) {
    var pos = position;
    var lat = pos.coords.latitude;
    var long = pos.coords.longitude;

    getGeoDetails(lat, long);
  }

  function getAddrDetailsByIp() {
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
        getLatLongByAddr(urlStr);
      });
  }

  function getLatLongByAddr(urlStr) {
    let url = urlStr;
    fetch(url)
      .then((response) => {
        return response.json();
      })
      .then((res) => {
        let data = new Array(res.results[0]);
        let lat = data[0].geometry.location.lat;
        let long = data[0].geometry.location.lng;
        getGeoDetails(lat, long);
      });
  }

  /**
   * [getGeoDetails feed lat & long coords into Google Maps API to obtain City, State info and pass to generateTimes ]
   * @param  {[float]} lat_crd  [user's lattitude]
   * @param  {[float]} long_crd [user's longitude]
   * @return {[string]} cityStr [user's City, State]
   */
  function getGeoDetails(lat_crd, long_crd) {
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

      timesHelper(lat, long, cityStr);
    });
  }

  function formatTime(x) {
    var reformattedTime = x.toString();
    reformattedTime = ("0" + x).slice(-2);
    return reformattedTime;
  }

  function generateSunStrings(timeObj) {
    var year = timeObj.getFullYear();
    var month = formatTime(timeObj.getMonth() + 1);
    var day = formatTime(timeObj.getDate());
    var hour = formatTime(timeObj.getHours());
    var min = formatTime(timeObj.getMinutes());
    var sec = formatTime(timeObj.getSeconds());
    var buildTimeStr = year + "-" + month + "-" + day + " " + hour + ":" + min;
    return buildTimeStr;
  }

  function generateDateString(timeObj) {
    var monthInt = timeObj.getMonth();
    var monthList = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var month = monthList[monthInt];
    var day = formatTime(timeObj.getDate());
    var year = timeObj.getFullYear();
    var buildDateStr = '<span id="zemanin_date">' + "Times for " + month + " " + day + ", " + year + '</span>';
    return buildDateStr;
  }

  function timesHelper(lat, long, city) {
    var cityStr = city;
    // can I replace the new Date() w/ the an ISO str?
    var todayTimesObj = SunCalc.getTimes(new Date(), lat, long);
    // console.log(todayTimesObj);
    var shabbatFeed = '<?php echo("$dates[4]"); ?>';
    var shabbatHelperStr = shabbatFeed.substr(0, 19);
    // console.log(shabbatHelperStr);
    // console.log(typeof shabbatHelperStr);
    var shabbatHelper = new Date(shabbatHelperStr);
    // console.log(shabbatHelper);
    var shabbatTimesObj = SunCalc.getTimes(new Date(shabbatHelperStr), lat, long);
    // console.log(shabbatTimesObj);
    var todayTimes = calculateTimes(todayTimesObj, false);
    var shabbatTimes = calculateTimes(shabbatTimesObj, true);
    // console.log(todayTimes);
    // console.log(shabbatTimes);
    var todayStrSet = generateTimeStrings(todayTimes, false);
    // console.log(todayStrSet);
    var shabbatStrSet = generateTimeStrings(shabbatTimes, true);
    // console.log(shabbatStrSet);

    displayTimes(todayStrSet, cityStr);
    displayShabbatTimes(shabbatStrSet, cityStr);
  }

  function generateTimeStrings(timeSet, shabbat) {
    // console.log(timeSet);
    var sunrise = timeSet[0];
    var sunset = timeSet[1];
    var offSet = timeSet[2];
    var sunsetDateTimeInt = timeSet[3];

    var latestShemaStr = '<span id="zemanim_shema">Latest Shema: </span>' + calculateLatestShema(sunrise, sunset, offSet);
    var earliestMinhaStr = '<span id="zemanim_minha">Earliest Minḥa: </span>' + calculateEarliestMinha(sunrise, sunset, offSet);
    var pelegHaMinhaStr = '<span id="zemanim_peleg">Peleḡ HaMinḥa: </span>' + calculatePelegHaMinha(sunrise, sunset, offSet);
    var sunsetStr = '<span id="zemanim_sunset">Sunset: </span>' + unixTimestampToDate(sunsetDateTimeInt + offSet);

    if (shabbat) {
      var candleLighting = timeSet[4];
      var habdala = timeSet[5];
      // var shabbatsunsetStr 
      var candleLightingStr = '<span id="zemanim_habdala">Candle Lighting (18 min): </span>' + unixTimestampToDate(candleLighting + offSet);
      var habdalaStr = '<span id="zemanim_habdala">Haḇdala (20 min): </span>' + unixTimestampToDate(habdala + offSet);
      var shabbatSet = [sunsetStr, candleLightingStr, habdalaStr]; 

      return shabbatSet;
    } else {
      var todaySet = [latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, sunsetStr];

      return todaySet;
    }

  }

  function calculateTimes(timeObj, shabbat) {
    // var shabbatTest = shabbat;
    // console.log(shabbatTest);
    // var cityStr = city;
    // var times = SunCalc.getTimes(new Date(), lat, long);
    var times = timeObj;
    // console.log(times);
    var sunriseObj = times.sunrise;
    // console.log(sunriseObj);
    var offSet = sunriseObj.getTimezoneOffset() / 60;
    var offSetSec = offSet * 3600;
    // var dateObj = new Date();
    // var dateStr = generateDateString(dateObj);
    var sunriseStr = generateSunStrings(sunriseObj);
    var sunsetObj = times.sunset;
    var sunsetStr = generateSunStrings(sunsetObj);

    var SunriseDateTimeInt = parseFloat((new Date(sunriseStr).getTime() / 1000) - offSetSec);
    var sunsetDateTimeInt = parseFloat((new Date(sunsetStr).getTime() / 1000) - offSetSec);
    var sunriseSec = SunriseDateTimeInt - offSet;
    var sunsetSec = sunsetDateTimeInt - offSet;

    if (shabbat) {
      // console.log("shabbat mode");
      var candleLightingOffset = 1080;
      var habdalaOffSet = 1200;
      var candleLightingSec = sunsetDateTimeInt - candleLightingOffset;
      var habdalaSec = sunsetDateTimeInt + habdalaOffSet;

      var timeSet = [sunriseSec, sunsetSec, offSetSec, sunsetDateTimeInt, candleLightingSec, habdalaSec]; 
    } else {
      var timeSet = [sunriseSec, sunsetSec, offSetSec, sunsetDateTimeInt]; 
    }

    return timeSet;
  }

  function unixTimestampToDate(timestamp) {
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

  function calculateLatestShema(sunriseSec, sunsetSec, offSetSec) {
    var halakhicHour = Math.abs((sunsetSec - sunriseSec) / 12);
    var shemaInSeconds = sunriseSec + (halakhicHour * 3) + offSetSec;
    var latestShema = unixTimestampToDate(shemaInSeconds);

    return latestShema;
  }

  function calculateEarliestMinha(sunriseSec, sunsetSec, offSetSec) {
    var halakhicHour = (sunsetSec - sunriseSec) / 12;
    var minhaInSeconds = sunriseSec + (halakhicHour * 6.5) + offSetSec;
    var earliestMinha = unixTimestampToDate(minhaInSeconds);

    return earliestMinha;
  }

  function calculatePelegHaMinha(sunriseSec, sunsetSec, offSetSec) {
    var halakhicHour = (sunsetSec - sunriseSec) / 12;
    var minhaInSeconds = sunsetSec - (halakhicHour * 1.25) + offSetSec;
    var pelegHaMinha = unixTimestampToDate(minhaInSeconds);

    return pelegHaMinha;
  }

  function displayTimes(timeSet, city) {
    var city = city;
    var shema = timeSet[0];
    var minha = timeSet[1];
    var peleg = timeSet[2];
    var sunset = timeSet[3];

    // z_date.innerHTML = date + "<br>";
    z_city.innerHTML = city + "<br>";
    z_shema.innerHTML = shema + "<br>";
    z_minha.innerHTML = minha + "<br>";
    z_peleg.innerHTML = peleg + "<br>";
    z_sunset.innerHTML = sunset + "<br>";
  }

  function displayShabbatTimes(timeSet, city) {
    var city = city;
    var sunset = timeSet[0];
    var candleLighting = timeSet[1];
    var habdala = timeSet[2];

    sz_city.innerHTML = city + "<br>";
    sz_sunset.innerHTML = sunset + "<br>";
    sz_candles.innerHTML = candleLighting + "<br>";
    sz_habdala.innerHTML = habdala;

  }


  // Make sure we're ready to run our script!
  jQuery(document).ready(function($) {
    getLocation();
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
      $title = __( 'New title', 'luna_zemanim_widget_domain' );
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

} // class Luna_Zemanim_Widget

$lunacodes_widget = new Luna_Zemanim_Widget();