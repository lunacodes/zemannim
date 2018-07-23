<?php

/**
 * Template Name: Geolocation Test
 */

wp_enqueue_script( 'jquery' );
?> 

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDFrCM7Ao83pwu_avw-53o7cV0Ym7eLqpc" type="text/javascript"></script>

<?php 
get_header();
get_template_part( 'tpl/tpl', 'page-title' ); ?>


<div id="cn_content" class="container row">
    
    <div class="main">
        <div id="standard_blog_full" class="twelve columns alpha omega">

<div id="locationB" style="border: 1px solid red;"></div>

<!-- <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDFrCM7Ao83pwu_avw-53o7cV0Ym7eLqpc" type="text/javascript"></script> -->
<br>
<div id="locationAwesome" style="border: 1px solid green;"></div>
<div id="Awesome2" style="border: 1px solid blue;"></div>


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

        // global arrayTest = 5;
        console.log(lat, long);
        window.lat = lat;
        window.long = long;
        // return latLong = [window.lat, window.long];
        });

        // return arrayTest;
        console.log("LatLong1: ", lat, long);
        var latLong = [lat, long];
        listen(latLong);
        console.log("latLong2: ", latLong);
        return latLong;

        // latLongTest(latLong);
    }

    getLocationB();
    function latLongTest(latLong) {
        console.log(latLong);
        console.log(lat);
    }

    function listen(pos) {
        console.log("listen worked!");
        var pos1 = pos;

        var hebCalQueryStr = buildHebCalQuery();
        console.log(hebCalQueryStr);
        let fileContents = null;
        function readContents(events) {
            fileContents = this.responseText;
            // console.log(fileContents);
        }

        // Get the Data from the Hebcal URL
        let xmlhttp = new XMLHttpRequest();
        xmlhttp.addEventListener("load", readContents, true);
        awesome.addEventListener("load", (e) => {console.log("Event", e)});
        xmlhttp.open("GET", "https://www.hebcal.com/converter/?cfg=json&gy=2018&gm=3&gd=19&g2h=1", true);
        xmlhttp.send();
        // console.log(xmlhttp);

        // Not the ideal solution
        function doWork() {
            if (fileContents === null) {
                window.setTimeout(doWork, 100);
                return;
            }
            // the hebDateStr here is an unnecessary extra step
            // steps could be combined
            var hebDate = JSON.parse(fileContents);
            var hebDateStr = hebDate["hebrew"];
            console.log(hebDateStr);
            awesome.innerHTML = "Hebrew Date: " + hebDateStr;
        }
        doWork();

    }

    function buildHebCalQuery() {
        // var dateInfo = getDateInfo();
        var d = new Date();
        var year = d.getFullYear();
        var month = '' + (d.getMonth() + 1);
        var day = '' + d.getDate();

        var hebCalQuery ="https://hebcal.com/convert/?cfg=json&gy=" + year + "&gm=" + month + "&gd=" + day + "&g2h=1";
        return hebCalQuery;
    }


    // listen(latLong);

    // Output date in correct format

    // function getDateInfo() {
    //     var d = new Date();
    //     var month = '' + (d.getMonth() + 1);
    //     var day = '' + d.getDate();
    //     var year = d.getFullYear();

    //     return [year, month, day];
    // }

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
    // console.log("yom_ymd = ", yom_ymd);


    // var hebcalData = {"gy":2018,"gm":3,"gd":19,"hy":5778,"hm":"Nisan","hd":3,"hebrew":"\u05d2\u05f3 \u05d1\u05bc\u05b0\u05e0\u05b4\u05d9\u05e1\u05b8\u05df \u05ea\u05e9\u05e2\u05f4\u05d7","events":["Parashat Tzav"]};


    // Do in PHP instead??
    
    /* Time Zone Section */
    var tzAwesome = document.getElementById("Awesome2");
    let tzContents = null;
    function readTzContents(event) {
        tzContents = this.responseText;
        console.log("tzContents", tzContents);
    }

    // jQuery(document).ready(function($) {
    //     $.ajax({
    //         url: "https://api.geonames.org/timezoneJSON?lat=40.7143&lng=-74.006&date=2018-03-20&username=lunacodes",
    //         success: function(data) {
    //             // console.log($(data));
    //             console.log("Success Yo!");
    //         }
    //     });
    // });

    // let xmltzc = new XMLHttpRequest();
    // xmltzc.addEventListener("load", readTzContents, false);
    // tzAwesome.addEventListener("load", (e) => {console.log(e)});
    // xmltzc.open("GET", "https://api.geonames.org/timezoneJSON?lat=40.7143&lng=-74.006&date=2018-03-20&username=lunacodes", true);
    // xmltzc.send();
    
    // function tzcDoWork() {
    //     if (tzContents === null) {
    //         window.setTimeout(doWork, 100);
    //         return;
    //     }
    //     console.log(tzContents);
    // }
    // tzcDoWork();

    /* Note: I need the data from the Hebcal function */
    var timeZoneUrl = 'http://api.geonames.org/timezoneJSON?lat=40.7143&lng=-74.006&date=2018-03-20&username=lunacodes ';
    /* Put in xmlHttp code to actually process the url here
    Instead of pretending like I have the data (as I'm doing rn) */
    var tzJd = {"sunrise":"2018-03-20 06:59","lng":-74.006,"countryCode":"US","gmtOffset":-5,"rawOffset":-5,"sunset":"2018-03-20 19:07","timezoneId":"America/New_York","dstOffset":-4,"dates":[{"date":"2018-03-20","sunrise":"2018-03-20 06:59","sunset":"2018-03-20 19:07"}],"countryName":"United States","time":"2018-03-20 14:37","lat":40.7143};
    // console.log("tzJd: ", tzJd);
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
    // console.log("Offset: ", offSet);

    var yomSunriseDateTimeInt = parseInt((new Date(yomSunrise).getTime() / 1000) - offSetSec);
    var yomSunsetDateTimeInt = parseInt((new Date(yomSunset).getTime() / 1000) - offSetSec);
    console.log("SunriseDateTimeInt: ", yomSunriseDateTimeInt);
    console.log("SunsetDateTimeInt: ", yomSunsetDateTimeInt);

    var sunriseSec = yomSunriseDateTimeInt - offSet;
    var sunsetSec = yomSunsetDateTimeInt - offSet;
    console.log("sunriseSec: ", sunriseSec, "sunsetSec", sunsetSec);

    // Calculate Shema
    function unixTimestampToDate(timestamp) {
        var date = new Date(timestamp * 1000);
        var hours = date.getHours();
        var ampm = "AM";
        var minutes = "0" + date.getMinutes();
        // var seconds = "0" + date.getSeconds();

        if (hours > 12) {
            hours -= 12;
            ampm = "PM";
        }
        else if (hours === 0) {
            hours = 12;
        }
        // console.log("Date: ", date, "Hours: ", hours, "Minute: ", minutes, ampm);
        var formattedTime = hours + ':' + minutes.substr(-2);;
        return formattedTime + " " + ampm;
    }

    function calculateLatestShema() {
        var halakhicHour = (sunsetSec - sunriseSec) / 12;
        var shemaInSeconds = sunriseSec + (halakhicHour * 3) + offSetSec;
        var latestShema = unixTimestampToDate(shemaInSeconds);
        // console.log("Halakhic Hour: ", halakhicHour);
        console.log("Latest Shema: ", latestShema);
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
        $yom = strtotime("now");
        $yom_txt = date("M d, Y", $yom);
        $yom_ymd = date("Y-m-d", $yom);
        echo('$yom = ' . $yom . "<br>");
        echo('$yom_txt = ' . $yom_txt . "<br>");
        echo('$yom_ymd = ' . $yom_ymd . "<br>");
        // echo("Yom YMD: " . $yom_ymd);

        // Get Hebrew Date from HebCal
        // more info: http://www.hebcal.com/home/219/hebrew-date-converter-rest-api
            $hebdate_str = "http://www.hebcal.com/converter/?cfg=json&gy=".date("Y",$yom)."&gm=".date("n",$yom)."&gd=".date("j",$yom)."&g2h=1";
            echo("<br> $hebdate_str <br>");
            $hebdate = file_get_contents("http://www.hebcal.com/converter/?cfg=json&gy=".date("Y",$yom)."&gm=".date("n",$yom)."&gd=".date("j",$yom)."&g2h=1");
            echo($hebdate);
            $hebdatejd = json_decode($hebdate,true);
            // var_dump($hebdatejd);
            $hebengdate = $hebdatejd['hd']." ".$hebdatejd['hm'].", ".$hebdatejd['hy'];
            $hebhebdate = $hebdatejd['hebrew'];
            if($instance['hebdatetxt'] == "e") {$hebrewdate = $hebengdate; } 
            if($instance['hebdatetxt'] == "h") {$hebrewdate = $hebhebdate; } 
            if($instance['hebdatetxt'] == "b") {$hebrewdate = $hebengdate."<br />".$hebhebdate; } 
        
                /* JSON get lat/long from zip */

            $ip = '';
            // $ip  = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $ip = $_SERVER['REMOTE_ADDR'];
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
            // $ip = '';
            // echo("Latitude: $lat <br>Longitude: $long");

        /* Get time offset for timzezone and DST */
        $tzurl = "http://api.geonames.org/timezoneJSON?lat=".$lat."&lng=".$long."&date=".$yom_ymd."&username=lunacodes";
        echo("<br> tzurl: $tzurl <br>");
        $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tzurl);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $tz = curl_exec($ch);
            curl_close($ch);
        $tzjd = json_decode(utf8_encode($tz),true);
        echo($tzjd);
        // var_dump($tzjd);
        $tzname = $tzjd['timezoneId'];
        $offset = $tzjd['gmtOffset'];
        $yomsunrise = $tzjd['dates'][0]['sunrise'];
        $yomsunset = $tzjd['dates'][0]['sunset'];
        $yomsunrisedatetime = strtotime($yomsunrise);
        $yomsunsetdatetime = strtotime($yomsunset);
        echo("<br> Yom Sunrise: " . $yomsunrise . "<br> Yom Sunsent: " .  $yomsunset . "<br>");
        // echo("br");
        echo("<br> Yom Sunrise Date-Time: " . $yomsunrisedatetime . "<br> Yom Sunset Date-Time: " . $yomsunsetdatetime . "<br>");
        $sunrisesec = $yomsunrisedatetime+$offset;
        $sunsetsec = $yomsunsetdatetime+$offset;
        echo("<br>Offset: " . $offset . "<br>");
        echo("<br>Sunrise Sec: " . $sunrisesec . "<br>");
        echo("<br>Sunset Sec: " . $sunsetsec . "<br>");

        // Show Date & Country Info
        echo("Times for " . $yom_txt . '<br />');
        echo("$address <br />");
        echo($hebhebdate . "<br />");
        // Calculate Halakhic Times
        $timedisplay = "g:i A";

        // Calculate Shema
        $halachichour = ($sunsetsec - $sunrisesec)/12;
        $shemainseconds = $sunrisesec + ($halachichour*3);
        $shematext = '<span id="zmantitle">Latest Shemaʿ: </span>'.date($timedisplay, $shemainseconds).'<br />';
        echo("<br> Halachic Hour: $halachichour");
        echo("<br> Shema in Seconds: $shemainseconds <br>" );
        echo($shematext);

        // Calculate Earliest Minha
        $halachichour = ($sunsetsec - $sunrisesec)/12;
        $minchainseconds = $sunrisesec + ($halachichour*6.5);
        $minchatext = '<span id="zmantitle">Earliest Minḥa: </span>'.date($timedisplay, $minchainseconds).'<br />';
        echo($minchatext);

        // Calculate Plag haMinha
        $halachichour = ($sunsetsec - $sunrisesec)/12;
        $plaginseconds = $sunsetsec - ($halachichour*1.25);
        $plagtext = '<span id="zmantitle">Peleḡ HaMinḥa: </span>'.date($timedisplay, $plaginseconds).'<br />';
        echo($plagtext);

        // echo($address);
        // echo($halachichour);

        // Display Sunset
        echo '<span id="zmantitle">Sunset: </span>'.date($timedisplay,$yomsunsetdatetime).'<br />';

        // Display Debugging Info
        echo("<br>Debug Mode: <br>");
        echo("IP - Remote Address is: $ip <br>");
        // echo("Remote Addr is: $ip_remote <br>");
        echo("Latitude: $lat <br />Longitude: $long <br />");


        // Reset variables
        // $ip = '';
        // $tzurl = '';
        // $tzjd = '';
        // $
?>

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


<script>
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
</script>


<p>Your Location: <span id="location"></span></p>

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





        </div>
    </div>