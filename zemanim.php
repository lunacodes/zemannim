<?php

/**
 * Template Name: Geolocation Test
 */

wp_enqueue_script( 'jquery' );

// add_action( 'wp_enqueue_scripts', 'enqueue_sun_calc' );
// function enqueue_sun_calc() {
//     wp_enqueue_script( 'sunCalc', get_stylesheet_directory_uri() . '/page-templates/suncalc-master/suncalc.js' );
// }
?> 

<script type="text/javascript" src="https://hasepharadi.com/staging/wp-content/themes/hasepharadi/page-templates/suncalc-master/suncalc.js?ver=4.9.4"></script>
<script type="text/javascript">
</script>

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDFrCM7Ao83pwu_avw-53o7cV0Ym7eLqpc" type="text/javascript"></script>

<?php 
get_header();
get_template_part( 'tpl/tpl', 'page-title' ); ?>


<div id="cn_content" class="container row">
    
    <div class="main">
        <div id="standard_blog_full" class="twelve columns alpha omega">

<div id="locationB" style="border: 1px solid red;"></div>

<br>
<div id="locationAwesome" style="border: 1px solid green;"></div>
<div id="Awesome2" style="border: 1px solid blue;">
    <script type="text/javascript" charset="utf-8"
 src="//www.hebcal.com/etc/hdate-he.js"></script>

</div>


