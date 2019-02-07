<?php
/**
 * Plugin Name: Daily Zemanim With Hebcal
 * Plugin URI: https://lunacodesdesign.com/
 * Description: Displays Zemannim (times) according to Sepharadic tradition.
 *   Uses the DB-IP API and the Google Maps API for geographic information.
 *   Uses the Sun-Calc Library (https://github.com/mourner/suncalc) for sunrise/sunset information.
 * Version: 1.3.2
 *
 * @author Luna Lunapiena
 * @link: https://lunacodesdesign.com/
 * @package Luna Zemanim Widget
 * License: GPL3+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: luna_zemanim_widget_hebcal_domain
 * Change Record:
 * ***********************************
 * 2018- - initial creation
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

/**
 * Stuff
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

		wp_enqueue_script( 'suncalc-master', plugins_url( '/suncalc-master/suncalc.js', __FILE__ ), '', $suncalc_version );
		wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&key=AIzaSyDgpmHtOYqSzG9JgJf98Isjno6YwVxCrEE', array(), true );

		$title = apply_filters( 'widget_title', $instance['title'] );

		// phpcs:disable
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		// phpcs:enable

		/**
		 * Generates the Hebrew Date from a passed in date object
		 *
		 * @param  object $date the date.
		 * @return string       The Hebrew rendition of the date.
		 */
		function generateHebrewDate($date) {
			$month = idate( 'm', $date );
			$day = idate( 'j', $date );
			$year = idate( 'Y', $date );
			$jdate = gregoriantojd( $month, $day, $year );
			$jd2 = jdtojewish( $jdate, true, CAL_JEWISH_ADD_GERESHAYIM );

			$heb_date_str = mb_convert_encoding( "$jd2", 'utf-8', 'ISO-8859-8' );
			return $heb_date_str;
		}

		/**
		 * Generates English and Hebrew dates for current day and upcoming Shabbath.
		 *
		 * @since 1.2.0
		 *gen
		 * @return array Contains English and Hebrew dates the current day and upcoming Shabbath
		 */
		function generateDatesWithHebcal() {
			$today = date( 'F, j, Y' );
			$today_int = strtotime( 'now' );
			$day_of_week = date( 'N' );
			if ( 5 == $day_of_week ) {
				$friday = strtotime( 'now' );
			} elseif ( 6 == $day_of_week ) {
				$friday = strtotime( 'yesterday' );
			} else {
				$friday = strtotime( 'next friday' );
			}

			$today_str = $today;
			$shabbat_iso = date( DATE_ISO8601, $friday );
			$today_heb_str = generateHebrewDate( $today_int );
			$shabbat_str = date( 'F, j, Y', $friday );
			$shabbat_heb_str = generateHebrewDate( $friday );
			$dates = [ $today_str, $today_heb_str, $shabbat_str, $shabbat_heb_str, $shabbat_iso ];
			return $dates;
		}
		$dates = generateDatesWithHebcal();

		/**
		 * Pre-generate the html structure for the front-end display.
		 *
		 * @since 1.0.0
		 *
		 * @param  list $dates A list containing date strings to be outputted in the HTML.
		 */
		function outputZemannimWithHebcal( $dates ) {
			$today = $dates[0];
			$today_heb = $dates[1];
			$shabbat = $dates[2];
			$shabbat_heb = $dates[3];

			?>

		<div id="zemannim_container">
				<div id="zemannim_display">
						<span id="zemannim_date">Times for <?php echo( esc_attr( $today ) ); ?><br></span>
						<span id="zemannim_city"></span>
						<span id="zemannim_hebrew"><?php echo( esc_attr( $today_heb ) ); ?><br></span>
						<span id="zemannim_shema">Latest Shema: <br></span>
						<span id="zemannim_minha">Earliest Minḥa:  <br></span>
						<span id="zemannim_peleg">Peleḡ haMinḥa:  <br></span>
						<span id="zemannim_sunset">Sunset: <br></span>
				</div>
		</div>
		<br>
		<h4 class="widgettitle widget-title shabbat-title">Shabbat Zemannim</h4>
		<div id="shabbat_zemannim_container">
				<div id="shabbat_zemannim_display">
						<span id="shzm_date">Shabbat Times for <?php echo( esc_attr( $shabbat ) ); ?><br></span>
						<span id="shzm_perasha"></span>
						<span id="shzm_date_heb"><?php echo( esc_attr( $shabbat_heb ) ); ?><br></span>
						<!-- <span id="shzm_city"></span> -->
						<span id="shzm_perasha_heb"></span>
						<span id="shzm_candles">Sunset: <br></span>
						<span id="shzm_sunset">Sunset: <br></span>
						<span id="shzm_habdala">Haḇdala: </span>
				</div>
		</div>

			<?php
		}

		outputZemannimWithHebcal( $dates );
		?>

