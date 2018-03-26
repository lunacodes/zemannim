<?php
/**
 * Plugin Name: Daily Zman Widget
 * Plugin URI: http://webdesign.adatosystems.com/dailyzmanwidget/
 * Description: Enter your zipcode and select which options you want to see. 
 *   Uses the inimitable Hebcal.com site for parsha and other information, 
 *   as well as Google Maps API for geographic and sunrise/sunset information.
 * Version: 1.1
 * Author: Leon Adato
 * Author URI: http://www.adatosystems.com/
 * Change Record:
 * ***********************************
 * 2013- - initial creation
 * 2013-11-22 - matched to updates in Shabbat Zman widget
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
class AdatoSystems_DailyZman_Widget extends WP_Widget {
	/**
	* Widget setup.
	*/
	function AdatoSystems_DailyZman_Widget() {
		/* Widget settings. */
		$widget_ops = array('classname' => 'zman-daily',
		'description' => 'displays various Daily zmanim (times) for a USA zip code or world city');

		/* Widget control settings. */
		$control_ops = array('width' => 200,
		 'height' => 200,
		 'id_base' => 'zman-daily-widget');

		/* Create the widget. */
		$this->WP_Widget('zman-daily-widget', 'Daily Zmanim', $widget_ops, $control_ops);
	} //end function AdatoSystems_DailyZman_Widget


	function widget($args, $instance) {
		extract($args);
		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title']);

		// Show the love
		$lovetext = "<br /><span style=\"font-size: small;\">Developed by <A HREF=\"http://www.adatosystems.com/\">AdatoSystems.com</a>.<br />Key features by <A HREF=\"http://www.hebcal.com\">Hebcal.com</a>.</span>";
		// Set time display (seconds or no seconds)
		if($instance['showseconds']) { $timedisplay = "g:i:s A"; } else { $timedisplay = "g:i A"; }

		// Set transliteration style
		if($instance['ashki']) {  
			$titleshabbat = 'Shabbos';
			$titlehavdalah = '<span id="zmantitle">Havdolo</span>';
		} else { 
			$titleshabbat = 'Shabbat';
			$titlehavdalah = '<span id="zmantitle">Havdalah</span>';
		}

		/* What day is it today*/
		$yom = strtotime("now");
		$yom_txt = date("M d, Y", $yom);
		$yom_ymd = date("Y-m-d", $yom);

		// Get Hebrew Date from HebCal
		// more info: http://www.hebcal.com/home/219/hebrew-date-converter-rest-api
		if($instance['hebdatetxt']) {
			$hebdate = file_get_contents("http://www.hebcal.com/converter/?cfg=json&gy=".date("Y",$yom)."&gm=".date("n",$yom)."&gd=".date("j",$yom)."&g2h=1");
			$hebdatejd = json_decode($hebdate,true);
			$hebengdate = $hebdatejd['hd']." ".$hebdatejd['hm'].", ".$hebdatejd['hy'];
			$hebhebdate = $hebdatejd['hebrew'];
			if($instance['hebdatetxt'] == "e") {$hebrewdate = $hebengdate; } 
			if($instance['hebdatetxt'] == "h") {$hebrewdate = $hebhebdate; } 
			if($instance['hebdatetxt'] == "b") {$hebrewdate = $hebengdate."<br />".$hebhebdate; } 
		}

		/* JSON get lat/long from zip */
		if(!$instance['userlat']) {
			$latlongurl = "http://api.geonames.org/postalCodeLookupJSON?formatted=true&postalcode=".$instance['zip']."&country=US&date=".$yom."&username=adatosystems&style=full";
			$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $latlongurl);
  				curl_setopt($ch, CURLOPT_HEADER, false);
  				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  				$du = curl_exec($ch);
				curl_close($ch);
			$djd = json_decode(utf8_encode($du),true);
			$long = $djd['postalcodes'][0]['lng'];
			$lat = $djd['postalcodes'][0]['lat'];
			$city = $djd['postalcodes'][0]['placeName'];
			$state = $djd['postalcodes'][0]['adminCode1'];
			$country = $djd['postalcodes'][0]['countryCode'];
			$address = "$city, $state $country";
		} else {
		$lat = $instance['userlat'];
		$long = $instance['userlong'];
		$latlongurl = "http://open.mapquestapi.com/nominatim/v1/reverse.php?format=json&lat=".$lat."&lon=".$long;
		$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $latlongurl);
  				curl_setopt($ch, CURLOPT_HEADER, false);
  				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  				$du = curl_exec($ch);
				curl_close($ch);
			$djd = json_decode(utf8_encode($du),true);
			$city = $djd['address']['city'];
			$state = $djd['address']['state'];
			$country = $djd['address']['country_code'];
			$address = "$city, $state $country";
		}

		/* Get time offset for timzezone and DST */
		$tzurl = "http://api.geonames.org/timezoneJSON?lat=".$lat."&lng=".$long."&date=".$yom_ymd."&username=adatosystems";
		$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $tzurl);
  			curl_setopt($ch, CURLOPT_HEADER, false);
  			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  			$tz = curl_exec($ch);
			curl_close($ch);
		$tzjd = json_decode(utf8_encode($tz),true);
		$tzname = $tzjd['timezoneId'];
		$yomsunrise = $tzjd['dates'][0]['sunrise'];
		$yomsunset = $tzjd['dates'][0]['sunset'];
		$yomsunrisedatetime = strtotime($yomsunrise);
		$yomsunsetdatetime = strtotime($yomsunset);
		$sunrisesec = $yomsunrisedatetime+$offset;
		$sunsetsec = $yomsunsetdatetime+$offset;

		// Calculate Plag haMincha
		if($instance['showplag']) {
			if($instance['plagmethod'] == "gra") {
				$halachichour = ($sunsetsec - $sunrisesec)/12;
				$plaginseconds = $sunsetsec - ($halachichour*1.25);
				$plagtext = '<span id="zmantitle">Plag haMincha (GR\'\'A): </span>'.date($timedisplay, $plaginseconds);
			} elseif ($instance['plagmethod'] == "avr") {
				$masunrise = $sunrisesec-($instance['plagrisemin']*60);
				$masunset = $sunsetsec+($instance['plagsetmin']*60);
				$halachichour = ($masunset - $masunrise)/12;
				$plaginseconds = $masunset - ($halachichour*1.25);
				$plagtext = '<span id="zmantitle">Plag haMincha (M\'\'A): </span>'.date($timedisplay, $plaginseconds);
			} else {
				$plagtext = '<span id="zmantitle">Plag haMincha:</span> unavailable';
			}
		} //end showplag

		// Calculate Shema
		if($instance['showshema']) {
			if($instance['shemamethod'] == "gra") {
				$halachichour = ($sunsetsec - $sunrisesec)/12;
				$shemainseconds = $sunrisesec + ($halachichour*3);
				$shematext = '<span id="zmantitle">Latest Shema (GR\'\'A): </span>'.date($timedisplay, $shemainseconds);
			} elseif ($instance['shemamethod'] == "avr") {
				$masunrise = $sunrisesec-($instance['shemarisemin']*60);
				$masunset = $sunsetsec+($instance['shemasetmin']*60);
				$halachichour = ($masunset - $masunrise)/12;
				$shemainseconds = $masunrise + ($halachichour*3);
				$shematext = '<span id="zmantitle">Latest Shema (M\'\'A): </span>'.date($timedisplay, $shemainseconds);
			} else {
				$shematext = '<span id="zmantitle">Latest Shema:</span> unavailable';
			}
		} //end showshema

		// Calculate Earliest Mincha
		if($instance['showmincha']) {
			if($instance['shemamethod'] == "gra") {
				$halachichour = ($sunsetsec - $sunrisesec)/12;
				$minchainseconds = $sunrisesec + ($halachichour*6.5);
				$minchatext = '<span id="zmantitle">Earliest Mincha (GR\'\'A): </span>'.date($timedisplay, $minchainseconds);
			} elseif ($instance['shemamethod'] == "avr") {
				$masunrise = $sunrisesec-($instance['shemarisemin']*60);
				$masunset = $sunsetsec+($instance['shemasetmin']*60);
				$halachichour = ($masunset - $masunrise)/12;
				$minchainseconds = $sunrisesec + ($halachichour*6.5);
				$minchatext = '<span id="zmantitle">Earliest Mincha (M\'\'A): </span>'.date($timedisplay, $minchainseconds);
			} else {
				$minchatext = '<span id="zmantitle">Earliest Mincha is</span> unavailable';
			}
		} //end showmincha		

				// Display the widget already!
		echo $before_widget;
		echo $before_title, $title, $after_title;
		echo '<div id="dailyzman">';
		echo '<span id="zmanbigtitle">Times for '.$yom_txt.'</span><br />';
		if($instance['showlocation'] ) {echo $address.'<br />'; }
		if($instance['hebdatetxt']) {echo $hebrewdate.'<br />';}
		if($instance['showsunrise'] ) {echo '<span id="zmantitle">Sunrise: </span>'.date($timedisplay,$yomsunrisedatetime).'<br />';}
		if($instance['showshema'] ) {echo $shematext.'<br />'; }
		if($instance['showmincha'] ) {echo $minchatext.'<br />'; }
		if($instance['showplag'] ) {echo $plagtext.'<br />'; }
		if($instance['showsunset'] ) {echo '<span id="zmantitle">Sunset: </span>'.date($timedisplay,$yomsunsetdatetime).'<br />';}
		if($instance['love'] ) {echo $lovetext.'<br />'; }

		if($instance['debug'] ) {
			echo 'Current PHP version: ' . phpversion().'<br />';
			echo 'Latlong URL is '.$latlongurl.'<br />';
			echo 'Lat and Long is: '.$lat.' and '.$long.'<br />'; 
			echo 'Timzezone URL is '.$tzurl.'<br />';
			echo 'Time zone is '.$tzname.'<br />'; 
			echo 'Date timestamp: '.$yom.' and Date text is '.$yom_txt.' <br />'; 
			echo 'Sunset: '.$yomsunsetdatetime.' <br />'; 
			echo 'Hebcal jewish date URL: '.$hebcaljd['link'].'<br />'; 
			echo 'Weekday number: '.date('N').'<br />'; 
			echo 'halachichour: '.$halachichour.'<br />'; 
			echo 'plaginseconds: '.$plaginseconds.'<br />'; 
		} //end debug display

		echo '</div>';
		echo $after_widget;
	} //end function widget,instance

	function update($new_instance, $old_instance) {
				$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['zip'] = strip_tags($new_instance['zip']);
		$instance['userlat'] = strip_tags($new_instance['userlat']);
		$instance['userlong'] = strip_tags($new_instance['userlong']);
		$instance['ashki'] = $new_instance['ashki'];
		$instance['showlocation'] = $new_instance['showlocation'];
		$instance['showseconds'] = $new_instance['showseconds'];
		$instance['hebdatetxt'] = $new_instance['hebdatetxt'];
		$instance['showsunrise'] = $new_instance['showsunrise'];
		$instance['showsunset'] = $new_instance['showsunset'];
		$instance['showplag'] = $new_instance['showplag'];
		$instance['plagmethod'] = $new_instance['plagmethod'];
		$instance['plagrisemin'] = $new_instance['plagrisemin'];
		$instance['plagsetmin'] = $new_instance['plagsetmin'];
		$instance['showshema'] = $new_instance['showshema'];
		$instance['shemamethod'] = $new_instance['shemamethod'];
		$instance['shemarisemin'] = $new_instance['shemarisemin'];
		$instance['shemasetmin'] = $new_instance['shemasetmin'];
		$instance['showmincha'] = $new_instance['showmincha'];
		$instance['minchamethod'] = $new_instance['minchamethod'];
		$instance['mincharisemin'] = $new_instance['mincharisemin'];
		$instance['minchasetmin'] = $new_instance['minchasetmin'];		
		$instance['love'] = $new_instance['love'];
		$instance['debug'] = $new_instance['debug'];
		return $instance;
	} //end function update new_instance, old_instance


	function form($instance) {
				/* Set up some default widget settings. */
		$defaults = array('zip' => '90210', 'plagmethod' => 'gra', 'shemamethod' => 'gra', 'love' => 'checked');
		$instance = wp_parse_args((array) $instance, $defaults); ?>

		<!-- Title: Text Input -->
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
		<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<!-- Zip code and location options: Text Input -->
		<p>
		<label for="<?php echo $this->get_field_id('zip'); ?>">Zip code:</label>
		<input id="<?php echo $this->get_field_id('zip'); ?>" name="<?php echo $this->get_field_name('zip'); ?>" value="<?php echo $instance['zip']; ?>" size="5" maxlength="5" />
				Or provide the latitude/Longitude<br />
		<label for="<?php echo $this->get_field_id('userlat'); ?>">Latitude:</label>
		<input id="<?php echo $this->get_field_id('userlat'); ?>" name="<?php echo $this->get_field_name('userlat'); ?>" value="<?php echo $instance['userlat']; ?>" size="15" maxlength="15" /><br />
		<label for="<?php echo $this->get_field_id('userlong'); ?>">Longitude:</label>
		<input id="<?php echo $this->get_field_id('userlong'); ?>" name="<?php echo $this->get_field_name('userlong'); ?>" value="<?php echo $instance['userlong']; ?>" size="15" maxlength="15" />
		</p>

		<p style="font-weight: bold; text-align: center;">General Display Choices:</p>
		<P>
		<input class="checkbox" type="checkbox" <?php if($instance['ashki' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('ashki'); ?>" name="<?php echo $this->get_field_name('ashki'); ?>" /> 
		<label for="<?php echo $this->get_field_id('ashki'); ?>">Ashkenazi Transliterations for Headings?</label><br />
		<input class="checkbox" type="checkbox" <?php if($instance['showlocation' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('showlocation'); ?>" name="<?php echo $this->get_field_name('showlocation'); ?>" /> 
		<label for="<?php echo $this->get_field_id('showlocation'); ?>">Show Location Information?</label><br />
		<input class="checkbox" type="checkbox" <?php if($instance['showseconds' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('showseconds'); ?>" name="<?php echo $this->get_field_name('showseconds'); ?>" /> 
		<label for="<?php echo $this->get_field_id('showseconds'); ?>">Show Seconds in Time displays?</label> <br />
		<input class="checkbox" type="checkbox" <?php if($instance['showsunrise' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('showsunrise'); ?>" name="<?php echo $this->get_field_name('showsunrise'); ?>" /> 
		<label for="<?php echo $this->get_field_id('showsunrise'); ?>">Show Sunrise?</label> <br />
		<input class="checkbox" type="checkbox" <?php if($instance['showsunset' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('showsunset'); ?>" name="<?php echo $this->get_field_name('showsunset'); ?>" /> 
		<label for="<?php echo $this->get_field_id('showsunset'); ?>">Show Sunset?</label><br />
		&nbsp;&nbsp;Hebrew Date Display:<br />
		<input class="radio" type="radio" <?php if($instance['hebdatetxt']=="n") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('hebdatetxt'); ?>" value="n">Do not show Hebrew date<br />
		<input class="radio" type="radio" <?php if($instance['hebdatetxt']=="e") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('hebdatetxt'); ?>" value="e">Show Hebrew date in English<br />
		<input class="radio" type="radio" <?php if($instance['hebdatetxt']=="h") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('hebdatetxt'); ?>" value="h">Show Hebrew date in Hebrew<br />
		<input class="radio" type="radio" <?php if($instance['hebdatetxt']=="b") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('hebdatetxt'); ?>" value="b">Show Hebrew date in both<br />
		</p>

		<!-- Plag haMincha Options: -->
		<p style="font-weight: bold; text-align: center;">Plag haMincha Options:</p>
		<P>
		<input class="checkbox" type="checkbox" <?php if($instance['showplag' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('showplag'); ?>" name="<?php echo $this->get_field_name('showplag'); ?>" /> 
		<label for="<?php echo $this->get_field_id('showplag'); ?>">Show Plag haMincha?</label><br />
		&nbsp;&nbsp;Calculate using: <br />
		<input class="radio" type="radio" <?php if($instance['plagmethod']=="gra") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('plagmethod'); ?>" value="gra">use <A HREF="http://en.wikipedia.org/wiki/Zmanim#Evening">GR''A</A><br />
		<input class="radio" type="radio" <?php if($instance['plagmethod']=="avr") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('plagmethod'); ?>" value="avr">use <A HREF="http://en.wikipedia.org/wiki/Zmanim#Evening">Magen Avraham</A><br />
		<label for="<?php echo $this->get_field_id('plagrisemin'); ?>">Min before sunrise:</label>&nbsp;
		<input id="<?php echo $this->get_field_id('plagrisemin'); ?>" name="<?php echo $this->get_field_name('plagrisemin'); ?>" value="<?php echo $instance['plagrisemin']; ?>" size="2" maxlength="2" /><br />
		<label for="<?php echo $this->get_field_id('plagsetmin'); ?>">Min after sunset:</label>&nbsp;
		<input id="<?php echo $this->get_field_id('plagsetmin'); ?>" name="<?php echo $this->get_field_name('plagsetmin'); ?>" value="<?php echo $instance['plagsetmin']; ?>" size="2" maxlength="2" /><br />
		</P>

		<p style="font-weight: bold; text-align: center;">Shema Options:</p>
		<P>
		<input class="checkbox" type="checkbox" <?php if($instance['showshema' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('showshema'); ?>" name="<?php echo $this->get_field_name('showshema'); ?>" /> 
		<label for="<?php echo $this->get_field_id('showshema'); ?>">Show Latest Shema?</label><br />
		&nbsp;&nbsp;Calculate using: <br />
		<input class="radio" type="radio" <?php if($instance['shemamethod']=="gra") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('shemamethod'); ?>" value="gra">use <A HREF="http://en.wikipedia.org/wiki/Zmanim#Evening">GR''A</A><br />
		<input class="radio" type="radio" <?php if($instance['shemamethod']=="avr") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('shemamethod'); ?>" value="avr">use <A HREF="http://en.wikipedia.org/wiki/Zmanim#Evening">Magen Avraham</A><br />
		<label for="<?php echo $this->get_field_id('shemarisemin'); ?>">Min before sunrise:</label>&nbsp;
		<input id="<?php echo $this->get_field_id('shemarisemin'); ?>" name="<?php echo $this->get_field_name('shemarisemin'); ?>" value="<?php echo $instance['shemarisemin']; ?>" size="2" maxlength="2" /><br />
		<label for="<?php echo $this->get_field_id('shemasetmin'); ?>">Min after sunset:</label>&nbsp;
		<input id="<?php echo $this->get_field_id('shemasetmin'); ?>" name="<?php echo $this->get_field_name('shemasetmin'); ?>" value="<?php echo $instance['shemasetmin']; ?>" size="2" maxlength="2" /><br />
		</P>

		<p style="font-weight: bold; text-align: center;">Mincha Options:</p>
		<P>
		<input class="checkbox" type="checkbox" <?php if($instance['showmincha' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('showmincha'); ?>" name="<?php echo $this->get_field_name('showmincha'); ?>" /> 
		<label for="<?php echo $this->get_field_id('showmincha'); ?>">Show Earliest Mincha?</label><br />
		&nbsp;&nbsp;Calculate using: <br />
		<input class="radio" type="radio" <?php if($instance['minchamethod']=="gra") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('minchamethod'); ?>" value="gra">use <A HREF="http://en.wikipedia.org/wiki/Zmanim#Evening">GR''A</A><br />
		<input class="radio" type="radio" <?php if($instance['minchamethod']=="avr") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('minchamethod'); ?>" value="avr">use <A HREF="http://en.wikipedia.org/wiki/Zmanim#Evening">Magen Avraham</A><br />
		<label for="<?php echo $this->get_field_id('mincharisemin'); ?>">Min before sunrise:</label>&nbsp;
		<input id="<?php echo $this->get_field_id('mincharisemin'); ?>" name="<?php echo $this->get_field_name('mincharisemin'); ?>" value="<?php echo $instance['mincharisemin']; ?>" size="2" maxlength="2" /><br />
		<label for="<?php echo $this->get_field_id('minchasetmin'); ?>">Min after sunset:</label>&nbsp;
		<input id="<?php echo $this->get_field_id('minchasetmin'); ?>" name="<?php echo $this->get_field_name('minchasetmin'); ?>" value="<?php echo $instance['minchasetmin']; ?>" size="2" maxlength="2" /><br />
		</P>		

		<!-- Debug mode -->
		<p>
		<input class="checkbox" type="checkbox" <?php if($instance['debug' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('debug'); ?>" name="<?php echo $this->get_field_name('debug'); ?>" /> 
		<label for="<?php echo $this->get_field_id('debug'); ?>">Turn on Debug Mode?</label>
		</p>

		<!-- Donation -->
		<p>
		<input class="checkbox" type="checkbox" <?php if($instance['love' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('love'); ?>" name="<?php echo $this->get_field_name('love'); ?>" /> 
		<label for="<?php echo $this->get_field_id('love'); ?>">If you like this widget, please help by promoting it</label><br />
		<br />If you REALLY like this widget, cash never hurts. Any amount is welcome.<br />
		<A HREF="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4ABF2RK76DKK4">
		<IMG src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif">
		</a></p>
	<?php 
	} //end function form

} //class AdatoSystems_DailyZman_Widget

add_action('widgets_init', 'adatosystems_load_dailyzman');
	function adatosystems_load_dailyzman() {
	register_widget('AdatoSystems_DailyZman_Widget');
} //end function adatosystems_load_dailyzman

?>