<script type="text/javascript" defer>
    var x = document.getElementById("locationB");
    let latLong = null;
    var awesome = document.getElementById("locationAwesome");
    function getLocationB() {
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
            var zip = res[0].formatted_address.match(/,\s\w{2}\s(\d{5})/);

            var city = res[5].formatted_address;
            console.log("Zip code is " + zip[1]);
            console.log("City: " + city);
        x.innerHTML = "Latitude: " + lat +
            "<br>Longitutde: " + long + 
            "<br>Zip: " + zip[1] + 
            "<br>City: " + city;

        console.log(lat, long);
        window.lat = lat;
        window.long = long;
        return latLong = [window.lat, window.long];
        });

        console.log("LatLong1: ", lat, long);
        var latLong = [window.lat, window.long];
        console.log("latLong2: ", latLong);

        latLongTest(latLong);
        positionTest(latLong);
        // return latLong;

    }

    getLocationB();
    function latLongTest(latLong) {
        console.log("Lat Long 1: ", latLong);
    }

    function positionTest(pos) {
        console.log("Position Test: ", pos);
    }

    function buildHebCalQuery() {
        // var dateInfo = getDateInfo();
        var d = new Date();
        var year = d.getFullYear();
        var month = '' + (d.getMonth() + 1);
        var day = '' + d.getDate();
        console.log("y, m d", year, month, day);

        var hebCalQuery = "https://hebcal.com/convert/?cfg=json&gy=" + year + "&gm=" + month + "&gd=" + day + "&g2h=1";
        return hebCalQuery;
    }

    function formatDate() {
        var d = new Date();
        var month = '' + (d.getMonth() + 1);
        var day = '' + d.getDate();
        var year = d.getFullYear();
        // console.log(d, month, day, year);

        if (month.length <2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }

    var yom_ymd = formatDate();
    console.log("yom_ymd = ", yom_ymd);

    // Timzone Stuff for Sunrise & Sunset
    // var tzJd = {"sunrise":"2018-03-20 06:59","lng":-74.006,"countryCode":"US","gmtOffset":-5,"rawOffset":-5,"sunset":"2018-03-20 19:07","timezoneId":"America/New_York","dstOffset":-4,"dates":[{"date":"2018-03-20","sunrise":"2018-03-20 06:59","sunset":"2018-03-20 19:07"}],"countryName":"United States","time":"2018-03-20 14:37","lat":40.7143};
    // console.log("tzJd: ", tzJd);
    var tzJd = {"sunrise":"2018-03-22 06:56","lng":-74.006,"countryCode":"US","gmtOffset":-5,"rawOffset":-5,"sunset":"2018-03-22 19:10","timezoneId":"America/New_York","dstOffset":-4,"dates":[{"date":"2018-03-21","sunrise":"2018-03-21 06:58","sunset":"2018-03-21 19:09"}],"countryName":"United States","time":"2018-03-21 22:03","lat":40.7143};
    var tzName = tzJd['timezoneId'];
    // var offSet = tzJd['gmtOffset'];
    var offSet;
    var yomSunrise = tzJd['dates'][0]['sunrise'];
    var yomSunset = tzJd['dates'][0]['sunset'];
    console.log("Yom Sunrise: ", yomSunrise, '\n',  " Yom Sunset: ", yomSunset);

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
    if (dstCheck) {
        offSet = Math.abs(tzJd['dstOffset']);
    }

    else {
        offSet = Math.abs(tzJd['gmtOffset']);
    }
    offSetSec = offSet * 3600;
    console.log("Offset: ", offSet);
    console.log("offSetSec: ", offSetSec);


    // var sunriseSec = yomSunriseDateTimeInt - offSet;
    // var sunsetSec = yomSunsetDateTimeInt - offSet;
    // console.log("sunriseSec: ", sunriseSec, "sunsetSec", sunsetSec);

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

    // Replace with generated lat & long
    var times = SunCalc.getTimes(new Date(), 40.7443683, -73.95796779999999);
    var sunriseObj = times.sunrise;
    var sunriseStr = generateTimeStrings(sunriseObj);
    var sunsetObj = times.sunset;
    var sunsetStr = generateTimeStrings(sunsetObj);
    console.log(times);
    // console.log("Sunrise, Sunset: ", sunriseStr, sunsetStr);

    console.log("/// Begin DateTime Debug: ///");
    var SunriseDateTimeInt = parseFloat((new Date(sunriseStr).getTime() / 1000) - offSetSec);
    var SunsetDateTimeInt = parseFloat((new Date(yomSunset).getTime() / 1000) - offSetSec);
    console.log("SunriseDateTimeInt: ", SunriseDateTimeInt);
    console.log("SunsetDateTimeInt: ", SunsetDateTimeInt);

    var yomSunriseDateTimeInt = parseFloat((new Date(yomSunrise).getTime() / 1000) - offSetSec);
    var yomSunsetDateTimeInt = parseFloat((new Date(yomSunset).getTime() / 1000) - offSetSec);
    console.log("yomSunriseDateTimeInt: ", yomSunriseDateTimeInt);
    console.log("yomSunsetDateTimeInt: ", yomSunsetDateTimeInt);
    console.log("/// End DateTime Debug ///");

    console.log("/// Begin TimeSec Debug ///")
    var sunriseSec = SunriseDateTimeInt - offSet;
    var sunsetSec = SunsetDateTimeInt - offSet;
    console.log("SunriseSec: ", sunriseSec);
    console.log("SunsetSec: ", sunsetSec);

    var yomSunriseSec = yomSunriseDateTimeInt - offSet;
    var yomSunsetSec = yomSunsetDateTimeInt - offSet;
    console.log("yomSunriseSec: ", yomSunriseSec);
    console.log("yomSunsetSec: ", yomSunsetSec);

    console.log("/// End TimeSec Debug ///");
    function unixTimestampToDate(timestamp) {
        var date = new Date(timestamp * 1000);
        var hours = date.getHours();
        var ampm = "AM";
        var minutes = "0" + date.getMinutes();
        // console.log("minutes1: ", minutes);
        // console.log("minutes2: ", minutes.substr(-2));
        // var seconds = "0" + date.getSeconds();

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
    function calculateLatestShema() {
        var halakhicHour = Math.abs((sunsetSec - sunriseSec) / 12);
        var shemaInSeconds = sunriseSec + (halakhicHour * 3) + offSetSec;
        var latestShema = unixTimestampToDate(shemaInSeconds);
        // var parsedShema = parseFloat(latestShema);
        // console.log("latestShema - Flat, Round, Floor, Ceiling", parsedShema, Math.round(parsedShema), Math.floor(parsedShema), Math.ceil(parsedShema));
        var yomHalakhicHour = (yomSunsetSec - yomSunriseSec) / 12;
        var yomShemaInSeconds = yomSunriseSec + (yomHalakhicHour * 3) + offSetSec;
        var yomLatestShema = unixTimestampToDate(yomShemaInSeconds);
        // console.log("Halakhic Hour: ", halakhicHour);
        console.log("/// Begin Shema Debug ///")
        console.log("Shema Debug: New Halakhic Hour, Shema In Seconds, Latest Shema", halakhicHour, shemaInSeconds, latestShema);
        console.log("Shema Debug: Old Halakhic Hour, Shema In Seconds, Latest Shema", yomHalakhicHour, yomShemaInSeconds, yomLatestShema);
        console.log("/// End Debug ///");

        // console.log("Latest Shema: ", latestShema);
    }

    calculateLatestShema();

    function calculateEarliestMinha() {
        var halakhicHour = (sunsetSec - sunriseSec) / 12;
        var minhaInSeconds = sunriseSec + (halakhicHour * 6.5) + offSetSec;
        var earliestMinha = unixTimestampToDate(minhaInSeconds);
        // console.log("Halakhic Hour: ", halakhicHour);
        console.log("Earliest Minḥa: ", earliestMinha);

    }

    calculateEarliestMinha();

    function calculatePelegHaMinha() {
        var halakhicHour = (sunsetSec - sunriseSec) / 12;
        var minhaInSeconds = sunsetSec - (halakhicHour * 1.25) + offSetSec;
        var pelegHaMinha = unixTimestampToDate(minhaInSeconds);
        // console.log("Halakhic Hour: ", halakhicHour);
        console.log("Peleḡ HaMinḥa: ", pelegHaMinha);

    }

    calculatePelegHaMinha();

    // Display Sunset
    var displaySunset = unixTimestampToDate(yomSunsetDateTimeInt+offSetSec);
    console.log("Sunset: ", displaySunset);
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