<?php
/**
 * Plugin Name: Daily Zemannim by lunacodes
 * Plugin URI: https://lunacodesdesign.com/
 * Description: Displays Zemannim (times) according to Sepharadic tradition.
 * Version: 1.4.3
 *
 * @author Luna Lunapiena
 * @link: https://lunacodesdesign.com/
 * @package Luna Zemanim Widget
 * License: GPL3+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: luna_zemanim_widget_hebcal_domain
 */

/**
 * Daily Zemannim Widget
 */
class Luna_Zemanim_Widget_Hebcal extends WP_Widget {

		/**
		 * Register widget with WordPress
		 */
	public function __construct() {
		parent::__construct(
			'luna_zemanim_widget_hebcal', // Base ID.
			__( 'Luna Zemannim Hebcal', 'luna_zemanim_widget_hebcal_domain' ), // Name.
			array( 'description' => __( 'Hebcal Adjustments', 'luna_zemanim_widget_hebcal_domain' ) ) // Args.
		);

		add_action(
			'widgets_init',
			function() {
				register_widget( 'Luna_Zemanim_Widget_Hebcal' );
			}
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args       Widget Arguments.
	 * @param array $instance   Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$suncalc_version  = date( 'ymd-Gis', filemtime( plugin_dir_path( __FILE__ ) . 'suncalc-master/suncalc.js' ) );
		$zemannim_version = date( 'ymd-Gis', filemtime( plugin_dir_path( __FILE__ ) . 'zemannim.js' ) );

		wp_enqueue_script( 'suncalc-master', plugins_url( '/suncalc-master/suncalc.js', __FILE__ ), '', $suncalc_version, true );
		wp_enqueue_script( 'google-maps-zemannim', 'https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyDgpmHtOYqSzG9JgJf98Isjno6YwVxCrEE', array(), $zemannim_version, true );
		wp_enqueue_script( 'zemannim-js', plugins_url( '/zemannim.js', __FILE__ ), array(), $zemannim_version, true );

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		/**
		 * Generates the Hebrew Date from a passed in date object
		 *
		 * @param  object $date the date.
		 * @return string       The Hebrew rendition of the date.
		 */
		function generateHebrewDate( $date ) {
			$month = idate( 'm', $date );
			$day   = idate( 'j', $date );
			$year  = idate( 'Y', $date );
			$jdate = gregoriantojd( $month, $day, $year );
			$jd2   = jdtojewish( $jdate, true, CAL_JEWISH_ADD_GERESHAYIM );

			$heb_date_str = mb_convert_encoding( "$jd2", 'utf-8', 'ISO-8859-8' );
			return $heb_date_str;
		}

		/**
		 * Generates English and Hebrew dates for current day and upcoming Shabbath.
		 *
		 * @since 1.2.0
		 *
		 * @return array Contains English and Hebrew dates the current day and upcoming Shabbath
		 */
		function generatePreDates() {
			$today       = date( 'F, j, Y' );
			$today_int   = strtotime( 'now' );
			$day_of_week = date( 'N' );
			if ( 5 === $day_of_week ) {
				$friday = strtotime( 'now' );
			} elseif ( 6 === $day_of_week ) {
				$friday = strtotime( 'yesterday' );
			} else {
				$friday = strtotime( 'next friday' );
			}

			$today_str       = $today;
			$shabbat_iso     = date( DATE_ISO8601, $friday );
			$today_heb_str   = generateHebrewDate( $today_int );
			$shabbat_str     = date( 'F, j, Y', $friday );
			$shabbat_heb_str = generateHebrewDate( $friday );
			$dates           = [ $today_str, $today_heb_str, $shabbat_str, $shabbat_heb_str, $shabbat_iso ];
			return $dates;
		}
		$dates = generatePreDates();

		/**
		 * Pre-generate the html structure for the front-end display.
		 *
		 * @since 1.0.0
		 *
		 * @param  list $dates A list containing date strings to be outputted in the HTML.
		 */
		function outputPreDates( $dates ) {
			$today       = $dates[0];
			$today_heb   = $dates[1];
			$shabbat     = $dates[2];
			$shabbat_heb = $dates[3];

			?>

		<div class="zemannim-text" id="zemannim_container">
				<div id="zemannim_display">
						<span id="zemannim_date">Times for <?php echo( esc_attr( $today ) ); ?><br></span>
						<span id="zemannim_city"></span>
						<span id="zemannim_hebrew"><?php echo( esc_attr( $today_heb ) ); ?><br></span>
						<span id="zemannim_shema">Latest Shema: <br></span>
						<span id="zemannim_minha">Earliest Minḥa: <br></span>
						<span id="zemannim_peleg">Peleḡ haMinḥa: <br></span>
						<span id="zemannim_sunset">Sunset: <br></span>
				</div>
		</div>
		<br>
		<h4 class="widgettitle widget-title shabbat-title">Shabbat Zemannim</h4>
		<div class="zemannim-text" id="shabbat_zemannim_container">
				<div id="shabbat_zemannim_display">
						<span id="shzm_date">Shabbat Times for <?php echo( esc_attr( $shabbat ) ); ?><br></span>
						<span id="shzm_perasha">Perasha <br></span>
						<span id="shzm_date_heb"><?php echo( esc_attr( $shabbat_heb ) ); ?><br></span>
						<span id="shzm_perasha_heb"></span>
						<span id="shzm_candles">Sunset: <br></span>
						<span id="shzm_sunset">Sunset: <br></span>
						<span id="shzm_habdala">Haḇdala: </span>
				</div>
		</div>

			<?php
		}

		outputPreDates( $dates );
		echo $args['after_widget'];

	} // public function widget ends here.

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'New title', 'luna_zemanim_widget_hebcal_domain' );
		}

		// Widget admin form.
		?>
	<p>
		<label for="<?php echo( esc_attr( $this->get_field_id( 'title' ) ) ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
	</p>

		<?php
	}


	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class Luna_Zemanim_Widget_Hebcal

$lunacodes_widget = new Luna_Zemanim_Widget_Hebcal();
