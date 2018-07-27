<?php
/**
* Plugin Name: Daily Zemanim
 * Plugin URI: https://lunacodesdesign.com/
 * Description: Displays Zemannim (times) according to Sepharadic tradition. 
 *   Uses the DB-IP API and the Google Maps API for geographic information. 
 *   Uses the Sun-Calc Library (https://github.com/mourner/suncalc) for sunrise/sunset information.
 * Version: 1.1
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
 * getGeoDetails: var state needs for loop, instead of just being set to null
 * improve code logic with promises?
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

function outputZemanim() { ?>
    <div id="zemanim_container">
        <div id="zemanim_display">
            <span id="zemanim_date"></span>
            <span id="zemanim_city"></span>
            <span id="zemanim_hebrew">
              <script type="text/javascript" charset="utf-8" src="//www.hebcal.com/etc/hdate-he.js"></script><br>
            </span>
            <span id="zemanim_shema"></span>
            <span id="zemanim_minha"></span>
            <span id="zemanim_peleg"></span>
            <span id="zemanim_sunset"></span>
        </div>
    </div>

<?php
}
outputZemanim();
?>

<script type="text/javascript" defer>
  var z_date = document.getElementById("zemanim_date");
  var z_city = document.getElementById("zemanim_city");
  var z_shema = document.getElementById("zemanim_shema");
  var z_minha = document.getElementById("zemanim_minha");
  var z_peleg = document.getElementById("zemanim_peleg");
  var z_sunset = document.getElementById("zemanim_sunset");    
  var zemanim = document.getElementById("zemanim_container");

  function getLocation()
    {
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
            var state = res[i].address_components[0].long_name;
          } // end if loop 2
        } // end for loop
      } // end if loop 1

      if (state == null) {
        var cityStr = city;
      } else {
        var cityStr =  city + ", " + state;
      }

      generateTimes(lat, long, cityStr);
    });
}

function checkForDST() {
  Date.prototype.stdTimezoneOffset = function () {
    var jan = new Date(this.getFullYear(), 0, 1);
    var jul = new Date(this.getFullYear(), 6, 1);
    return Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
  }

  Date.prototype.isDstObserved = function () {
    return this.getTimezoneOffset() < this.stdTimezoneOffset();
  }

  var today = new Date();
  if (today.isDstObserved()) { 
    return true;
  }
}

function formatTime(x) {
  var reformattedTime = x.toString();
  reformattedTime = ("0" + x).slice(-2);
  return reformattedTime;
}

function generateTimeStrings(timeObj) {
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

function generateTimes(lat, long, city) {
  var cityStr = city;
  var times = SunCalc.getTimes(new Date(), lat, long);
  var sunriseObj = times.sunrise;
  var offSet = sunriseObj.getTimezoneOffset() / 60;
  var offSetSec = offSet * 3600;
  var dateObj = new Date();
  var dateStr = generateDateString(dateObj);
  var sunriseStr = generateTimeStrings(sunriseObj);
  var sunsetObj = times.sunset;
  var sunsetStr = generateTimeStrings(sunsetObj);

  var SunriseDateTimeInt = parseFloat((new Date(sunriseStr).getTime() / 1000) - offSetSec);
  var SunsetDateTimeInt = parseFloat((new Date(sunsetStr).getTime() / 1000) - offSetSec);
  var sunriseSec = SunriseDateTimeInt - offSet;
  var sunsetSec = SunsetDateTimeInt - offSet;

  var latestShemaStr = '<span id="zmantitle">Latest Shema: </span>' + calculateLatestShema(sunriseSec, sunsetSec, offSetSec);
  var earliestMinhaStr = '<span id="zmantitle">Earliest Minḥa: </span>' + calculateEarliestMinha(sunriseSec, sunsetSec, offSetSec);
  var pelegHaMinhaStr = '<span id="zmantitle">Peleḡ HaMinḥa: </span>' + calculatePelegHaMinha(sunriseSec, sunsetSec, offSetSec);
  var displaySunsetStr = '<span id="zmantitle">Sunset: </span>' + unixTimestampToDate(SunsetDateTimeInt+offSetSec);

  displayTimes(dateStr, cityStr, latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, displaySunsetStr);
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

function displayTimes(date, city, shema, minha, peleg, sunset) {

  z_date.innerHTML = date + "<br>";
  z_city.innerHTML = city + "<br>";
  z_shema.innerHTML = shema + "<br>";
  z_minha.innerHTML = minha + "<br>";
  z_peleg.innerHTML = peleg + "<br>";
  z_sunset.innerHTML = sunset + "<br>";
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