<script type="text/javascript" defer>
/*jshint esversion: 6 */

var zemannim = document.getElementById("zemannim_container");
var z_city = document.getElementById("zemannim_city");
var z_shema = document.getElementById("zemannim_shema");
var z_minha = document.getElementById("zemannim_minha");
var z_peleg = document.getElementById("zemannim_peleg");
var z_sunset = document.getElementById("zemannim_sunset");
var shabbat_zemannim = document.getElementById("shzm_container");
var sz_date = document.getElementById("shzm_date");
var sz_date_heb = document.getElementById("shzm_date_heb");
var sz_perasha = document.getElementById("shzm_perasha");
var sz_perasha_heb = document.getElementById("shzm_perasha_heb");
var sz_candles = document.getElementById("shzm_candles");
var sz_sunset = document.getElementById("shzm_sunset");
var sz_habdala = document.getElementById("shzm_habdala");


function hebCalShab(cityStr, lat, long, tzid) {
	let urlStr = 'https://www.hebcal.com/hebcal/?v=1&cfg=json&maj=on&min=on&nx=on&ss=on&mod=off&s=on&c=on&m=20&b=18&o=on&D=on&year=now&month=2&i=off&geo=pos' + '&latitude=' + lat + '&longitude=' + long + '&tzid=' + tzid;

	/*jshint sub:true*/
	let city = cityStr;
	fetch(urlStr)
		.then(function(response) {
			return response.json();
		})
		.then(function(res) {
			let data = res["items"];
			let name = res["title"];
			// let dateStr = res["date"];
			let tmpDate = data[16]["date"];
			const date = new Date(tmpDate);
			let dateStr = date.toLocaleString('en-us', { month: 'long', day: 'numeric', year: 'numeric' });
			let engdate = "Shabbat Times for " + dateStr;

			let hebdate = data[16]["hebrew"];

			// Candles & Sunset
			let candles = data[15]["title"];
			let sunset = hebCalGetSunset(candles);
			sunset = "Sunset: " + sunset;
			let habdala = data[18]["title"];
			let index = habdala.indexOf("(") - 1;
			habdala = 'Haḇdala' + habdala.slice(index);
			// indexHab

			// Perasha Info
			let perashaHeb = data[17]["hebrew"];
			let perashaEng = data[17]["title"];

			var str = perashaEng;
			var firstSpace=str.indexOf(" ");
			var perashaShort= str.slice(firstSpace + 1);
			let a2s = ashkiToSeph(perashaShort, 'perasha');
			perashaEng = "Perasha " + a2s;

			var hebCalFinal = [engdate, hebdate, perashaHeb, perashaEng, candles, sunset, habdala];
			displayShabbatTimes(hebCalFinal, city);
			// return hebCalFinal;
			/*jshint sub:false*/
	});
}

