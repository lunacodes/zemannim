<?php

/**
 * Template Name: Geolocation Test
 */

wp_enqueue_script( 'jquery' );

?> 

<!-- I don't know why Enqueueing these two scripts doesn't work... -->
<script type="text/javascript" src="https://hasepharadi.com/staging/wp-content/themes/hasepharadi/page-templates/suncalc-master/suncalc.js?ver=4.9.4"></script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDFrCM7Ao83pwu_avw-53o7cV0Ym7eLqpc" type="text/javascript"></script>

<?php 
get_header();
get_template_part( 'tpl/tpl', 'page-title' ); ?>


<div id="cn_content" class="container row">
    
    <div class="main">
        <div id="standard_blog_full" class="twelve columns alpha omega">

<div id="zemanim_container">
    <div id="zemanim_display" style="padding: 10px;">
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

<br>
<div id="hebrew_date" style="border: 1px solid blue;">

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
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPositionB);
            // var testing = nagivator.geolocation;
            // console.log(testing);
        }

        else {
            console.log("Geolocation is not supported by this browser");
        }
    }

    function showPositionB(position) {
        // 'use strict';
        var lat = position.coords.latitude;
        var long = position.coords.longitude;
        // plug it into Geocode API
        var point = new google.maps.LatLng(lat, long);        new google.maps.Geocoder().geocode({'latLng': point}, function (res, status) {
            // var zip = res[0].formatted_address.match(/,\s\w{2}\s(\d{5})/);

            var response = res;
            console.log(res);
            // var city = res[5].formatted_address;
            // var cityStr = res[1].formatted_address.toString();

            if (res[1]) {
                for (var i = 0; i < res.length; i++) {
                    if (res[i].types[0] === "locality") {
                        var city = res[i].address_components[0].short_name;
                        // var state = res[i].address_components[2].short_name;
                        console.log("City: ", city);
                    } // end if loop 2

                    if (res[i].types[0] === "neighborhood") {
                        var neighborhood = res[i].address_components[0].long_name;
                        // var state = res[i].address_components[2].short_name;
                        console.log("Neighborhood: ", neighborhood);
                    } // end if loop 2

                    if (res[i].types[0] === "administrative_area_level_1") {
                        // var city = res[i].address_components[0].short_name;
                        var state = res[i].address_components[0].long_name;
                        console.log("State: ", state);
                    } // end if loop 2
                } // end for loop
            } // end if loop 1

            // var city2 = res[2].formatted_address;
            // console.log(city2);
            cityStr =  city + ", " + state + ", " + "United States" + "<br>" + neighborhood;
            console.log(cityStr);
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
        console.log("Lat Long 1: ", latLong);
    }

    function positionTest(pos) {
        console.log("Position Test: ", pos);
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
        var buildDateStr = "Times for " + month + " " + day + ", " + year;
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
        console.log(times);
        console.log(dateStr);
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

        console.log("Latest Shema: ", latestShema);
        return latestShema;
    }

    function calculateEarliestMinha(sunriseSec, sunsetSec, offSetSec) {
        var halakhicHour = (sunsetSec - sunriseSec) / 12;
        var minhaInSeconds = sunriseSec + (halakhicHour * 6.5) + offSetSec;
        var earliestMinha = unixTimestampToDate(minhaInSeconds);
        // console.log("Halakhic Hour: ", halakhicHour);

        console.log("Earliest Minḥa: ", earliestMinha);
        return earliestMinha;
    }

    function calculatePelegHaMinha(sunriseSec, sunsetSec, offSetSec) {
        var halakhicHour = (sunsetSec - sunriseSec) / 12;
        var minhaInSeconds = sunsetSec - (halakhicHour * 1.25) + offSetSec;
        var pelegHaMinha = unixTimestampToDate(minhaInSeconds);

        console.log("Peleḡ HaMinḥa: ", pelegHaMinha);
        return pelegHaMinha;
    }

    function displayTimes(date, city, shema, minha, peleg, sunset) {

        // var hebcalDate = '<script ' + 'type="text/javascript" charset="utf-8" src="//www.hebcal.com/etc/hdate-he.js"' + '></' + 'script>';

        console.log(date, city, shema, minha, peleg, sunset);

        z_date.innerHTML = date + "<br>";
        z_city.innerHTML = city + "<br>";
        // z_hebrew.innerHTML = hebrew + "<br>";
        z_shema.innerHTML = shema + "<br>";
        z_minha.innerHTML = minha + "<br>";
        z_peleg.innerHTML = peleg + "<br>";
        z_sunsetinnerHTML = sunset + "<br>";
        // zemanim.innerHTML = date + "<br>" + city + "<br"> + hebcalDate +  "<br>" + shema + "<br>" + minha + "<br>" + peleg + "<br>" + sunset;
    }
</script>

<br><br><br>


<?php
//if latitude and longitude are submitted
if(!empty($_POST['latitude']) && !empty($_POST['longitude'])){
    //send request and receive json data by latitude and longitude
    $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($_POST['latitude']).','.trim($_POST['longitude']).'&sensor=false';
    $json = @file_get_contents($url);
    $data = json_decode($json);
    $status = $data->status;
    
    //if request status is successful
    if($status == "OK"){
        //get address from json data
        $location = $data->results[0]->formatted_address;
    }else{
        $location =  '';
    }
    
    //return address to ajax 
    echo $location;
}
?>


<!-- <script>
jQuery(document).ready(function(){
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(showLocation);
    }else{ 
        jQuery('#location').html('Geolocation is not supported by this browser.');
    }
});

function showLocation(position){
    var latitude = position.coords.latitude;
    var longitude = position.coords.longitude;
    jQuery.ajax({
        type:'POST',
        url:'getLocation.php',
        data:'latitude='+latitude+'&longitude='+longitude,
        success:function(msg){
            if(msg){
               jQuery("#location").html(msg);
            }else{
                jQuery("#location").html('Not Available');
            }
        }
    });
}
</script> -->


<!-- <p>Your Location: <span id="location"></span></p> -->

<?php
// if latitude and longitude are submitted
if(!empty($_POST['latitude']) && !empty($_POST['longitude'])){
    //send request and receive json data by latitude and longitude
    $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($_POST['latitude']).','.trim($_POST['longitude']).'&sensor=false';
    $json = @file_get_contents($url);
    $data = json_decode($json);
    $status = $data->status;
    
    //if request status is successful
    if($status == "OK"){
        //get address from json data
        $location = $data->results[0]->formatted_address;
    }else{
        $location =  '';
    }
    
    //return address to ajax 
    echo $location;
}
?>

        </div>
    </div>