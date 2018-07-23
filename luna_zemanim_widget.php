<?php
/**
 * Plugin Name: Luna Zemanim Widget
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
        ?>

        <?php
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $before_widget;
        if ( ! empty( $title ) ) {
            echo $before_title . $title . $after_title;
        }
        echo __( 'Zemanim Widget Working', 'text_domain' );

        ?>
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

        $hebdate = file_get_contents('http://www.hebcal.com/shabbat/?cfg=json&geonameid=3448439&m=50');
        $hebdatejd = json_decode($hebdate, true);

        function get_location()
        {?>
            <script type="text/javascript">
            var options = {
              enableHighAccuracy: true,
              // timeout: 5000,
              maximumAge: 0
            };

            function error(err) {
              console.warn(`ERROR(${err.code}): ${err.message}`);
            }

            navigator.geolocation.getCurrentPosition(showPosition, error, options);

    function showPosition(position) {
        var posLog = position;
        console.log(posLog);
        console.log(JSON.stringify(posLog));
        console.log(posLog.toString());
        var lat = position.coords.latitude;
        var long = position.coords.longitude;
        var point = new google.maps.LatLng(lat, long);        new google.maps.Geocoder().geocode({'latLng': point}, function (res, status) {

            var response = res;

            if (res[1]) {
                for (var i = 0; i < res.length; i++) {
                    if (res[i].types[0] === "locality") {
                        var city = res[i].address_components[0].short_name;
                    } // end if loop 2

                    if (res[i].types[0] === "neighborhood") {
                        var neighborhood = res[i].address_components[0].long_name;
                    } // end if loop 2

                    if (res[i].types[0] === "administrative_area_level_1") {
                        var state = res[i].address_components[0].long_name;
                    } // end if loop 2
                } // end for loop
            } // end if loop 1

            cityStr =  city + ", " + state + ", " + "United States" + "<br>" + neighborhood;

        console.log(lat, long);
        console.log(cityStr);
        var geocoder = 
        window.lat = lat;
        window.long = long;
        window.city = city
        return latLong = [window.lat, window.long];

        });
    }

    // showPosition();
            </script>


        <?php }
        get_location();

        function luna_run_zemanim_widget() {
            // $zemanim_geolocate = '<script type="text/javascript" src="/wp-content/plugins/luna-zemanim-widget/zemanim-geolocate.js"></script>';
            // $fallback_url = "http://api.ipstack.com/96.239.116.76?access_key=62e3a66a273f35e0bde207e433850072";

            // $ch = curl_init();
            // echo("ch: $ch");
            // curl_setopt($ch, CURLOPT_URL, $fallback_url);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // $response_fb = json_decode(curl_exec($ch), true);

            // if ( $response_fb['status'] != 'OK' ) {
            //     return null;
            // }
            // echo("Response FB: $response_fb");
            // print_r($response_fb);

            // set IP address and API access key 
            $ip = '';
            $ip = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            echo("ip: $ip");
            // $ip = '134.201.250.155';
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
            $api_result = json_decode($json, true);
            // echo("api_result_initial: ");
            // echo($api_result);
            // Output the "capital" object inside "location"
            $ip_result = $api_result['ip'];
            $city_result = $api_result['city'];
            $state_result = $api_result['region_code'];
            echo("<br>api_result final: ");
            echo ("$ip_result <br>$city_result<br>$state_result");

            // print_r($api_result['location']['capital']);
            // echo("SC ZG: $sun_calc $zemanim_geolocate");
        } // end run_zemanim_
        luna_run_zemanim_widget();
        echo $after_widget;
    } // end function widget,instance

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