function hebCalHol(city, lat, long, tzid) {
	let urlStr = 'https://www.hebcal.com/hebcal/?v=1&cfg=json&maj=on&min=on&nx=on&ss=on&mod=off&s=on&c=on&m=20&b=18&o=on&D=on&year=now&month=2&i=off&geo=pos' + '&latitude=' + lat + '&longitude=' + long + '&tzid=' + tzid;

	fetch(urlStr)
		.then(function(response) {
			return response.json();
		})
		.then(function(res) {
			// console.log(res);
			let data = res["items"];
			let name = res["title"];
			// let dateStr = res["date"];
			const date = new Date(res["date"]);
			// var sunsetCheck = calculateTimes(todayTimesObj, false);

			// let tzOffset = date.getTimezoneOffset();
			// tzOffsetHrs = tzOffset / 60;

			let dateStr = date.toLocaleString('en-us', { month: 'long', day: 'numeric', year: 'numeric' });
			engdate = "Shabbat Times for " + dateStr;

			let hebdate = data[9]["hebrew"];

			// Candles & Sunset
			// let candles = data[10]["title"];
			// console.log(dateStr);
			// let sunset = hebCalGetSunset(dateStr);
			// sunset = "Sunset: " + sunset;
			// let habdala = data[4]["title"];
			// let index = habdala.indexOf("(") - 1;
			// habdala = 'Haḇdala' + habdala.slice(index);
			// indexHab

			// Perasha Info
			// let perashaHeb = data[3]["hebrew"];
			// let perashaEng = data[3]["title"];

			// var str = perashaEng;
			// var firstSpace=str.indexOf(" ");
			// var perashaShort= str.slice(firstSpace + 1);
			// let a2s = ashkiToSeph(perashaShort, 'perasha');
			// perashaEng = "Perasha " + a2s;

			var hebCalFinal = [engdate, hebdate];
			timesHelper(lat, long, city);
			// displayShabbatTimes(hebCalFinal, city);

		});
}

function hebCalGetSunset(timestr) {
		let str = timestr;
		let index = str.indexOf(":") + 2;
		let time = str.replace(/\D/g,'');
		let hr = time.slice(0,1);
		let min = time.slice(-2);
		min = parseInt(min) + 18;

		if (min >= 60) {
				min -=60;
				if (min < 10) {
						min = min.toString().padStart(2,0);
				}

				hr = parseInt(hr);
				hr += 1;
		}

		time = hr + ":" + min;
		return time;

}

