<?php
/**
 * Plugin Name: Shabbat Zman Widget
 * Plugin URI: http://webdesign.adatosystems.com/shabbatzmanwidget/
 * Description: Enter your zipcode and select which options you want to see. 
 *   Uses the inimitable Hebcal.com site for parsha and other information, 
 *   as well as Google Maps API for geographic and sunrise/sunset information.
 * Version: 1.8
 * Author: Leon Adato
 * Author URI: http://www.adatosystems.com/
 * Change Record:
 * ***********************************
 * 2013-10-10 - added debug code to show PHP version.
 * 2013-10-13 - minor updates for consistent text output and code cleanup
 * 2013-10-14 - update to repos and such
 * 2013-10-22 - changed URL calls to use cURL
 * 2013-10-24 - changed Lat/Long call to use geocoder.us, timezone to use 
 * 2013-11-13 - more changes to fix up time zone and dst stuff.
 * 2013-11-15 - add option for latitude/longitude instead of postal code; also returns location in that locale's language if possible
 * 2013-11-22 - fixed more calculatings, proving once again that developers should not QA their own code.
 * 2014-01-08 - changed https calls to http because it was causing errors on some systems that required certificates
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
class AdatoSystems_Zman_Widget extends WP_Widget {
	/**
	* Widget setup.
	*/
	function AdatoSystems_Zman_Widget() {
		/* Widget settings. */
		$widget_ops = array('classname' => 'frizman-shabbat',
		'description' => 'displays various Shabbat zmanim (times) for a USA zip code or world city');

		/* Widget control settings. */
		$control_ops = array('width' => 200,
		'height' => 200,
		'id_base' => 'frizman-shabbat-widget');

		/* Create the widget. */
		$this->WP_Widget('frizman-shabbat-widget', 'Shabbat Zmanim', $widget_ops, $control_ops);
	} //end function AdatoSystems_Zman_Widget

	function widget($args, $instance) {
		extract($args);
		/* Our variables from the widget settings. */
		date_default_timezone_set('UTC');
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
			$titlehavdalah = '<span id="zmantitle">Haḇdala</span>';
		} //end transliteration style

		/* What day is Friday  */
		if(date('N')==5 || date("N")==6) {
			$friday = strtotime("now");
			$friday_txt = date("M d, Y", $friday);
			$friday_ymd = date("Y-m-d", $friday);
		} else {
			$friday = strtotime( "next friday" );
			if(!$friday) {
				$daysdiff = 5-now("N");
				$friday = date('Y-m-d', strtotime(now(), " + ".$daysdiff." day"));
				$friday_ymd = date("Y-m-d", $friday);
			}
			$friday_txt = date("M d, Y", $friday );
			$friday_ymd = date("Y-m-d", $friday);
		} //end get Friday

		if(date('N')!=6) {
			$saturday = strtotime( "next saturday" );
			if(!$saturday) {
				$daysdiff = 6-now("N");
				$saturday = date('Y-m-d', strtotime(now(), " + ".$daysdiff." day"));
			}
			$saturday_txt = date("Y-m-d", $saturday);
			} else {
				$saturday = strtotime("now");
				$saturday_txt = date("Y-m-d", $saturday);
		} //end what day is saturday
?>

<?php
		/* JSON get lat/long from zip using geocoder.us */

		// New Code from modded Daily Zemanim 
?>

        <div id="location2"></div>
<!-- <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDFrCM7Ao83pwu_avw-53o7cV0Ym7eLqpc" type="text/javascript"></script> -->
<!-- <script type="text/javascript" defer>
    var yY = document.getElementById("location2");
    function getShabbatLocation() {
        // 'use strict';
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showShabbatPosition);
        }

        else {
            console.log("Geolocation is not supported by this browser");
        }
    }

    function showShabbatPosition(position) {
        'use strict';
        yY.innerHTML = "Google Data - not being plugged in just yet <br>" +
        "Latitude: " + position.coords.latitude +
            "<br>Longitutde: " + position.coords.longitude;
    }

    getShabbatLocation();
    showShabbatPosition();
</script> -->

