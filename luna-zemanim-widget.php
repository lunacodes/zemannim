<?php
/**
* Plugin Name: Daily Zemanim
 * Plugin URI: https://lunacodesdesign.com/
 * Description: Displays Zemannim (times) according to Sepharadic tradition.
 *   Uses the DB-IP API and the Google Maps API for geographic information.
 *   Uses the Sun-Calc Library (https://github.com/mourner/suncalc) for sunrise/sunset information.
 * Version: 1.3
 * Author: Luna Lunapiena
 * Author URI: https://lunacodesdesign.com/
 * License: GPL3+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: luna_zemanim_widget_domain
 * Change Record: see README.md
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
 * Issues: see README.md
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
      // Debugging - don't run if not SSL (js will break)
      $ssl_check = is_ssl();
      if ( ! is_ssl() ) {
        return;
      }

      wp_enqueue_script( 'suncalc-master', plugins_url( '/suncalc-master/suncalc.js?ver=4.9.4', __FILE__ ) );
      // wp_enqueue_script( 'google_js', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&key=AIzaSyAmCeKW07UlPDH_eQarF4y9vWJ6cwHePp4', '', '' );
      wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&key=AIzaSyAmCeKW07UlPDH_eQarF4y9vWJ6cwHePp4' );

      // extract( $args );
      $title = apply_filters( 'widget_title', $instance['title'] );

      echo $args['before_widget'];
      if ( ! empty( $title ) ) {
        echo $args['before_title'] . $title . $args['after_title'];
      }
      // echo __( 'Zemanim Widget', 'luna_zemanim_widget_domain' );

    /**
     * Pre-generates html structure for front-end display, prior to
     * any other code being run
     * @since 1.0.0
     */
    function outputZemanim() { ?>
        <div id="zemanim_container">
            <div id="zemanim_display">
                <span id="js_ip"></span>
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

    // Testing code - extract to tests files.
    var js_ip = document.getElementById("js_ip");
    var z_date = document.getElementById("zemanim_date");
    var z_city = document.getElementById("zemanim_city");
    var z_shema = document.getElementById("zemanim_shema");
    var z_minha = document.getElementById("zemanim_minha");
    var z_peleg = document.getElementById("zemanim_peleg");
    var z_sunset = document.getElementById("zemanim_sunset");
    var zemanim = document.getElementById("zemanim_container");

    /**
     * getLocation Requests location permission from user for HTML5 Geolocation API
     * @since  1.0.0
     */
    function getLocation() {
        var options = {
          enableHighAccuracy: true,
          // timeout: 5000,
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
     * extracts lat & long from coordinates object and passes them to getGeoDetails
     * @param  {object} position Coordinates object from getLocation()
     * @since  1.0.0
     */
    function getLatLngByGeo(position) {
      var pos = position;
      var lat = pos.coords.latitude;
      var long = pos.coords.longitude;

      getGeoDetails(lat, long);
    }

    /**
     * Parses JSON object from DB-IP API, and passes
     * formatted `urlStr` to getLatLongByAddr()
     * @since  1.0.0
     */
    function getAddrDetailsByIp() {
      let urlStr = 'https://api.db-ip.com/v2/free/self';
      fetch(urlStr)
        .then(function(response) {
          return response.json();
        })
        .then(function(res) {
          let ip = res["ipAddress"];
          js_ip.innerHTML = ip + "<br>";
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

    /**
     * Extracts lat & long from passed urlStr, and
     * sends to Google Maps Geocoding API via getGeoDetails
     * @param  {string} urlStr [formatted string to plug into Google Maps API]
     * @since  1.0.0
     */
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
     * Obtains city and state from pased lat long, and passes to generateTimes()
     * @param  {int} lat_crd Lattitude coordinate passed from getLatLongByGeo()
     * @param  {int} long_crd Longitude cooredinate passed from getLatLongByGeo()
     * @since  1.0.0
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

    /**
     * Checks if we are currently in Daylight Savings Time, via Date object
     * @since 1.0.0
     */
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

    /**
     * Removes leading 0 from 2-digit month or date numbers and returns to generateTimeStrings
     * @param  {int} x Numerical time passed from generateTimeStrings()
     * @return {int}   1-digit version of int `x` that was passed in
     */
    function formatTime(x) {
      var reformattedTime = x.toString();
      reformattedTime = ("0" + x).slice(-2);
      return reformattedTime;
    }

    /**
     * Splits time object into year, month, day, hour, min, sec and returns buildTimeStr
     * @param  {int} timeObj Value passed in from generateTimes() after formatting from formatTime()
     * @return {string}   Time String in Y-M-D-H-M
     */
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

    /**
     * Helper function for generateTimeStrings
     * @param  {object} timeObj Time oject used to build a date string
     * @see generateTimeStrings()
     * @return {string}   The Date in a string
     */
    function generateDateString(timeObj) {
      var monthInt = timeObj.getMonth();
      var monthList = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
      var month = monthList[monthInt];
      var day = formatTime(timeObj.getDate());
      var year = timeObj.getFullYear();
      var buildDateStr = '<span id="zemanin_date">' + "Times for " + month + " " + day + ", " + year + '</span>';
      return buildDateStr;
    }
    /**
     * Obtains Sunrise and Sunset via SunCalc API, calculates Halakhic Times, and passes strings to displayTimes()
     * @param  {int}        lat  User's Lattitude
     * @param  {int}        long User's Longitude
     * @param  {string}     city User's city
     * @since  1.0.0
     */
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
      var pelegHaMinhaStr = '<span id="zmantitle">Peleḡ haMinḥa: </span>' + calculatePelegHaMinha(sunriseSec, sunsetSec, offSetSec);
      var displaySunsetStr = '<span id="zmantitle">Sunset: </span>' + unixTimestampToDate(SunsetDateTimeInt+offSetSec);

      displayTimes(dateStr, cityStr, latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, displaySunsetStr);
      // zemanim.innerHTML = "This Worked";


      // Display Sunset
    }

    /**
     * [unixTimestampToDate description]
     * @param  {int} timestamp UTC Timestamp
     * @return {string}           Time in H:M:S
     * @since  1.0.0
     */
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

    /**
     * Calculates the latest halakhic time to say the Shema Yisrael prayer
     * @param  {int} sunriseSec The time of sunrise, in seconds
     * @param  {int} sunsetSec  Time of sunset, in seconds
     * @param  {int} offSetSec  Offset for Time Zone and DST
     * @return {string}            Formatted string in H:M:S
     * @see unixTimestampToDate
     * @since  1.0.0
     */
    function calculateLatestShema(sunriseSec, sunsetSec, offSetSec) {
      var halakhicHour = Math.abs((sunsetSec - sunriseSec) / 12);
      var shemaInSeconds = sunriseSec + (halakhicHour * 3) + offSetSec;
      var latestShema = unixTimestampToDate(shemaInSeconds);

      return latestShema;
    }

    /**
     * Calculates the earliest halakhic time to pray Minḥa
     * @param  {int} sunriseSec The time of sunrise, in seconds
     * @param  {int} sunsetSec  Time of sunset, in seconds
     * @param  {int} offSetSec  Offset for Time Zone and DST
     * @return {string}            Formatted string in H:M:S
     * @see unixTimestampToDate
     */
    function calculateEarliestMinha(sunriseSec, sunsetSec, offSetSec) {
      var halakhicHour = (sunsetSec - sunriseSec) / 12;
      var minhaInSeconds = sunriseSec + (halakhicHour * 6.5) + offSetSec;
      var earliestMinha = unixTimestampToDate(minhaInSeconds);

      return earliestMinha;
    }

    /**
     * Calculates the latest halakhic time to pray Minḥa
     * @param  {int} sunriseSec The time of sunrise, in seconds
     * @param  {int} sunsetSec  Time of sunset, in seconds
     * @param  {int} offSetSec  Offset for Time Zone and DST
     * @return {string}            Formatted string in H:M:S
     * @see unixTimestampToDate
     */
    function calculatePelegHaMinha(sunriseSec, sunsetSec, offSetSec) {
      var halakhicHour = (sunsetSec - sunriseSec) / 12;
      var minhaInSeconds = sunsetSec - (halakhicHour * 1.25) + offSetSec;
      var pelegHaMinha = unixTimestampToDate(minhaInSeconds);

      return pelegHaMinha;
    }

    /**
     * Receives time and location info from generateTimes() and
     * writes innerHtml for front-end display, via jQuery
     * @param  {string} date   Today's Date
     * @param  {string} city   User's City
     * @param  {string} shema  Lastest time to pray Shema
     * @param  {string} minha  Earliest time to pray Minḥa
     * @param  {string} peleg  Latest time to pray Minḥa
     * @param  {string} sunset Time of Sunset
     * @since  1.0.0
     */
    function displayTimes(date, city, shema, minha, peleg, sunset) {

      z_date.innerHTML = date + "<br>";
      z_city.innerHTML = city + "<br>";
      // z_hebrew.innerHTML = hebrew + "<br>";
      z_shema.innerHTML = shema + "<br>";
      z_minha.innerHTML = minha + "<br>";
      z_peleg.innerHTML = peleg + "<br>";
      z_sunset.innerHTML = sunset + "<br>";
      // zemanim.innerHTML = date + "<br>" + city + "<br"> + hebcalDate +  "<br>" + shema + "<br>" + minha + "<br>" + peleg + "<br>" + sunset;
    }

    // Make sure we're ready to run our script!
    jQuery(document).ready(function($) {
      getLocation();
    });

    // if (
    //     document.readyState === "complete" ||
    //     (document.readyState !== "loading" && !document.documentElement.doScroll)
    // ) {
    //   callback();
    // } else {
    //   document.addEventListener("DOMContentLoaded", callback);
    // }


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