function ashkiToSeph(input, type) {
	/* Consider either using a second variable for Perasha vs Holiday. Or separating them into 2 holidays... or just don't bother
	for now type can either be `perasha` or `holiday`*/
	var perashaList = [
		["Parashat", "Perasha"],
		["Achrei Mot", "ʾAḥare Mot"],
		["Balak", "Balaq"],
		["Bamidbar", "Bemiḏbar"],
		["Bechukotai", "Beḥuqqotay"],
		["Beha'alotcha", "Behaʿalotekha"],
		["Behar", "Behar"],
		["Bereshit", "Bereshit"],
		["Beshalach", "Beshallaḥ"],
		["Bo", "Bo"],
		["Chayei Sara", "Ḥayye Sara"],
		["Chukat", "Ḥuqqat"],
		["Devarim", "Deḇarim"],
		["Eikev", "ʿEqeḇ"],
		["Emor", "ʾEmor"],
		["Ha'Azinu", "HaʾAzinu"],
		["Kedoshim", "Qeḏoshim"],
		["Ki Tavo", "Ki-Taḇo"],
		["Ki Teitzei", "Ki-Teṣe"],
		["Ki Tisa", "Ki Tisa"],
		["Korach", "Qoraḥ"],
		["Lech-Lecha", "Lekh-Lekha"],
		["Masei", "Masʿe"],
		["Matot", "Maṭṭot"],
		["Metzora", "Meṣoraʿ"],
		["Miketz", "Miqqeṣ"],
		["Mishpatim", "Mishpaṭim"],
		["Nasso", "Naso"],
		["Nitzavim", "Niṣṣaḇim"],
		["Noach", "Noaḥ"],
		["Pekudei", "Pequḏe"],
		["Pinchas", "Pineḥas"],
		["Re'eh", "Reʾe"],
		["Sh'lach", "Shelaḥ-Lekha"],
		["Shemot", "Shemot"],
		["Shmini", "Shemini"],
		["Shoftim", "Shopheṭim"],
		["Tazria", "Tazriaʿ"],
		["Terumah", "Teruma"],
		["Tetzaveh", "Teṣavve"],
		["Toldot", "Toleḏot"],
		["Tzav", "Ṣav"],
		["Vaera", "VaʾEra"],
		["Vaetchanan", "VaʾEtḥannan"],
		["Vayakhel", "VayYaqhel"],
		["Vayechi", "VaYeḥi"],
		["Vayeilech", "VayYelekh"],
		["Vayera", "VayYera"],
		["Vayeshev", "VayYesheḇ"],
		["Vayetzei", "VayYeṣe"],
		["Vayigash", "VayYiggash"],
		["Vayikra", "VayYiqra"],
		["Vayishlach", "VayYishlaḥ"],
		["Vezot Haberakhah", "VeZot HabBerakha"],
		["Yitro", "Yitro"],
		["Asara B'Tevet", "ʿAsara Beṭeḇet"]
	];

	var holidays = [
		['ashkiHol', 'Yitro'],
		['ashkiHol', 'ʿAsara Beṭeḇet'],
		['ashkiHol', 'Haḏlaqat Nerot'],
		['ashkiHol', 'Ḥanukka'],
		['ashkiHol', 'Ḥanukka: Ner I'],
		['ashkiHol', 'Ḥanukka: Ner II'],
		['ashkiHol', 'Ḥanukka: Ner III'],
		['ashkiHol', 'Ḥanukka: Ner IV'],
		['ashkiHol', 'Ḥanukka: Ner V'],
		['ashkiHol', 'Ḥanukka: Ner VI'],
		['ashkiHol', 'Ḥanukka: Ner VII'],
		['ashkiHol', 'Ḥanukka: Ner VIII'],
		['ashkiHol', 'Ḥanukka: Yom VIII '],
		['ashkiHol', 'ʿOmer'],
		['ashkiHol', 'ʿEreḇ Pesaḥ'],
		['ashkiHol', 'ʿEreḇ Purim'],
		['ashkiHol', 'ʿEreḇ Rosh Hashana'],
		['ashkiHol', 'ʿEreḇ Shaḇuʿot'],
		['ashkiHol', 'ʿEreḇ Simḥat Torah'],
		['ashkiHol', 'ʿEreḇ Sukot'],
		['ashkiHol', 'ʿEreḇ Tishʿa Beʾaḇ'],
		['ashkiHol', 'ʿEreḇ Yom HakKippurim'],
		['ashkiHol', 'Haḇḏala'],
		['ashkiHol', 'Lag LaʿOmer'],
		['ashkiHol', 'Seliḥot'],
		['ashkiHol', 'Pesaḥ'],
		['ashkiHol', 'Pesaḥ Yom I'],
		['ashkiHol', 'Pesaḥ Yom II'],
		['ashkiHol', 'Pesaḥ (Ḥol HaMoʿḏ) Yom II'],
		['ashkiHol', 'Pesaḥ (Ḥol HaMoʿḏ) Yom III'],
		['ashkiHol', 'Pesaḥ (Ḥol HaMoʿḏ) Yom IV'],
		['ashkiHol', 'Pesaḥ Sheni'],
		['ashkiHol', 'Pesaḥ (Ḥol HaMoʿḏ) Yom V'],
		['ashkiHol', 'Pesaḥ (Ḥol HaMoʿḏ) Yom VI'],
		['ashkiHol', 'Pesaḥ Yom VII'],
		['ashkiHol', 'Pesaḥ Yom VIII'],
		['ashkiHol', 'Purim'],
		['ashkiHol', 'Purim Qaṭan'],
		['ashkiHol', 'Rosh Ḥoḏesh'],
		['ashkiHol', 'ʾAḏar'],
		['ashkiHol', 'ʾAḏar I'],
		['ashkiHol', 'ʾAḏar II'],
		['ashkiHol', 'ʾAḇ'],
		['ashkiHol', 'Marḥeshvan'],
		['ashkiHol', 'ʾElul'],
		['ashkiHol', 'ʾIyayr'],
		['ashkiHol', 'Kislev'],
		['ashkiHol', 'Nisan'],
		['ashkiHol', 'Sheḇaṭ'],
		['ashkiHol', 'Sivan'],
		['ashkiHol', 'Tamuz'],
		['ashkiHol', 'Ṭeḇet'],
		['ashkiHol', 'Rosh Hashana'],
		['ashkiHol', 'Rosh Hashana Yom I'],
		['ashkiHol', 'Rosh Hashana Yom II'],
		['ashkiHol', 'Shabbat Ḥazon'],
		['ashkiHol', 'Shabbat HaḤoḏesh'],
		['ashkiHol', 'Shabbat Haggaḏol'],
		['ashkiHol', 'Shabbat Maḥar Ḥoḏesh'],
		['ashkiHol', 'Shabbat Naḥamu'],
		['ashkiHol', 'Shabbat Para'],
		['ashkiHol', 'Shabbat Rosh Ḥoḏesh'],
		['ashkiHol', 'Shabbat Sheqalim'],
		['ashkiHol', 'Shabbat Shuḇa'],
		['ashkiHol', 'Shabbat Zakhor'],
		['ashkiHol', 'Shaḇuʿot'],
		['ashkiHol', 'Shaḇuʿot Yom I'],
		['ashkiHol', 'Shaḇuʿot Yom II'],
		['ashkiHol', 'Shemini ʿAṣeret'],
		['ashkiHol', 'Shushan Purim'],
		['ashkiHol', 'Sigd'],
		['ashkiHol', 'Simḥat Tora'],
		['ashkiHol', 'Sukkot'],
		['ashkiHol', 'Sukkot Yom I'],
		['ashkiHol', 'Sukkot Yom II'],
		['ashkiHol', 'Sukkot (Ḥol HaMoʿḏ) Yom II'],
		['ashkiHol', 'Sukkot (Ḥol HaMoʿḏ) Yom III'],
		['ashkiHol', 'Sukkot (Ḥol HaMoʿḏ) Yom IV'],
		['ashkiHol', 'Sukkot (Ḥol HaMoʿḏ) Yom V'],
		['ashkiHol', 'Sukkot (Ḥol HaMoʿḏ) Yom VI'],
		['ashkiHol', 'Sukkot (Hoshaʿna Rabba) Yom VII'],
		['ashkiHol', 'Taʿanit Bekhorot'],
		['ashkiHol', 'Taʿanit ʾEster'],
		['ashkiHol', 'Tishʿa Beʾaḇ'],
		['ashkiHol', 'Ṭu Beʾaḇ'],
		['ashkiHol', 'Ṭu Bishḇaṭ'],
		['ashkiHol', 'Ṭu Bishḇaṭ'],
		['ashkiHol', 'Ṣom Geḏalya'],
		['ashkiHol', 'Ṣom Tamuz'],
		['ashkiHol', 'Yom Haʿaṣmaʾut'],
		['ashkiHol', 'Yom Hashoʾa'],
		['ashkiHol', 'Yom Hazzikkaron'],
		['ashkiHol', 'Yom HakKippurim'],
		['ashkiHol', 'Yom Yerushalayim'],
		['ashkiHol', 'Yom HaʿAliya'],
	];

	var i; // Reusable loop variable
		input = input.replace(/\w\S*/g, function (txt) { return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase(); });
		input = input.replace("'", "");
		for (i = 0; i < perashaList.length; i++) {
			// note: for whatever reason, this continues to loop, even past getting the match
			if (perashaList[i][0] == input) {
				return (perashaList[i][1]);
			}
		}
}