<?php
        $ip = '';
        $ip  = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        echo("IP is: $ip <br>");

		$url = "http://api.ipinfodb.com/v3/ip-city/?key=15fd47f9a1419a3ab10a17e9f655cfff81ca7278fc86f9337a2dd334d24425fb&ip=$ip";
            $d = file_get_contents($url);        
            $data = explode(';' , $d);
            // var_dump($data);
            $lat = $data['8'];
            $long = $data['9'];
            $city =  $data['6'];
            $state =  $data['5'];
            $country =  $data['4'];
            $address = "$city, $state $country";
            // reset ip - not strictly sure if this is necessary?
            $ip = '';
            echo("Latitude: $lat <br>Longitude: $long");

        /* Get time offset for timzezone and DST */
  //       $tzurl = "http://api.geonames.org/timezoneJSON?lat=".$lat."&lng=".$long."&date=".$friday_ymd."&username=adatosystems";

		// if(!$instance['userlat']) {
		// 	$latlongurl = "http://api.geonames.org/postalCodeLookupJSON?formatted=true&postalcode=".$instance['zip']."&country=US&date=".$friday."&username=adatosystems&style=full";
		// 	$ch = curl_init();
		// 		curl_setopt($ch, CURLOPT_URL, $latlongurl);
  // 				curl_setopt($ch, CURLOPT_HEADER, false);
  // 				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	 //            $tz = curl_exec($ch);
	 //            curl_close($ch);
	 //        $tzjd = json_decode(utf8_encode($tz),true);
	 //        echo "tzjd: $tzjd";
	 //        $tzname = $tzjd['timezoneId'];
	 //        $yomsunrise = $tzjd['dates'][0]['sunrise'];
	 //        $yomsunset = $tzjd['dates'][0]['sunset'];
	 //        $yomsunrisedatetime = strtotime($yomsunrise);
	 //        $yomsunsetdatetime = strtotime($yomsunset);
	 //        $sunrisesec = $yomsunrisedatetime+$offset;
	 //        $sunsetsec = $yomsunsetdatetime+$offset;
 
  // 				$du = curl_exec($ch);
		// 		curl_close($ch);
		// 	$djd = json_decode(utf8_encode($du),true);
		// 	$city = $djd['address']['city'];
		// 	$state = $djd['address']['state'];
		// 	$country = $djd['address']['country_code'];
		// 	$address = "$city, $state $country";
		// }

		/* Get time offset for timzezone and DST */
		$tzurl = "http://api.geonames.org/timezoneJSON?lat=".$lat."&lng=".$long."&date=".$friday_ymd."&username=adatosystems";
		$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $tzurl);
  			curl_setopt($ch, CURLOPT_HEADER, false);
  			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  			$tz = curl_exec($ch);
			curl_close($ch);
		$tzjd = json_decode(utf8_encode($tz),true);
		$tzname = $tzjd['timezoneId'];
		$frisunrise = $tzjd['dates'][0]['sunrise'];
		$frisunset = $tzjd['dates'][0]['sunset'];
		//***Gives "2013-11-22 17:01". Now convert to date, and set the text to something readable
		$frisunrisedatetime = strtotime($frisunrise);
		$frisunsetdatetime = strtotime($frisunset);

