<?php
/**
 * Plugin Name: Luna Zemanim Widget
 */

/**
 * NOTE: I need to factor out the overlapping code in getGeoDetails and showPosition
 * I probably want to replace a lot of the var statements w/ let statements
 * Pretty sure I can delete getSunriseObj
 * NOTE: PHP cURL is *much faster*
 */
class Luna_Zemanim_Widget extends WP_Widget {

  /**
   * Register widget with WordPress
   */
  public function __construct() {
  parent::__construct(
    'luna_zemanim_widget', // Base ID
    'Luna Zemanim Widget', // Name
    array( 'description' => __( "Luna's Zemanim Widget", 'text_domain' ),  ) //Args
    ); 

  add_action( 'widgets_init', function() {register_widget( 'Luna_Zemanim_Widget' ); } );
  }

  /**
   * Front-end display of widget.
   * 
   * @see WP_Widget::widget()
   * 
   * @param array $args     Widget Arguments.
   * @param array $instance Saved values from database
   */
  public function widget( $args, $instance ) {
    wp_enqueue_script( 'jquery' ); 
    wp_enqueue_script( 'google-maps', 'http://maps.googleapis.com/maps/api/js?key=AIzaSyDFrCM7Ao83pwu_avw-53o7cV0Ym7eLqpc' );
    wp_enqueue_script( 'suncalc-master', plugins_url( '/suncalc-master/suncalc.js?ver=4.9.4', __FILE__ ) );

    extract( $args );
    $title = apply_filters( 'widget_title', $instance['title'] );

    echo $before_widget;
    if ( ! empty( $title ) ) {
      echo $before_title . $title . $after_title;
    }
    echo __( 'Zemanim Widget', 'text_domain' );
    ?>

    <?php
    function outputZemanim() { ?>
        <div id="zemanim_container">
            <div id="zemanim_display">
                <span id="zemanim_date"></span>
                <span id="zemanim_city"></span>
                <span id="zemanim_hebrew">
                    <?php 
                    $hebcal_magic_date = '
                    <script type="text/javascript" charset="utf-8" src="//www.hebcal.com/etc/hdate-he.js"></script>';
                    echo($hebcal_magic_date); ?>
                    <br>
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

  function getClientIP() {
    $client_ip = '';
    $client_ip = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
    // echo('<br>');
    // echo("ip: $client_ip <br>");
    // $ip = '134.201.250.155';
    return $client_ip;
  }

  // function getLatLngByIP() {
    $ip = getClientIP();

    $access_key = '62e3a66a273f35e0bde207e433850072';

    // Initialize CURL:
    $ch = curl_init('http://api.ipstack.com/'.$ip.'?access_key='.$access_key.'');
    // echo("ch: $ch");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Store the data:
    $json = curl_exec($ch);
    // echo("ch json: $json");
    curl_close($ch);

    // Decode JSON response:
    $result = json_decode($json, true);
    // var_dump($result);
    $lat =$result['latitude'];
    $long = $result['longitude'];
    $ip = $result['ip'];
    $continent_name = $result['continent_name'];
    $region_name = $result['region_name'];
    $city = $result['city'];
    $state = $result['region_code'];
    echo("$lat," . " $long <br>" );
    echo ("$ip <br>$city, $state<br>");

    $latLng = json_encode([$lat, $long]);
    echo("latLng is: $latLng");
    // return $latLng;
  // }

  // getLatLngByIP();
?>

<script type="text/javascript" defer>
    var z_date = document.getElementById("zemanim_date");
    var z_city = document.getElementById("zemanim_city");
    var z_shema = document.getElementById("zemanim_shema");
    var z_minha = document.getElementById("zemanim_minha");
    var z_peleg = document.getElementById("zemanim_peleg");
    var z_sunset = document.getElementById("zemanim_sunset");    var x = document.getElementById("zemanim_container");

    let latLong = null;
    var zemanim = document.getElementById("zemanim_display");

    <?php echo("var pos = " . $latLng . ';') ?>
    console.log(pos);

    function getLocation()
      {
        var options = {
          enableHighAccuracy: true,
          // timeout: 5000,
          maximumAge: 0
        };

        function error(err) {
          console.warn(`ERROR(${err.code}): ${err.message}`);
        zemanim.innerHtml = "Please enable location services to display the most up-to-date Zemanim";
          console.log("going by ip instead!");
          getLatLngByIP(pos);

        }

        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(getLatLngByGeo, error, options);
        }

      }

    function getLatLngByGeo(position) {
      console.log("navigator.geolocation is geolocating");
      var pos = position;
      var lat = pos.coords.latitude;
      var long = pos.coords.longitude;

      getGeoDetails(lat, long);
    }

    function getLatLngByIP(position) {
      var pos = position;
      console.log("pos test: ", pos[0], pos[1]);
      // console.log(JSON.stringify(pos));
      // console.log(pos.toString());
      var lat = parseFloat(pos[0]);
      var long = parseFloat(pos[1]);
      getGeoDetails(lat, long);
    }

    function getGeoDetails(lat_crd, long_crd) {
      let lat = lat_crd;
      let long = long_crd;

      var point = new google.maps.LatLng(lat, long);        new google.maps.Geocoder().geocode({'latLng': point}, function (res, status) {

        var response = res;

        if (res[1]) {
          for (var i = 0; i < res.length; i++) {
            if (res[i].types[0] === "locality") {
              var city = res[i].address_components[0].short_name;
              // var city = "";
            } // end if loop 2

            if (res[i].types[0] === "neighborhood") {
              var neighborhood = res[i].address_components[0].long_name;
              // var neighborhood = "";
            } // end if loop 2

            if (res[i].types[0] === "administrative_area_level_1") {
              var state = res[i].address_components[0].long_name;
              // var state = "";
            } // end if loop 2
          } // end for loop
        } // end if loop 1
        console.log(res);

        var cityStr =  city + ", " + state + "<br>" + neighborhood;
        console.log(cityStr);

        generateTimes(lat, long, cityStr);
        // return latLong = [window.lat, window.long];
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
      console.log("Daylight saving time!");
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
    console.log(year, month, day, hour, min, sec);
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
    console.log(lat, long);
    console.log(city);
    var cityStr = city;
    var times = SunCalc.getTimes(new Date(), lat, long);
    var sunriseObj = times.sunrise;
    var offSet = sunriseObj.getTimezoneOffset() / 60;
    var offSetSec = offSet * 3600;
    console.log("Offset: ", offSet);
    console.log("offSetSec: ", offSetSec);
    var dateObj = new Date();
    var dateStr = generateDateString(dateObj);
    var sunriseStr = generateTimeStrings(sunriseObj);
    var sunsetObj = times.sunset;
    var sunsetStr = generateTimeStrings(sunsetObj);
    // console.log(times);
    // console.log(dateStr);
    // console.log("Sunrise: ", sunriseStr);
    // console.log("Sunset: ", sunsetStr);

    // console.log("/// Begin DateTime Debug: ///");
    var SunriseDateTimeInt = parseFloat((new Date(sunriseStr).getTime() / 1000) - offSetSec);
    var SunsetDateTimeInt = parseFloat((new Date(sunsetStr).getTime() / 1000) - offSetSec);
    var sunriseSec = SunriseDateTimeInt - offSet;
    var sunsetSec = SunsetDateTimeInt - offSet;

    var latestShemaStr = '<span id="zmantitle">Latest Shema: </span>' + calculateLatestShema(sunriseSec, sunsetSec, offSetSec);
    var earliestMinhaStr = '<span id="zmantitle">Earliest Minḥa: </span>' + calculateEarliestMinha(sunriseSec, sunsetSec, offSetSec);
    var pelegHaMinhaStr = '<span id="zmantitle">Peleḡ HaMinḥa: </span>' + calculatePelegHaMinha(sunriseSec, sunsetSec, offSetSec);
    var displaySunsetStr = '<span id="zmantitle">Sunset: </span>' + unixTimestampToDate(SunsetDateTimeInt+offSetSec);
    // console.log("Sunset: ", displaySunset);

    displayTimes(dateStr, cityStr, latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, displaySunsetStr);
    // zemanim.innerHTML = "This Worked";


    // Display Sunset
  }

  // console.log("/// End TimeSec Debug ///");
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
    // console.log("Date: ", date, "Hours: ", hours, "Minute: ", minutes, ampm);
    var formattedTime = hours + ':' + minutes.substr(-2);
    // console.log("formattedTime: ", formattedTime);
    return formattedTime + " " + ampm;
  }

  // Calculate Shema
  function calculateLatestShema(sunriseSec, sunsetSec, offSetSec) {
    var halakhicHour = Math.abs((sunsetSec - sunriseSec) / 12);
    var shemaInSeconds = sunriseSec + (halakhicHour * 3) + offSetSec;
    var latestShema = unixTimestampToDate(shemaInSeconds);

    // console.log("Latest Shema: ", latestShema);
    return latestShema;
  }

  function calculateEarliestMinha(sunriseSec, sunsetSec, offSetSec) {
    var halakhicHour = (sunsetSec - sunriseSec) / 12;
    var minhaInSeconds = sunriseSec + (halakhicHour * 6.5) + offSetSec;
    var earliestMinha = unixTimestampToDate(minhaInSeconds);
    console.log("Halakhic Hour: ", halakhicHour);

    console.log("Earliest Minḥa: ", earliestMinha);
    return earliestMinha;
  }

  function calculatePelegHaMinha(sunriseSec, sunsetSec, offSetSec) {
    var halakhicHour = (sunsetSec - sunriseSec) / 12;
    var minhaInSeconds = sunsetSec - (halakhicHour * 1.25) + offSetSec;
    var pelegHaMinha = unixTimestampToDate(minhaInSeconds);

    // console.log("Peleḡ HaMinḥa: ", pelegHaMinha);
    return pelegHaMinha;
  }

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

  jQuery(document).ready( () => {
      // var latLng = <?php echo($latLng); ?>;
      // getGeoDetails(latLng);
      getLocation();
    })

</script>
<?php

    /**
     * Setup for Date, Time, Timezone, etc
     */
    
    /* What day is it today*/
    $yom = strtotime("now");
    $yom_txt = date("M d, Y", $yom);
    $yom_ymd = date("Y-m-d", $yom);


} 

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
    $title = __( 'New title', 'text_domain' );
  }

  // Widget admin form
  ?>
  <p>
  <label for="<?php echo $this->get_field_id( 'title' );?>"><?php _e( 'Title:' ); ?></label>
  <input class="widefat" id="<?php echo $this-> get_field_name( 'title' );?>" type="text" value="<?php echo esc_attr( $title ); ?>"  /> 
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