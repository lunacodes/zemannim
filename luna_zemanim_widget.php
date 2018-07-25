<?php
/**
 * Plugin Name: Luna Zemanim Widget
 */

/**
 * Issues:
   * js: I probably want to replace a lot of the var statements w/ let statements
   * js, php: change js api calls to php cURL calls
   * php: refactor into functions
   * php: I might be able to make the Time calculations easier by setting the default time zone to $tzname from the outset
   * php: Should I combine the Shema & Minha calculations into one function?
   * php: write tests
   * php: docblocks for functions
   * php: I can probably remove all of the enqueues now!!
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
    // wp_enqueue_script( 'jquery' ); 
    // wp_enqueue_script( 'google-maps', 'http://maps.googleapis.com/maps/api/js?key=AIzaSyDFrCM7Ao83pwu_avw-53o7cV0Ym7eLqpc' );
    // wp_enqueue_script( 'suncalc-master', plugins_url( '/suncalc-master/suncalc.js?ver=4.9.4', __FILE__ ) );

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

    /**
     * Setup for Date, Time, Timezone, etc
     */
    
    /* What day is it today*/
    $yom = strtotime("now");
    $yom_txt = date("M d, Y", $yom);
    $yom_ymd = date("Y-m-d", $yom);

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
    
    /* Get time offset for timzezone and DST */
    $tzurl = "http://api.geonames.org/timezoneJSON?lat=".$lat."&lng=".$long."&date=".$yom_ymd."&username=adatosystems";
    // luna debug check url
    // echo("tzurl: $tzurl");
    $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $tzurl);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tz = curl_exec($ch);
      curl_close($ch);
    $tzjd = json_decode(utf8_encode($tz),true);
    // echo "tzjd: $tzjd";
    // var_dump($tzjd);
    $tzname = $tzjd['timezoneId'];
    
    // Get the Time Zone Offset
    date_default_timezone_set('UTC');
    $utc = new DateTime();
    $current = timezone_open($tzname);
    $offset = timezone_offset_get($current, $utc); // seconds
    $offset_h = $offset / 3600;

    $yomsunrise = $tzjd['dates'][0]['sunrise'];
    $yomsunset = $tzjd['dates'][0]['sunset'];
    // Convert string to UTC Seconds
    $yomsunrisedatetime = strtotime($yomsunrise);
    // Convert UTC Secs to Date str
    $yomsunriseformat = date("h:i:A", $yomsunrisedatetime);
    $yomsunsetdatetime = strtotime($yomsunset);
    $yomsunsetformat = date("h:i:A", $yomsunsetdatetime);
    // Note: these are currently in UTC
    $sunrisesec = $yomsunrisedatetime+$offset;
    $sunsetsec = $yomsunsetdatetime+$offset;
    $halakhicHour = ($sunsetsec - $sunrisesec) / 12;

    echo("<br>Time Zone: $tzname <br>Offset: $offset <br> Offset Hrs: $offset_h <br>Sunrise: $yomsunrise  <br>Sunset: $yomsunset  <br>Sunrise DT: $yomsunrisedatetime  <br>Sunset DT: $yomsunsetdatetime <br>Sunrise Format: $yomsunriseformat <br>Sunset Format: $yomsunsetformat <br>Sunrise Sec: $sunrisesec <br> Sunset Sec: $sunsetsec <br>Halakhic Hour: $halakhicHour <br><br>");

    date_default_timezone_set($tzname);
    function calculateLatestShema($sunriseSec, $offSetSec, $halakhicHour) {
      echo("CLS Sunrise Sec Passed: $sunriseSec");
      $h3 = $halakhicHour * 3;
      echo("CLS: $sunriseSec, Offset: $offSetSec, $halakhicHour, $h3<br><br>");
      $shemaInSeconds = $sunriseSec + ($halakhicHour * 3) + $offSetSec;
      // echo("Shema Sec: $shemaInSeconds<br>");
      // echo("Shema Sec 2: $shema2<br>");
      $latestShema = date("h:i:A", $shemaInSeconds);
      // $latestShema2 = 
      return $latestShema;
    }

    function calculateEarliestMinha($sunriseSec, $offSetSec, $halakhicHour) {
      // echo("CEM Sunrise Sec Passed: $sunriseSec");
      // $h3 = $halakhicHour * 3;
      // echo("PHP values: $sunriseSec, $sunsetSec, $halakhicHour, $h3");
      $minhaInSeconds = $sunriseSec + ($halakhicHour * 6.5) + $offSetSec;
      // $minha1 = $sunriseSec + ($halakhicHour * 3) + $offSetSec;
      // echo("Shema Sec: $minhaInSeconds<br>");
      // echo("Shema Sec 2: $shema2<br>");
      $earliestMinha = date("h:i:A:e:O:Z", $minhaInSeconds);
      return $earliestMinha;
    }

    function calculatePelegHaMinha($sunsetSec2, $offSetSec2, $halakhicHour2) {
      // echo("<br>CPM Sunset Sec Passed: $sunsetSec2 <br>");
      $h1 = $halakhicHour2 * 1.25;
      $absOffset = abs($offSetSec2);
      echo("CPM Sunset: $sunsetSec2, Offset: $offSetSec2, Abs Offset: $absOffset, Halakhic Hr: $halakhicHour2, H1: $h1 <br>");
      $pelegHaMinhaInSeconds2 = $sunsetSec2 - ($halakhicHour2 * 1.25) + ($absOffset * 1.25);
      // $shema2 = $sunriseSec + ($halakhicHour2 * 3) + $offSetSec2;
      echo("Peleg HaMinha Sec: $pelegHaMinhaInSeconds2<br>");
      // echo("Shema Sec 2: $shema2<br>");
      $pelegHaMinha = date("h:i:A:e:O:Z", $pelegHaMinhaInSeconds2);
      // $latestShema2 = 
      return $pelegHaMinha;
    }

    $latestShema = calculateLatestShema($sunrisesec, $offset, $halakhicHour);
    $earliestMinha2 = calculateEarliestMinha($sunrisesec, $offset, $halakhicHour);
    $pelegHaMinha = calculatePelegHaMinha($sunsetsec, $offset, $halakhicHour);

    echo("Latest Shema: $latestShema<br>"); 
    echo("Earliest Minha: $earliestMinha2<br>"); 
    echo("Peleg HaMinha: $pelegHaMinha<br>"); 

  function displayZemanim() { 
 ?>
    <div id="zemanim_container">
      <div id="zemanim_display">
          <span id="zemanim_date"><?php echo($yom_ymd); ?></span>
          <span id="zemanim_city"></span>
          <span id="zemanim_hebrew">
              <?php 
              $hebcal_magic_date = '
              <script type="text/javascript" charset="utf-8" src="//www.hebcal.com/etc/hdate-he.js"></script>';
              echo($hebcal_magic_date); ?>
              <br>
          </span>
          <span id="zemanim_shema"><?php echo("Latest Shema: $latestShema<br>"); ?></span>
          <span id="zemanim_minha"><?php echo("Earliest Minha: $earliestMinha2<br>"); ?></span>
          <span id="zemanim_peleg"><?php echo("Peleg HaMinha: $pelegHaMinha<br>"); ?></span>
          <span id="zemanim_sunset"></span>
      </div>
  </div>

<?php 
  }
  displayZemanim();
?>

<!-- <script type="text/javascript" defer>
    var z_date = document.getElementById("zemanim_date");
    var z_city = document.getElementById("zemanim_city");
    var z_shema = document.getElementById("zemanim_shema");
    var z_minha = document.getElementById("zemanim_minha");
    var z_peleg = document.getElementById("zemanim_peleg");
    var z_sunset = document.getElementById("zemanim_sunset");    var x = document.getElementById("zemanim_container");

    // let latLong = null;
    var zemanim = document.getElementById("zemanim_display");

    <?php // echo("var pos = " . $latLng . ';') ?>
    console.log(pos);

  jQuery(document).ready( () => {
      // var latLng = <?php // echo($latLng); ?>;
      // getGeoDetails(latLng);
      getLocation();
    })

</script> -->
<?php

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