$saturl = "http://api.geonames.org/timezoneJSON?lat=".$lat."&lng=".$long."&date=".$saturday_txt."&username=adatosystems";
		$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $tzurl);
  			curl_setopt($ch, CURLOPT_HEADER, false);
  			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  			$tz = curl_exec($ch);
			curl_close($ch);
		$tzjd = json_decode(utf8_encode($tz),true);
		$tzname = $tzjd['timezoneId'];
		$satsunrise = $tzjd['dates'][0]['sunrise'];
		$satsunset = $tzjd['dates'][0]['sunset'];
		$satsunrisedatetime = strtotime($satsunrise);
		$satsunsetdatetime = strtotime($satsunset);

		// Get Hebrew Date from HebCal
		// more info: http://www.hebcal.com/home/219/hebrew-date-converter-rest-api
		if($instance['hebdatetxt']) {
			$hebdateurl = "http://www.hebcal.com/converter/?cfg=json&gy=".date("Y",$friday)."&gm=".date("n",$friday)."&gd=".date("j",$friday)."&g2h=1";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $hebdateurl);
  			curl_setopt($ch, CURLOPT_HEADER, false);
  			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  			$hebdate = curl_exec($ch);
			curl_close($ch);
			//$hebdate = file_get_contents("http://www.hebcal.com/converter/?cfg=json&gy=".date("Y",$friday)."&gm=".date("n",$friday)."&gd=".date("j",$friday)."&g2h=1");
			$hebdatejd = json_decode($hebdate,true);
			$hebengdate = $hebdatejd['hd']." ".$hebdatejd['hm'].", ".$hebdatejd['hy'];
			$hebhebdate = $hebdatejd['hebrew'];
			if($instance['hebdatetxt'] == "e") {$hebrewdate = $hebengdate; } 
			if($instance['hebdatetxt'] == "h") {$hebrewdate = $hebhebdate; } 
			if($instance['hebdatetxt'] == "b") {$hebrewdate = $hebengdate."<br />".$hebhebdate; } 
		} //end get Hebcal date

		// Use the HebCal JSON for the hebrew elements (parsha hashavua, etc.)
		// information here: http://www.hebcal.com/home/195/jewish-calendar-rest-api
		if($instance['inisrael']) { $inisrael = "i=on"; } else { $inisrael = "i=off"; }
		$hebcalurl = "http://www.hebcal.com/hebcal/?v=1&cfg=json&nh=off&nx=off&year=now&month=x&ss=off&mf=off&c=off&s=on&".$inisrael."&lg=".$instance['ptext'];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $hebcalurl);
  		curl_setopt($ch, CURLOPT_HEADER, false);
  		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  		$hebcal = curl_exec($ch);
		if($instance['debug'] ) {
			echo "<h3>hebcal CURL error info</h3>";
			if ($hebcal === FALSE) {
   				die("Curl failed with error: " . curl_error($ch));
			}
		}
		curl_close($ch);

		$hebcaljd = json_decode($hebcal,true);
		if($instance['debug'] ) {
			echo "<h3>hebcal JSON error info</h3>";
			if (is_null($hebcaljd)) {
				die("Json decoding failed with error: ". json_last_error());
			}
		}
		foreach($hebcaljd['items'] as $hebstat => $hebitem) {
			foreach($hebitem as $stat=>$value) {
				if($hebitem['category'] == "parashat") {
					if($hebitem['date'] == $saturday_txt) {
						$parshaname = $hebitem['title'];
						if($instance['ptext'] == "sh" || $instance['ptext'] == "ah") {$parshaname = $parshaname." / ".$hebitem['hebrew']; }
					}
				}
			}
		} //end get Parsha reading

		// Calculate Plag haMincha
		if($instance['showplag']) {
			$sunrisesec = $frisunrisedatetime+$offset;
			$sunsetsec = $frisunsetdatetime+$offset;
			if($instance['plagmethod'] == "gra") {
				$halachichour = ($sunsetsec - $sunrisesec)/12;
				$plaginseconds = $sunsetsec - ($halachichour*1.25);
				$plagtext = '<span id="zmantitle">Peleḡ HamMinḥa: </span>'.date($timedisplay, $plaginseconds);
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
			$sunrisesec = $frisunrisedatetime+$offset;
			$sunsetsec = $frisunsetdatetime+$offset;
			if($instance['shemamethod'] == "gra") {
				$halachichour = ($sunsetsec - $sunrisesec)/12;
				$shemainseconds = $sunrisesec + ($halachichour*3);
				$shematext = '<span id="zmantitle">Latest Shema: </span>'.date($timedisplay, $shemainseconds);
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

		// Calculate Candles & havdalah
		if($instance['showcandles']) { $candletime = date($timedisplay, $frisunsetdatetime-($instance['cmin']*60 ) ); }
		if($instance['showhavdalah']) {$havdalahtime = date($timedisplay, $satsunsetdatetime+($instance['m']*60) ); }

		// Display the widget already!
		echo $before_widget;
		echo $before_title, $title, $after_title;
		echo '<div id="frizman">';
		echo '<span id="zmanbigtitle">'.$titleshabbat.' Times for '.$friday_txt.'</span><br />';
		if($instance['showlocation'] ) {echo $address.'<br />'; }
		if($instance['hebdatetxt']) {echo $hebrewdate.'<br />';}
		if($instance['showparsha'] ) {echo $parshaname.'<br />'; }
		if($instance['showplag'] ) {echo $plagtext.'<br />'; }
		if($instance['showcandles'] ) {echo '<span id="zmantitle">Candle Lighting (18 min): </span>'.$candletime.'<br />'; }
		if($instance['showfrisunset'] ) {echo '<span id="zmantitle">Sunset Friday: </span>'.date($timedisplay,$frisunsetdatetime).'<br />';}
		if($instance['showsunrise'] ) {echo '<span id="zmantitle">Sunrise: </span>'.date($timedisplay, $frisunrisedatetime).'<br /> ';}
		if($instance['showshema'] ) {echo $shematext.'<br />'; }
		if($instance['showsatsunset'] ) {echo '<span id="zmantitle">Sunset Saturday: </span>'.date($timedisplay, $satsunsetdatetime).'<br />';}
		if($instance['showhavdalah'] ) {echo $titlehavdalah.' ('.$instance['m'].' min): '.$havdalahtime.'<br />'; }
		if($instance['love'] ) {echo $lovetext.'<br />'; }

		if($instance['debug'] ) {
			echo "<h3>General debug info</h3>";
			echo 'Current PHP version: ' . phpversion().'<br />';
			echo 'Latlong URL is '.$latlongurl.'<br />';
			echo 'Lat and Long is: '.$lat.' and '.$long.'<br />'; 
			echo 'Friday URL is '.$tzurl.'<br />';
			echo 'Saturday URL is '.$saturl.'<br />';
			echo 'Time zone is '.$tzname.', tzoffset is '.$tzoffset.', dstoffset is '.$dstoffset.' and offset is '.$offset.'<br />'; 
			echo 'Friday timestamp: '.$friday.' and Friday text is '.$friday_txt.' <br />'; 
			echo 'Saturday timestamp: '.$saturday.' and Saturday text: '.$saturday_txt.' <br />'; 
			echo 'Friday Sunset: '.$frisunset.' <br />'; 
			echo 'Saturday Sunset: '.$satsunset.' <br />'; 
			echo 'hebcal URL is '.$hebcalurl.'<br />';
			echo 'Hebdate URL is '.$hebdateurl.'<br />';
			echo 'Hebcal jewish date URL: '.$hebcaljd['link'].'<br />'; 
			echo 'Weekday number: '.date('N').'<br />'; 
			echo 'halachichour: '.$halachichour.'<br />'; 
			echo 'plaginseconds: '.$plaginseconds.'<br />'; 
		} //end debug


		echo '</div>';
		echo $after_widget;
		$ip='';
		$data='';
	} //end function widget,instance


	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['zip'] = strip_tags($new_instance['zip']);
		$instance['userlat'] = strip_tags($new_instance['userlat']);
		$instance['userlong'] = strip_tags($new_instance['userlong']);
		$instance['ashki'] = $new_instance['ashki'];
		$instance['showlocation'] = $new_instance['showlocation'];
		$instance['showseconds'] = $new_instance['showseconds'];
		$instance['hebdatetxt'] = $new_instance['hebdatetxt'];
		$instance['showcandles'] = $new_instance['showcandles'];
		$instance['showparsha'] = $new_instance['showparsha'];		
		$instance['showhavdalah'] = $new_instance['showhavdalah'];
		$instance['showsunrise'] = $new_instance['showsunrise'];
		$instance['showfrisunset'] = $new_instance['showfrisunset'];
		$instance['showsatsunset'] = $new_instance['showsatsunset'];
		$instance['showplag'] = $new_instance['showplag'];
		$instance['m'] = $new_instance['m'];
		$instance['cmin'] = $new_instance['cmin'];
		$instance['ctime'] = $new_instance['ctime'];
		$instance['ptext'] = $new_instance['ptext'];
		$instance['inisrael'] = $new_instance['inisrael'];
		$instance['plagmethod'] = $new_instance['plagmethod'];
		$instance['plagrisemin'] = $new_instance['plagrisemin'];
		$instance['plagsetmin'] = $new_instance['plagsetmin'];
		$instance['showshema'] = $new_instance['showshema'];
		$instance['shemamethod'] = $new_instance['shemamethod'];
		$instance['shemarisemin'] = $new_instance['shemarisemin'];
		$instance['shemasetmin'] = $new_instance['shemasetmin'];
		$instance['love'] = $new_instance['love'];
		$instance['debug'] = $new_instance['debug'];
		return $instance;
	} //end function update

	/**
	* Displays the widget settings controls on the widget panel.
	* Make use of the get_field_id() and get_field_name() function
	* when creating your form elements. This handles the confusing stuff.
	*/
	function form($instance) {
		/* Set up some default widget settings. */
		$defaults = array('zip' => '90210', 'm' => '72', 'cmin' => '18', 'plagmethod' => 'gra', 'shemamethod' => 'gra', 'ptext' => 's', 'love' => 'checked');
		$instance = wp_parse_args((array) $instance, $defaults); ?>

		<!-- Title: Text Input -->
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
		<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<!-- Zip code and location options: Text Input -->
		<p>
		<label for="<?php echo $this->get_field_id('zip'); ?>">Zip code:</label>
		<input id="<?php echo $this->get_field_id('zip'); ?>" name="<?php echo $this->get_field_name('zip'); ?>" value="<?php echo $instance['zip']; ?>" size="5" maxlength="5" /><br />
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
		<input class="checkbox" type="checkbox" <?php if($instance['showfrisunset' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('showfrisunset'); ?>" name="<?php echo $this->get_field_name('showfrisunset'); ?>" /> 
		<label for="<?php echo $this->get_field_id('showfrisunset'); ?>">Show Friday Sunset?</label><br />
		<input class="checkbox" type="checkbox" <?php if($instance['showsatsunset' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('showsatsunset'); ?>" name="<?php echo $this->get_field_name('showsatsunset'); ?>" /> 
		<label for="<?php echo $this->get_field_id('showsatsunset'); ?>">Show Saturday Sunset?</label><br />
		&nbsp;&nbsp;Hebrew Date Display:<br />
		<input class="radio" type="radio" <?php if($instance['hebdatetxt']=="n") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('hebdatetxt'); ?>" value="n">Do not show Hebrew date<br />
		<input class="radio" type="radio" <?php if($instance['hebdatetxt']=="e") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('hebdatetxt'); ?>" value="e">Show Hebrew date in English<br />
		<input class="radio" type="radio" <?php if($instance['hebdatetxt']=="h") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('hebdatetxt'); ?>" value="h">Show Hebrew date in Hebrew<br />
		<input class="radio" type="radio" <?php if($instance['hebdatetxt']=="b") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('hebdatetxt'); ?>" value="b">Show Hebrew date in both<br />
		</p>

		<!-- Candle Lighting Options -->
		<p style="font-weight: bold; text-align: center;">Candlelighting &amp; Havdalah Choices:</p>
		<P>
		<input class="checkbox" type="checkbox" <?php if($instance['showcandles' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('showcandles'); ?>" name="<?php echo $this->get_field_name('showcandles'); ?>" /> 
		<label for="<?php echo $this->get_field_id('showcandles'); ?>">Show candle lighting time?</label><br />
		<label for="<?php echo $this->get_field_id('cmin'); ?>">Minutes before sunset:</label>&nbsp;
		<input id="<?php echo $this->get_field_id('cmin'); ?>" name="<?php echo $this->get_field_name('cmin'); ?>" value="<?php echo $instance['cmin']; ?>" size="2" maxlength="2" /><br />
		<input class="checkbox" type="checkbox" <?php if($instance['showhavdalah' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('showhavdalah'); ?>" name="<?php echo $this->get_field_name('showhavdalah'); ?>" /> 
		<label for="<?php echo $this->get_field_id('showhavdalah'); ?>">Show Havdalah Time?</label><br />
		<label for="<?php echo $this->get_field_id('m'); ?>">Minutes past sunset:</label>&nbsp;
		<input id="<?php echo $this->get_field_id('m'); ?>" name="<?php echo $this->get_field_name('m'); ?>" value="<?php echo $instance['m']; ?>" size="2" maxlength="2" />
		</p>

		<!-- Torah Porion Options -->
		<p style="font-weight: bold; text-align: center;">Torah Portion Display Options</P>
		<P>
		<input class="checkbox" type="checkbox" <?php if($instance['showparsha' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('showparsha'); ?>" name="<?php echo $this->get_field_name('showparsha'); ?>" /> 
		<label for="<?php echo $this->get_field_id('showparsha'); ?>">Show the Torah Portion name</label><br />
		<input class="checkbox" type="checkbox" <?php if($instance['inisrael' ]) echo ' checked="checked"' ?> id="<?php echo $this->get_field_id('inisrael'); ?>" name="<?php echo $this->get_field_name('inisrael'); ?>" /> 
		<label for="<?php echo $this->get_field_id('inisrael'); ?>">Show Reading for Israel</label><br />
		&nbsp;&nbsp;Display using: <br />
		<input class="radio" type="radio" <?php if($instance['ptext']=="s") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('ptext'); ?>" value="s">Sephardic Transliterations<br />
		<input class="radio" type="radio" <?php if($instance['ptext']=="h") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('ptext'); ?>" value="h">Hebrew<br />
		<input class="radio" type="radio" <?php if($instance['ptext']=="a") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('ptext'); ?>" value="a">Ashkenazic Transliterations<br />
		<input class="radio" type="radio" <?php if($instance['ptext']=="ah") { echo ' checked="checked" ';}?>  name="<?php echo $this->get_field_name('ptext'); ?>" value="ah">Ashkenazic Transliterations + Hebrew<br />
		</P>

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
		<A HREF="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9BGXLVJUQW6DA">
		<IMG src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif">
		</a></p>
		<?php
	} //end function form
} //end class AdatoSystems_Zman_Widget

add_action('widgets_init', 'adatosystems_load_widgets');
function adatosystems_load_widgets() {
	register_widget('AdatoSystems_Zman_Widget');
} //end add_action adatosystems_load_widgets
?>
