<?php
/**
 * Plugin Name: Luna Zemanim Widget
 */

/**
 * Issues:
   * widget: before & after html formatting not appearing
   * php: occasional uncaught Type Error - make an If Wrapper for this??
   * php: refactor into functions
   * php: I might be able to make the Time calculations easier by setting the default time zone to $tzname from the outset
   * php: Should I combine the Shema & Minha calculations into one function?
   * php: write tests
   * php: docblocks for functions
   * php: I can probably remove all of the enqueues now!!
   * php: fix calculations for +GMT Offset locations
   * api: fix freegoip.net error message in console
 */
class Luna_Zemanim_Widget extends WP_Widget {

  /**
   * Register widget with WordPress
   */
  public function __construct() {
    parent::__construct(
      'luna_zemanim_widget', // Base ID
      __('Luna Zemanim Widget', 'luna_zemanim_widget_domain'), // Name
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
    // extract( $args );
    $title = apply_filters( 'widget_title', $instance['title'] );

    echo $args['before_widget'];
    if ( ! empty( $title ) ) {
      echo $args['before_title'] . $title . $args['after_title'];
    }
    // echo __( 'Zemanim Widget', 'luna_zemanim_widget_domain' );

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
    $locationStr = "$city, $state";
    // echo("$lat," . " $long <br>" );
    // echo ("$ip <br>$city, $state<br>");

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
    $yomsunsetformat = date("h:i", $yomsunsetdatetime);
    $SunsetStr = "$yomsunsetformat PM";
    // Note: these are currently in UTC
    $sunrisesec = $yomsunrisedatetime+$offset;
    $sunsetsec = $yomsunsetdatetime+$offset;
    $halakhicHour = ($sunsetsec - $sunrisesec) / 12;

    // echo("<br>Time Zone: $tzname <br>Offset: $offset <br> Offset Hrs: $offset_h <br>Sunrise: $yomsunrise  <br>Sunset: $yomsunset  <br>Sunrise DT: $yomsunrisedatetime  <br>Sunset DT: $yomsunsetdatetime <br>Sunrise Format: $yomsunriseformat <br>Sunset Format: $yomsunsetformat <br>Sunrise Sec: $sunrisesec <br> Sunset Sec: $sunsetsec <br>Halakhic Hour: $halakhicHour <br><br>");

    date_default_timezone_set($tzname);
    function calculateLatestShema($sunriseSec, $offSetSec, $halakhicHour) {
      // echo("CLS Sunrise Sec Passed: $sunriseSec");
      // $h3 = $halakhicHour * 3;
      // echo("CLS: $sunriseSec, Offset: $offSetSec, $halakhicHour, $h3<br><br>");
      $shemaInSeconds = $sunriseSec + ($halakhicHour * 3) + $offSetSec;
      // echo("Shema Sec: $shemaInSeconds<br>");
      // echo("Shema Sec 2: $shema2<br>");
      $latestShema = date("h:i", $shemaInSeconds);
      $latestShemaStr = "$latestShema AM";
      // $latestShema2 = 
      return $latestShemaStr;
    }

    function calculateEarliestMinha($sunriseSec, $offSetSec, $halakhicHour) {
      // echo("CEM Sunrise Sec Passed: $sunriseSec");
      // $h3 = $halakhicHour * 3;
      // echo("PHP values: $sunriseSec, $sunsetSec, $halakhicHour, $h3");
      $minhaInSeconds = $sunriseSec + ($halakhicHour * 6.5) + $offSetSec;
      // $minha1 = $sunriseSec + ($halakhicHour * 3) + $offSetSec;
      // echo("Shema Sec: $minhaInSeconds<br>");
      // echo("Shema Sec 2: $shema2<br>");
      $earliestMinha = date("h:i", $minhaInSeconds);
      $earliestMinhaStr = "$earliestMinha PM";
      return $earliestMinhaStr;
    }

    function calculatePelegHaMinha($sunsetSec, $offSetSec, $halakhicHour) {
      // echo("<br>CPM Sunset Sec Passed: $sunsetSec <br>");
      $h1 = $halakhicHour * 1.25;
      $absOffset = abs($offSetSec);
      // echo("CPM Sunset: $sunsetSec, Offset: $offSetSec, Abs Offset: $absOffset, Halakhic Hr: $halakhicHour, H1: $h1 <br>");
      
      // $step1 = $sunsetSec - $h1;
      // echo("Step 1: $step1");
      // $step2 = $absOffset;
      // echo("Step 2: $step2");
      // $step3 = $step1 + $step2;
      // echo("Step 3: $step3");

      $pelegHaMinhaInSeconds = $sunsetSec + ($halakhicHour * 1.25) + ($absOffset * 1.25);
      // $shema2 = $sunriseSec + ($halakhicHour2 * 3) + $offSetSec;
      // echo("Peleg HaMinha Sec: $pelegHaMinhaInSeconds<br>");
      // echo("Shema Sec 2: $shema2<br>");
      $pelegHaMinha = date("h:i", $pelegHaMinhaInSeconds);
      $pelegHaMinhaStr = "$pelegHaMinha PM";
      // $latestShema2 = 
      return $pelegHaMinhaStr;
    }

    $latestShema = calculateLatestShema($sunrisesec, $offset, $halakhicHour);
    $earliestMinha = calculateEarliestMinha($sunrisesec, $offset, $halakhicHour);
    $pelegHaMinha = calculatePelegHaMinha($sunsetsec, $offset, $halakhicHour);

    // echo("Latest Shema: $latestShema<br>"); 
    // echo("Earliest Minha: $earliestMinha<br>"); 
    // echo("Peleg HaMinha: $pelegHaMinha<br>"); 

  function outputZemanim($yom, $location, $shema, $minha, $peleg, $sunset) { 
    echo($before_widget);
    echo($before_title);
 ?>
    <div id="zemanim_container">
      <div id="zemanim_display">
  <span id="zemanim_date"><?php echo("Times for $yom <br>"); ?></span>
  <span id="zemanim_city"><?php echo("$location <br>"); ?></span>
  <span id="zemanim_hebrew">
      <?php 
      $hebcal_magic_date = '
      <script type="text/javascript" charset="utf-8" src="//www.hebcal.com/etc/hdate-he.js"></script>';
      echo($hebcal_magic_date); ?>
      <br>
  </span>
  <span id="zemanim_shema"><?php echo("Latest Shema': $shema<br>"); ?></span>
  <span id="zemanim_minha"><?php echo("Earliest Minḥa: $minha<br>"); ?></span>
  <span id="zemanim_peleg"><?php echo("Peleḡ HaMinḥa: $peleg<br>"); ?></span>
          <span id="zemanim_sunset"><?php echo("Sunset: $sunset"); ?></span>
      </div>
  </div>

<?php 
  }
  outputZemanim($yom_txt, $locationStr, $latestShema, $earliestMinha, $pelegHaMinha, $SunsetStr);

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