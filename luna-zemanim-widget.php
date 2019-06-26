<?php
/**
 * Plugin Name: Daily Zemanim With Hebcal
 * Plugin URI: https://lunacodesdesign.com/
 * Description: Displays Zemannim (times) according to Sepharadic tradition.
 *   Uses the DB-IP API and the Google Maps API for geographic information.
 *   Uses the Sun-Calc Library (https://github.com/mourner/suncalc) for sunrise/sunset information.
 * Version: 1.4
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

		wp_enqueue_script( 'suncalc-master', plugins_url( '/suncalc-master/suncalc.js', __FILE__ ), '', $suncalc_version );
		wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyDgpmHtOYqSzG9JgJf98Isjno6YwVxCrEE', array(), true );

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
		function generateHebrewDate( $date ) {
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
		 *
		 * @return array Contains English and Hebrew dates the current day and upcoming Shabbath
		 */
		function generatePreDates() {
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
		$dates = generatePreDates();

		/**
		 * Pre-generate the html structure for the front-end display.
		 *
		 * @since 1.0.0
		 *
		 * @param  list $dates A list containing date strings to be outputted in the HTML.
		 */
		function outputPreDates( $dates ) {
			$today = $dates[0];
			$today_heb = $dates[1];
			$shabbat = $dates[2];
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
						<!-- <span id="shzm_city"></span> -->
						<span id="shzm_perasha_heb"></span>
						<span id="shzm_candles">Sunset: <br></span>
						<span id="shzm_sunset">Sunset: <br></span>
						<span id="shzm_habdala">Haḇdala: </span>
				</div>
		</div>

			<?php
		}

		outputPreDates( $dates );
		?>

<script type="text/javascript" defer>
/* jshint esversion: 6 */
/* jshint node: true */
/* jshint strict: global */
'use strict';

var zemannim = document.getElementById('zemannim_container');
var z_date = document.getElementById('zemannim_date');
var z_city = document.getElementById('zemannim_city');
var z_shema = document.getElementById('zemannim_shema');
var z_minha = document.getElementById('zemannim_minha');
var z_peleg = document.getElementById('zemannim_peleg');
var z_sunset = document.getElementById('zemannim_sunset');
var shabbat_zemannim = document.getElementById('shzm_container');
var sz_date = document.getElementById('shzm_date');
var sz_date_heb = document.getElementById('shzm_date_heb');
var sz_perasha = document.getElementById('shzm_perasha');
var sz_perasha_heb = document.getElementById('shzm_perasha_heb');
var sz_candles = document.getElementById('shzm_candles');
var sz_sunset = document.getElementById('shzm_sunset');
var sz_habdala = document.getElementById('shzm_habdala');

function getShabbatDate(dayNum, dayDate) {
	if (dayNum < 7) {
		let i = 7 - dayNum;
		let dateMod = dayDate + i;
		let fri = new Date();
		let sat = new Date();

		fri.setDate(dateMod - 2);
		sat.setDate(dateMod - 1);
		// console.log('fri, sat', fri, sat);

		return [fri, sat];
	}
}

function getCandleTimes(data, date) {
	// console.log('getCandleTimes data date:', data, date);
	let candlesList = [];
	const d1 = new Date(date);

	data.forEach((item) => {
		let d2 = new Date(item.date);
		// console.log('d1, d2', d1, d2);
		let same = d1.getDate() === d2.getDate();
		// console.log(same);

		if ((item.category === 'candles') && same) {
			// console.log('candles item:', item, item.category);
			candlesList.push(item);
		}
	});
	return candlesList[0];
}

function getHebDate(data, date) {
	// console.log('getCandleTimes data date:', data, date);
	let hebDatesList = [];
	const d1 = new Date(date);

	data.forEach((item) => {
		let d2 = new Date(item.date);
		let same = d1.getDate() === d2.getDate();
		// console.log(same);

		if ((item.category === 'hebdate') && same) {
			hebDatesList.push(item);
			// console.log('hebDatesList', hebDatesList);
			}
		});
	return hebDatesList[0].hebrew;
}

function getPerasha(data, date) {
	let perashaList = [];
	const d1 = new Date(date);

	data.forEach((item) => {
		let d2 = new Date(item.date);
		let same = (d1.getDate() -1) === (d2.getDate());

		// console.log(item.category);
		if ((item.category === 'parashat') && same ) {
			// console.log('success', item);
			perashaList.push(item);
			// console.log('p2', perashaList);
			return perashaList[0];
		}
	});
	return perashaList[0];
}

function getHabdalaTimes(data, date) {
	let habdalaTimes = [];
	const d1 = new Date(date);

	data.forEach((item) => {
		let d2 = new Date(item.date);
		let same = d1.getDate() === d2.getDate();
		// console.log(same);

		if ((item.category === 'havdalah') && same) {
			// console.log(item.category);
			habdalaTimes.push(item);
			// console.log(habdalaTimes);
		}
	});
	return habdalaTimes;
}

function hebCalGetSunset(timestr) {
		let str = timestr;
		// let index = str.indexOf(':') + 2;
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

		time = hr + ':' + min;
		return time;
}

function getDaysInMonth(month, year) {
	return new Date(year, month, 0).getDate();
}

function hebCalShab(cityStr, lat, long, tzid) {
	const now = new Date();
	let month = now.getMonth() + 1;
	let year = now.getYear();
	let daysInMonth = getDaysInMonth(month, year);
	// tmpDayNum may be redundant w/ todayNum - doublecheck later
	let tmpDayNum = now.getDate();
	if ( daysInMonth === tmpDayNum ) {
		month = month + 1;
	}
	// console.log(now);
	// console.log("m, y, dim, tdn", month, year, daysInMonth, tmpDayNum);

	let urlStr = 'https://www.hebcal.com/hebcal/?v=1&cfg=json&maj=on&min=on&nx=on&ss=on&mod=off&s=on&c=on&m=20&b=18&o=on&D=on&year=now&month=' + month + '&i=off&geo=pos' + '&latitude=' + lat + '&longitude=' + long + '&tzid=' + tzid;
	// console.log("hebCalShab urlStr", urlStr);

	// Talk to Hebcal API
	fetch(urlStr)
		.then(function(response) {
			return response.json();
		})
		.then(function(res) {
			// console.log(res);
			let data = res.items;
			// console.log(data);

			// Today's Data
			let today = now.toLocaleString('en-us', { month: 'long', day: 'numeric', year: 'numeric' });
			let todayStr = 'Times for ' + today;
			let todayNum = now.getDay();
			let todayDate = now.getDate();

			/* Shabbath Section */
			let shabbatDate = getShabbatDate(todayNum, todayDate);
			let fri = shabbatDate[0];
			let sat = shabbatDate[1];

			// Shabbath Strings
			let dateStr = fri.toLocaleString('en-us', { month: 'long', day: 'numeric', year: 'numeric' });
			let engdate = 'Shabbat Times for ' + dateStr;
			let hebdate = getHebDate(data, fri);

			// Candles & Sunset
			let candlesData = getCandleTimes(data, fri);
			let candles = candlesData.title;
			let sunset = hebCalGetSunset(candles);
			sunset = 'Sunset: ' + sunset;

			// Habdala Info
			let habdalaData = getHabdalaTimes(data, sat);
			let habdala = habdalaData[0].title;
			let index = habdala.indexOf('(') - 1;
			habdala = 'Haḇdala' + habdala.slice(index);

			// Perasha Info
			let perasha = getPerasha(data, sat);
			// console.log('getPerasha returned:', perasha)
			let perashaHeb = perasha.hebrew;
			// console.log('perasha.title:', perasha.title)
			let perashaEng = perasha.title;
			let phIndex = perashaHeb.indexOf('ת') + 1;
			perashaHeb = 'פרשה' + perashaHeb.slice(phIndex);

			// Transliterations
			let str = perashaEng;
			let firstSpace=str.indexOf(' ');
			let perashaShort= str.slice(firstSpace + 1);
			let a2s = ashkiToSeph(perashaShort, 'p');
			// let a2sTest = ashkiToSeph("Asara B'Tevet", 'p');
			perashaEng = 'Perasha ' + a2s;

			let shabbatSet = [engdate, perashaEng, hebdate, perashaHeb, candles, sunset, habdala];
			let todaySet = timesHelper(lat, long);

			displayTimes(todaySet, shabbatSet, cityStr, todayStr);
	});
}

function ashkiToSeph(input, sel) {
	/* jshint quotmark: double */
	let perashaList = [
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

	let holidayList = [
		["Asara B'Tevet", "ʿAsara Beṭeḇet"],
		["Candle lighting", "Haḏlaqat Nerot"],
		["Chanukah", "Ḥanukka"],
		["Chanukah: 1 Candle", "Ḥanukka: Ner I"],
		["Chanukah: 2 Candles", "Ḥanukka: Ner II"],
		["Chanukah: 3 Candles", "Ḥanukka: Ner III"],
		["Chanukah: 4 Candles", "Ḥanukka: Ner IV"],
		["Chanukah: 5 Candles", "Ḥanukka: Ner V"],
		["Chanukah: 6 Candles", "Ḥanukka: Ner VI"],
		["Chanukah: 7 Candles", "Ḥanukka: Ner VII"],
		["Chanukah: 8 Candles", "Ḥanukka: Ner VIII"],
		["Chanukah: 8th Day", "Ḥanukka: Yom VIII "],
		["Days of the Omer", "ʿOmer"],
		["Erev Pesach", "ʿEreḇ Pesaḥ"],
		["Erev Purim", "ʿEreḇ Purim"],
		["Erev Rosh Hashana", "ʿEreḇ Rosh Hashana"],
		["Erev Shavuot", "ʿEreḇ Shaḇuʿot"],
		["Erev Simchat Torah", "ʿEreḇ Simḥat Torah"],
		["Erev Sukkot", "ʿEreḇ Sukot"],
		["Erev Tish'a B'Av", "ʿEreḇ Tishʿa Beʾaḇ"],
		["Erev Yom Kippur", "ʿEreḇ Yom HakKippurim"],
		["Havdalah", "Haḇḏala"],
		["Lag BaOmer", "Lag LaʿOmer"],
		["Leil Selichot", "Seliḥot"],
		["Pesach", "Pesaḥ"],
		["Pesach I", "Pesaḥ Yom I"],
		["Pesach II", "Pesaḥ Yom II"],
		["Pesach II (CH''M)", "Pesaḥ (Ḥol HaMoʿḏ) Yom II"],
		["Pesach III (CH''M)", "Pesaḥ (Ḥol HaMoʿḏ) Yom III"],
		["Pesach IV (CH''M)", "Pesaḥ (Ḥol HaMoʿḏ) Yom IV"],
		["Pesach Sheni", "Pesaḥ Sheni"],
		["Pesach V (CH''M)", "Pesaḥ (Ḥol HaMoʿḏ) Yom V"],
		["Pesach VI (CH''M)", "Pesaḥ (Ḥol HaMoʿḏ) Yom VI"],
		["Pesach VII", "Pesaḥ Yom VII"],
		["Pesach VIII", "Pesaḥ Yom VIII"],
		["Purim", "Purim"],
		["Purim Katan", "Purim Qaṭan"],
		["Rosh Chodesh %s", "Rosh Ḥoḏesh"],
		["Adar", "ʾAḏar"],
		["Adar I", "ʾAḏar I"],
		["Adar II", "ʾAḏar II"],
		["Av", "ʾAḇ"],
		["Cheshvan", "Marḥeshvan"],
		["Elul", "ʾElul"],
		["Iyyar", "ʾIyayr"],
		["Kislev", "Kislev"],
		["Nisan", "Nisan"],
		["Sh'vat", "Sheḇaṭ"],
		["Sivan", "Sivan"],
		["Tamuz", "Tamuz"],
		["Tevet", "Ṭeḇet"],
		["Rosh Hashana", "Rosh Hashana"],
		["Rosh Hashana I", "Rosh Hashana Yom I"],
		["Rosh Hashana II", "Rosh Hashana Yom II"],
		["Shabbat Chazon", "Shabbat Ḥazon"],
		["Shabbat HaChodesh", "Shabbat HaḤoḏesh"],
		["Shabbat HaGadol", "Shabbat Haggaḏol"],
		["Shabbat Machar Chodesh", "Shabbat Maḥar Ḥoḏesh"],
		["Shabbat Nachamu", "Shabbat Naḥamu"],
		["Shabbat Parah", "Shabbat Para"],
		["Shabbat Rosh Chodesh", "Shabbat Rosh Ḥoḏesh"],
		["Shabbat Shekalim", "Shabbat Sheqalim"],
		["Shabbat Shuva", "Shabbat Shuḇa"],
		["Shabbat Zachor", "Shabbat Zakhor"],
		["Shavuot", "Shaḇuʿot"],
		["Shavuot I", "Shaḇuʿot Yom I"],
		["Shavuot II", "Shaḇuʿot Yom II"],
		["Shmini Atzeret", "Shemini ʿAṣeret"],
		["Shushan Purim", "Shushan Purim"],
		["Sigd", "Sigd"],
		["Simchat Torah", "Simḥat Tora"],
		["Sukkot", "Sukkot"],
		["Sukkot I", "Sukkot Yom I"],
		["Sukkot II", "Sukkot Yom II"],
		["Sukkot II (CH''M)", "Sukkot (Ḥol HaMoʿḏ) Yom II"],
		["Sukkot III (CH''M)", "Sukkot (Ḥol HaMoʿḏ) Yom III"],
		["Sukkot IV (CH''M)", "Sukkot (Ḥol HaMoʿḏ) Yom IV"],
		["Sukkot V (CH''M)", "Sukkot (Ḥol HaMoʿḏ) Yom V"],
		["Sukkot VI (CH''M)", "Sukkot (Ḥol HaMoʿḏ) Yom VI"],
		["Sukkot VII (Hoshana Raba)", "Sukkot (Hoshaʿna Rabba) Yom VII"],
		["Ta'anit Bechorot", "Taʿanit Bekhorot"],
		["Ta'anit Esther", "Taʿanit ʾEster"],
		["Tish'a B'Av", "Tishʿa Beʾaḇ"],
		["Tu B'Av", "Ṭu Beʾaḇ"],
		["Tu BiShvat", "Ṭu Bishḇaṭ"],
		["Tu B'Shvat", "Ṭu Bishḇaṭ"],
		["Tzom Gedaliah", "Ṣom Geḏalya"],
		["Tzom Tammuz", "Ṣom Tamuz"],
		["Yom HaAtzma'ut", "Yom Haʿaṣmaʾut"],
		["Yom HaShoah", "Yom Hashoʾa"],
		["Yom HaZikaron", "Yom Hazzikkaron"],
		["Yom Kippur", "Yom HakKippurim"],
		["Yom Yerushalayim", "Yom Yerushalayim"],
		["Yom HaAliyah", "Yom HaʿAliya"]
	];
	/*jshint quotmark: single*/

	input = input.replace(/\w\S*/g, function (txt) { return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase(); });
	// Why is this second replace here?!?!?!
	// It causes trouble for Beha'alotch et al.
	// input = input.replace("'", "");

	let res = [];
	if (sel === 'p') {
		perashaList.forEach((item) => {
			// console.log(item[0]);
			let same = (input === item[0]);
			// console.log(same);
			if (same) {
				// console.log('Ashki, Seph:', item[0], ',', item[1]);
				res.push(item[1]);
				// console.log('p res', res);
			}
		});
	} else if (sel === 'h') {
		holidayList.forEach((item) => {
			// console.log(item[0]);
			let same = (input === item[0]);
			// console.log(same);
			if (same) {
				res.push[item[1]];
				// console.log('h res', res);
			}
		});
	}

	let a2s = res;
	return a2s;
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
	zemannim.innerHtml = 'Please enable location services to display the most up-to-date Zemannim';
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
	let pos = position;
	let lat = pos.coords.latitude;
	let long = pos.coords.longitude;

	getGeoDetails(lat, long);
}

/**
 * Parses JSON object from DB-IP API, and passes
 * lat, long, and city strings to hebCalShab()
 *
 * @since  1.3.1
 * @since  1.0.0
 */
function getAddrDetailsByIp() {
	// Get the user's ip & location info
	let urlStr = 'https://ipapi.co/json/';
	fetch(urlStr)
		.then(function(response) {
			return response.json();
		})
		.then(function(res) {
			// let ip = res["ip"];
			let city = res.city;
			let state = res.region_code;
			// let country = res["country_name"];
			let lat = res.latitude;
			let long = res.longitude;
			let tzid = res.timezone ;
			// let cityStr = city + ', ' + state;

			hebCalShab(city, lat, long, tzid);
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
	let {cityStr, city, state} = '';

	var point = new google.maps.LatLng(lat, long);        new google.maps.Geocoder().geocode({'latLng': point}, function (res, status) {

		if (res[0]) {
			for (let i = 0; i < res.length; i++) {
				if (res[i].types[0] === 'locality') {
					city = res[i].address_components[0].short_name;
				} // end if loop 2

				if (res[i].types[0] === 'administrative_area_level_1') {
					state = res[i].address_components[0].short_name;
				} // end if loop 2
			} // end for loop
		} // end if loop 1

		if (null == state ) {
			cityStr = city;
		} else {
			cityStr = city + ', ' + state;
		}

		let rightNow = new Date();
		let utc = convToUTC(rightNow);

		getTimeZoneID(cityStr, lat, long, utc);
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
			let tzid = res.timeZoneId;
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
	reformattedTime = ('0' + x).slice(-2);
	return reformattedTime;
}

/**
 * Splits time object into year, month, day, hour, min, sec and returns buildTimeStr
 * @param  {int} timeObj Value passed in from generateTimes() after formatting from formatTime()
 * @return {string}   Time String in Y-M-D-H-M
 */
function generateSunStrings(timeObj) {
	let year = timeObj.getFullYear();
	let month = formatTime(timeObj.getMonth() + 1);
	let day = formatTime(timeObj.getDate());
	let hour = formatTime(timeObj.getHours());
	let min = formatTime(timeObj.getMinutes());
	// let sec = formatTime(timeObj.getSeconds());
	let buildTimeStr = year + '-' + month + '-' + day + ' ' + hour + ':' + min;
	return buildTimeStr;
}

/* Will likely deprecate, since I can do this part with hebCalShab
		May still need for Min7a, Peleg, Shema, et al
*/
function timesHelper(lat, long) {
	let todayTimesObj = SunCalc.getTimes(new Date(), lat, long);
	let todayTimes = calculateSunTimes(todayTimesObj, false);
	let todayStrSet = generateTimeStrings(todayTimes, false);

	return todayStrSet;
}


/**
 * Splits time object into year, month, day, hour, min, sec and returns buildTimeStr
 * @param  {int} timeObj Value passed in from generateTimes() after formatting from formatTime()
 * @return {string}   Time String in Y-M-D-H-M
 */
function generateTimeStrings(timeSet, shabbat) {
	let sunrise = timeSet[0];
	let sunset = timeSet[1];
	let offSet = timeSet[2];
	let sunsetDateTimeInt = timeSet[3];

	let latestShemaStr = '<span id="zemannim_shema">Latest Shema: </span>' + calculateLatestShema(sunrise, sunset, offSet);
	let earliestMinhaStr = '<span id="zemannim_minha">Earliest Minḥa: </span>' + calculateEarliestMinha(sunrise, sunset, offSet);
	let pelegHaMinhaStr = '<span id="zemannim_peleg">Peleḡ HaMinḥa: </span>' + calculatePelegHaMinha(sunrise, sunset, offSet);
	let sunsetStr = '<span id="zemannim_sunset">Sunset: </span>' + unixTimestampToDate(sunsetDateTimeInt + offSet);

	if (shabbat) {
		let candleLighting = timeSet[4];
		let habdala = timeSet[5];
		let candleLightingStr = '<span id="zemannim_habdala">Candle Lighting (18 min): </span>' + unixTimestampToDate(candleLighting + offSet);
		let habdalaStr = '<span id="zemannim_habdala">Haḇdala (20 min): </span>' + unixTimestampToDate(habdala + offSet);
		let shabbatSet = [sunsetStr, candleLightingStr, habdalaStr];

		return shabbatSet;
	} else {
		let todaySet = [latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, sunsetStr];

		return todaySet;
	}

}

/**
 * [calculateSunTimes description]
 * @param  {array} timeObj  Contains soon-to-be manupulated time data
 * @param  {boolean} shabbat Determines whether we are calculate times for Shabbath or weekday
 * @return {array}         An array of time value integers
 */
function calculateSunTimes(timeObj, shabbat) {
	let times = timeObj;
	let sunriseObj = times.sunrise;
	let offSet = sunriseObj.getTimezoneOffset() / 60;
	let offSetSec = offSet * 3600;
	let sunriseStr = generateSunStrings(sunriseObj);
	let sunsetObj = times.sunset;
	let sunsetStr = generateSunStrings(sunsetObj);
	let SunriseDateTimeInt = parseFloat((new Date(sunriseStr).getTime() / 1000) - offSetSec);
	let sunsetDateTimeInt = parseFloat((new Date(sunsetStr).getTime() / 1000) - offSetSec);
	let sunriseSec = SunriseDateTimeInt - offSet;
	let sunsetSec = sunsetDateTimeInt - offSet;

	let timeSet = [sunriseSec, sunsetSec, offSetSec, sunsetDateTimeInt];
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
	var ampm = 'AM';
	var minutes = '0' + date.getMinutes();

	if (hours > 12) {
		hours -= 12;
		ampm = 'PM';
	}
	else if (hours === 0) {
		hours = 12;
	}
	var formattedTime = hours + ':' + minutes.substr(-2);
	return formattedTime + ' ' + ampm;
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
function displayTimes(todaySet, shabbatSet, city, date) {
	/* Weekday */
	let shema = todaySet[0];
	let minha = todaySet[1];
	let peleg = todaySet[2];
	let sunset = todaySet[3];

	z_date.innerHTML = date + '<br>';
	z_city.innerHTML = city + '<br>';
	z_shema.innerHTML = shema + '<br>';
	z_minha.innerHTML = minha + '<br>';
	z_peleg.innerHTML = peleg + '<br>';
	z_sunset.innerHTML = sunset + '<br>';

	/* Shabbath */
	let sh_engdate = shabbatSet[0];
	let sh_perasha_en = shabbatSet[1];
	// let city = cityStr;
	let sh_hebdate = shabbatSet[2];
	let sh_perasha_he = shabbatSet[3];
	let sh_candles = shabbatSet[4];
	let sh_sunset = shabbatSet[5];
	let sh_habdala = shabbatSet[6];

	// sz_city.innerHTML = city + '<br>';
	sz_date.innerHTML = sh_engdate + '<br>';
	sz_perasha.innerHTML = sh_perasha_en + '<br>';
	sz_date_heb.innerHTML = sh_hebdate + '<br>';
	sz_perasha_heb.innerHTML = sh_perasha_he + '<br>';
	sz_candles.innerHTML = sh_candles + '<br>';
	sz_sunset.innerHTML = sh_sunset + '<br>';
	sz_habdala.innerHTML = sh_habdala + '<br>';
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
