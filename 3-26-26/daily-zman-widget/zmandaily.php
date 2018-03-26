<?php
/**
 * Plugin Name: Daily Zman Widget
 * Plugin URI: https://lunacodesdesign.com
 * Original Plugin URI: http://webdesign.adatosystems.com/dailyzmanwidget/
 * Description: A rewrite of the Dail Zman Widget, originally by Adato Systems
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
        wp_enqueue_script( 'jquery' );
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
            $titlehavdalah = '<span id="zmantitle">Haḇdala</span>';
        }

        ?>

<script type="text/javascript" src="https://hasepharadi.com/staging/wp-content/themes/hasepharadi/page-templates/suncalc-master/suncalc.js?ver=4.9.4"></script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDFrCM7Ao83pwu_avw-53o7cV0Ym7eLqpc" type="text/javascript"></script>

        <?php
        // Display the widget already!
        echo $before_widget;
        echo $before_title, $title, $after_title;
        ?> 
<div id="zemanim_container">
    <div id="zemanim_display">
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

<script type="text/javascript" defer>
    var z_date = document.getElementById("zemanim_date");
    var z_city = document.getElementById("zemanim_city");
    // var z_hebrew = document.getElementById("zemanim_hebrew");
    var z_shema = document.getElementById("zemanim_shema");
    var z_minha = document.getElementById("zemanim_minha");
    var z_peleg = document.getElementById("zemanim_peleg");
    var z_sunset = document.getElementById("zemanim_sunset");    var x = document.getElementById("zemanim_container");

    let latLong = null;
    var zemanim = document.getElementById("zemanim_display");
    function getZemanimContainer() {
        // 'use strict';
        // x.innerHTML = "This is a test";
        // zemanim.innerHTML = "Please Enable Location Services";
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPositionB);
            // var testing = nagivator.geolocation;
            // console.log(testing);
        }
        else {
            // console.log("fail");
            zemanim.innerHtml = "Please enable location services to display the most up-to-date Zemanim";
            // var defaultPos = [
            //     "coords"[["lat", 40], ["long", -40] ];

            // showPositionB(defaultPos);

            // console.log("Geolocation is not supported by this browser");
        }
    }

    function showPositionB(position) {
        var posLog = position;
        // position = Position {coords: Coordinates, timestamp: 1521726976626}coords: Coordinatesaccuracy: 102altitude: nullaltitudeAccuracy: nullheading: nulllatitude: 40.744372399999996longitude: -73.9580058speed: null__proto__: Coordinatestimestamp: 1521726976626__proto__: Position
        // console.log(posLog);
        // console.log(JSON.stringify(posLog));
        // console.log(posLog.toString());
        // zemanim.innerHTML = posLog;
        // 'use strict';
        var lat = position.coords.latitude;
        var long = position.coords.longitude;
        // plug it into Geocode API
        var point = new google.maps.LatLng(lat, long);        new google.maps.Geocoder().geocode({'latLng': point}, function (res, status) {
            // var zip = res[0].formatted_address.match(/,\s\w{2}\s(\d{5})/);

            var response = res;
            // console.log(res);
            // var city = res[5].formatted_address;
            // var cityStr = res[1].formatted_address.toString();

            if (res[1]) {
                for (var i = 0; i < res.length; i++) {
                    if (res[i].types[0] === "locality") {
                        var city = res[i].address_components[0].short_name;
                        // var state = res[i].address_components[2].short_name;
                        // console.log("City: ", city);
                    } // end if loop 2

                    if (res[i].types[0] === "neighborhood") {
                        var neighborhood = res[i].address_components[0].long_name;
                        // var state = res[i].address_components[2].short_name;
                        // console.log("Neighborhood: ", neighborhood);
                    } // end if loop 2

                    if (res[i].types[0] === "administrative_area_level_1") {
                        // var city = res[i].address_components[0].short_name;
                        var state = res[i].address_components[0].long_name;
                        // console.log("State: ", state);
                    } // end if loop 2
                } // end for loop
            } // end if loop 1

            // var city2 = res[2].formatted_address;
            // console.log(city2);
            cityStr =  city + ", " + state + ", " + "United States" + "<br>" + neighborhood;
            // console.log(cityStr);
            // zemanim.innerHTML = "Five";
            // var cityStr = res[1].address_components
            // console.log(cityStr);
            // console.log("Zip code is " + zip[1]);
            // console.log("City: " + city);
        // x.innerHTML = "Latitude: " + lat +
        //     "<br>Longitutde: " + long + 
        //     "<br>City: " + city;

        // console.log(lat, long);
        var geocoder = 
        window.lat = lat;
        window.long = long;
        window.city = city
        generateTimes(lat, long, cityStr);
        return latLong = [window.lat, window.long];

        });

        // console.log("LatLong1: ", lat, long);
        // var latLong = [window.lat, window.long];
        // console.log("latLong2: ", latLong);

        // latLongTest(latLong);
        // positionTest(latLong);
        // return latLong;
        // generateTimes(lat, long, city2);

    }

    getZemanimContainer();

    function latLongTest(latLong) {
        // console.log("Lat Long 1: ", latLong);
    }

    function positionTest(pos) {
        // console.log("Position Test: ", pos);
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
            // alert ("Daylight saving time!");
            return true;
        }
    }

    var dstCheck = checkForDST();
    /* Note: I may need to replace this check with the updated Data. Check a pre-DST date to be sure */
    if (dstCheck) {
        // offSet = Math.abs(tzJd['dstOffset']);
    }

    else {
        // offSet = Math.abs(tzJd['gmtOffset']);
    }

    // Improved Method - uses SunCalc Library
    function formatTime(x) {
        var reform = x.toString();
        reform = ("0" + x).slice(-2);
        return reform;
    }

    function generateTimeStrings(timeObj) {
        var year = timeObj.getFullYear();
        var month = formatTime(timeObj.getMonth() + 1);
        var day = formatTime(timeObj.getDate());
        var hour = formatTime(timeObj.getHours());
        var min = formatTime(timeObj.getMinutes());
        // var sec = formatTime(timeObj.getSeconds());
        // console.log(year, month, day, hour, min, sec);
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
        // console.log(lat, long);
        // console.log(city);
        var cityStr = city;
        var times = SunCalc.getTimes(new Date(), lat, long);
        var sunriseObj = times.sunrise;
        var offSet = sunriseObj.getTimezoneOffset() / 60;
        var offSetSec = offSet * 3600;
        // console.log("Offset: ", offSet);
        // console.log("offSetSec: ", offSetSec);
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

        displayTimes(dateStr, cityStr, latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, displaySunsetStr)
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
        // console.log("Halakhic Hour: ", halakhicHour);

        // console.log("Earliest Minḥa: ", earliestMinha);
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

        // var hebcalDate = '<script ' + 'type="text/javascript" charset="utf-8" src="//www.hebcal.com/etc/hdate-he.js"' + '></' + 'script>';

        // console.log(date, city, shema, minha, peleg, sunset);

        z_date.innerHTML = date + "<br>";
        z_city.innerHTML = city + "<br>";
        // z_hebrew.innerHTML = hebrew + "<br>";
        z_shema.innerHTML = shema + "<br>";
        z_minha.innerHTML = minha + "<br>";
        z_peleg.innerHTML = peleg + "<br>";
        z_sunset.innerHTML = sunset + "<br>";
        // zemanim.innerHTML = date + "<br>" + city + "<br"> + hebcalDate +  "<br>" + shema + "<br>" + minha + "<br>" + peleg + "<br>" + sunset;
    }