/**
 * Requests location permission from user for HTML5 Geolocation API,
 * and routes it to the relevant function.
 * @return {(number|Array)} [lat, long] coordinates
 */
function getLocation() {
	var options = {
		enableHighAccuracy: true,
		maximumAge: 0
	};

	function error(err) {
		console.warn(`ERROR(${err.code}): ${err.message}`);
	zemannim.innerHtml = "Please enable location services to display the most up-to-date Zemannim";
			getAddrDetailsByIp();
	}

	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(getLatLngByGeo, error, options);
		}
}

/**
 * getLatLngByGeo - separate [lat, long] into lat & long vars, pass to Google Maps API via getGeoDetails
 * @since  1.0.0
 * @param  {number|Array} position [lat, long]
 * @return {[type]}          [description]
 */
function getLatLngByGeo(position) {
	var pos = position;
	var lat = pos.coords.latitude;
	var long = pos.coords.longitude;

	getGeoDetails(lat, long);
}

/**
 * Parses JSON object from DB-IP API, and passes
 * lat, long, and city strings to timesHelper()
 *
 * @since  1.3.1
 * @since  1.0.0
 */
function getAddrDetailsByIp() {
	/*jshint sub:true*/
	// Get the user's ip & location info
	let urlStr = 'https://ipapi.co/json/';
	fetch(urlStr)
		.then(function(response) {
			return response.json();
		})
		.then(function(res) {
			// let ip = res["ip"];
			let city = res["city"];
			let state = res["region_code"];
			// let country = res["country_name"];
			let lat = res["latitude"];
			let long = res["longitude"];
			let tzid = res ["timezone"];
			let cityStr = city + ", " + state;

			hebCalShab(city, lat, long, tzid);
			// console.log("Get Weekly Times");
			timesHelper(lat, long, city);
		});
		/*jshint sub:false*/
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
 * Feeds lat & long coords into Google Maps API to obtain city and state info and
 * pass to generateTimes()
 *
 * @param  {[float]} lat_crd  [user's lattitude]
 * @param  {[float]} long_crd [user's longitude]
 * @return {[string]} cityStr [user's City, State]
 */
function getGeoDetails(lat_crd, long_crd) {
	let lat = lat_crd;
	let long = long_crd;
	// console.log("latLng1:", lat, long);
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

		if (null == state ) {
			var cityStr = city;
		} else {
			var cityStr = city + ", " + state;
		}

		let rightNow = new Date();
		let utc = convToUTC(rightNow);

		let tzid = getTimeZoneID(cityStr, lat, long, utc);
		// typeof(tzid);

		// setTimeout(() => {
		//   hebCalShab(cityStr, lat, long, tzid);
		// }, 2000);
		// timesHelper(lat, long, cityStr);
	});
}

function convToUTC(dateObj) {
	let time = dateObj;
	time = time.toISOString();
	time = Date.parse(time);
	time = time.toString();
	time = time.slice(0,10);

	return time;
}

function getTimeZoneID(city, lat,long, utc) {
	let tzKey = 'AIzaSyDgpmHtOYqSzG9JgJf98Isjno6YwVxCrEE';
	let tzUrlBase = 'https://maps.googleapis.com/maps/api/timezone/json?location=';
	let tzUrlStr = tzUrlBase + lat + ',' + long + '&timestamp=' + utc + '&key=' + tzKey;

	fetch(tzUrlStr)
		.then(function(response) {
			return response.json();
		})
		.then(function(res) {
			let tzid = res['timeZoneId'];
			// return tzid;
			// trace();
			hebCalHol(city, lat, long, tzid);
			hebCalShab(city, lat, long, tzid);
	}
);

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

/* Will likely deprecate, since I can do this part with hebCalShab
		May still need for Min7a, Peleg, Shema, et al
*/
function timesHelper(lat, long, city) {
	var cityStr = city;
	var todayTimesObj = SunCalc.getTimes(new Date(), lat, long);
	// var shabbatFeed = '<?php // echo( esc_html( "$dates[4]" ) ); ?>';.
	// var shabbatHelperStr = shabbatFeed.substr(0, 19);
	// var shabbatHelper = new Date(shabbatHelperStr);
	// var shabbatTimesObj = SunCalc.getTimes(new Date(shabbatHelperStr), lat, long);
	var todayTimes = calculateTimes(todayTimesObj, false);
	// var shabbatTimes = calculateTimes(shabbatTimesObj, true);
	var todayStrSet = generateTimeStrings(todayTimes, false);
	// var shabbatStrSet = generateTimeStrings(shabbatTimes, true);

	displayTimes(todayStrSet, cityStr);
	// displayShabbatTimes(shabbatStrSet, cityStr);
}

/**
 * Splits time object into year, month, day, hour, min, sec and returns buildTimeStr
 * @param  {int} timeObj Value passed in from generateTimes() after formatting from formatTime()
 * @return {string}   Time String in Y-M-D-H-M
 */
function generateTimeStrings(timeSet, shabbat) {
	var sunrise = timeSet[0];
	var sunset = timeSet[1];
	var offSet = timeSet[2];
	var sunsetDateTimeInt = timeSet[3];

	var latestShemaStr = '<span id="zemannim_shema">Latest Shema: </span>' + calculateLatestShema(sunrise, sunset, offSet);
	var earliestMinhaStr = '<span id="zemannim_minha">Earliest Minḥa: </span>' + calculateEarliestMinha(sunrise, sunset, offSet);
	var pelegHaMinhaStr = '<span id="zemannim_peleg">Peleḡ HaMinḥa: </span>' + calculatePelegHaMinha(sunrise, sunset, offSet);
	var sunsetStr = '<span id="zemannim_sunset">Sunset: </span>' + unixTimestampToDate(sunsetDateTimeInt + offSet);

	if (shabbat) {
		var candleLighting = timeSet[4];
		var habdala = timeSet[5];
		var candleLightingStr = '<span id="zemannim_habdala">Candle Lighting (18 min): </span>' + unixTimestampToDate(candleLighting + offSet);
		var habdalaStr = '<span id="zemannim_habdala">Haḇdala (20 min): </span>' + unixTimestampToDate(habdala + offSet);
		var shabbatSet = [sunsetStr, candleLightingStr, habdalaStr];

		return shabbatSet;
	} else {
		var todaySet = [latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, sunsetStr];

		return todaySet;
	}

}

/**
 * [calculateTimes description]
 * @param  {array} timeObj  Contains soon-to-be manupulated time data
 * @param  {boolean} shabbat Determines whether we are calculate times for Shabbath or weekday
 * @return {array}         An array of time value integers
 */
function calculateTimes(timeObj, shabbat) {
	var times = timeObj;
	var sunriseObj = times.sunrise;
	var offSet = sunriseObj.getTimezoneOffset() / 60;
	var offSetSec = offSet * 3600;
	var sunriseStr = generateSunStrings(sunriseObj);
	var sunsetObj = times.sunset;
	var sunsetStr = generateSunStrings(sunsetObj);
	var SunriseDateTimeInt = parseFloat((new Date(sunriseStr).getTime() / 1000) - offSetSec);
	var sunsetDateTimeInt = parseFloat((new Date(sunsetStr).getTime() / 1000) - offSetSec);
	var sunriseSec = SunriseDateTimeInt - offSet;
	var sunsetSec = sunsetDateTimeInt - offSet;

	// if (shabbat) {
	// 	var candleLightingOffset = 1080;
	// 	var habdalaOffSet = 1200;
	// 	var candleLightingSec = sunsetDateTimeInt - candleLightingOffset;
	// 	var habdalaSec = sunsetDateTimeInt + habdalaOffSet;

	// 	var timeSet = [sunriseSec, sunsetSec, offSetSec, sunsetDateTimeInt, candleLightingSec, habdalaSec];
	// } else {
	// 	var timeSet = [sunriseSec, sunsetSec, offSetSec, sunsetDateTimeInt];
	// }

	var timeSet = [sunriseSec, sunsetSec, offSetSec, sunsetDateTimeInt];
	return timeSet;
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

/**
 * Generates HTML output for display of Shabbath Times
 * @since  1.2.0
 *
 * @param  {array} timeSet an array containing
 * sunset, candle lighting, and habdala information
 * @param  {string} city    Name of the user's city
 * @return {[type]}         [description]
 */
function displayShabbatTimes(timeSet, cityStr) {
	// var city = city;
	let date = timeSet[0];
	// let city = cityStr;
	let hebDate = timeSet[1];
	let perashaHeb = timeSet[2];
	let perashaEng = timeSet[3];
	let candles = timeSet[4];
	let sunset = timeSet[5];
	let habdala = timeSet[6];
	// sz_city.innerHTML = city + "<br>";
	sz_date.innerHTML = date + "<br>";
	sz_date_heb.innerHTML = hebDate + "<br>";
	sz_perasha.innerHTML = perashaEng + "<br>";
	sz_perasha_heb.innerHTML = perashaHeb + "<br>";
	sz_candles.innerHTML = candles + "<br>";
	sz_sunset.innerHTML = sunset + "<br>";
	sz_habdala.innerHTML = habdala + "<br>";

}


	// Make sure we're ready to run our script!
	jQuery(document).ready(function($) {
		getLocation();
	});

</script>

		<?php
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
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class Luna_Zemanim_Widget_Hebcal

$lunacodes_widget = new Luna_Zemanim_Widget_Hebcal();