</script>

        <?php
        // echo '<span id="zmanbigtitle">Times for '.$yom_txt.'</span><br />';
        // if($instance['showlocation'] ) {echo $address.'<br />'; }
        // if($instance['hebdatetxt']) {echo $hebrewdate.'<br />';}
        // if($instance['showsunrise'] ) {echo '<span id="zmantitle">Sunrise: </span>'.date($timedisplay,$yomsunrisedatetime).'<br />';}
        // if($instance['showshema'] ) {echo $shematext.'<br />'; }
        // if($instance['showmincha'] ) {echo $minchatext.'<br />'; }
        // if($instance['showplag'] ) {echo $plagtext.'<br />'; }
        // if($instance['showsunset'] ) {echo '<span id="zmantitle">Sunset: </span>'.date($timedisplay,$yomsunsetdatetime).'<br />';}
        // if($instance['love'] ) {echo $lovetext.'<br />'; }

        // if($instance['debug'] ) {
        //     echo 'Current PHP version: ' . phpversion().'<br />';
        //     echo 'Latlong URL is '.$latlongurl.'<br />';
        //     echo 'Lat and Long is: '.$lat.' and '.$long.'<br />'; 
        //     echo 'Timzezone URL is '.$tzurl.'<br />';
        //     echo 'Time zone is '.$tzname.'<br />'; 
        //     echo 'Date timestamp: '.$yom.' and Date text is '.$yom_txt.' <br />'; 
        //     echo 'Sunset: '.$yomsunsetdatetime.' <br />'; 
        //     echo 'Hebcal jewish date URL: '.$hebcaljd['link'].'<br />'; 
        //     echo 'Weekday number: '.date('N').'<br />'; 
        //     echo 'halachichour: '.$halachichour.'<br />'; 
        //     echo 'plaginseconds: '.$plaginseconds.'<br />'; 
        // } //end debug display

        // echo '</div>';
        echo $after_widget;
        // $ip='';
        // $data='';
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
