<?php

/**
 * Template Name: Geolocation Test
 */

?> 

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDFrCM7Ao83pwu_avw-53o7cV0Ym7eLqpc" type="text/javascript"></script>

<?php 
get_header();
get_template_part( 'tpl/tpl', 'page-title' ); ?>


<div id="cn_content" class="container row">
    
    <div class="main">
        <div id="standard_blog_full" class="twelve columns alpha omega">

<div id="locationB"></div>
<!-- <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDFrCM7Ao83pwu_avw-53o7cV0Ym7eLqpc" type="text/javascript"></script> -->

<script type="text/javascript" defer>
    var x = document.getElementById("locationB");
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

            // Seems to be sufficient
            var city = res[5].formatted_address;

            // var city1 = res[4].address_components[1].short_name;
            // var city2 = res[4].address_components[2].short_name;
            console.log("Zip code is " + zip[1]);
            console.log("City: " + city);
            // console.log("City 1: " + city1);
            // console.log("City 2: " + city2);

        x.innerHTML = "Google Data - not being plugged in just yet <br>" +
        "Latitude: " + lat +
            "<br>Longitutde: " + long + 
            "<br>Zip: " + zip[1] + 
            "<br>City: " + city;
        });

        // console.log(lat, long, zip, city);
    }

    getLocationB();
    // showPositionB();
    // window.get_test = "I want my GET";
</script>

<br><br><br>

        <?php 
        $yom = strtotime("now");
        $yom_txt = date("M d, Y", $yom);
        $yom_ymd = date("Y-m-d", $yom);
        // echo("Yom YMD: " . $yom_ymd);

        // Get Hebrew Date from HebCal
        // more info: http://www.hebcal.com/home/219/hebrew-date-converter-rest-api
            $hebdate = file_get_contents("http://www.hebcal.com/converter/?cfg=json&gy=".date("Y",$yom)."&gm=".date("n",$yom)."&gd=".date("j",$yom)."&g2h=1");
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
        $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tzurl);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $tz = curl_exec($ch);
            curl_close($ch);
        $tzjd = json_decode(utf8_encode($tz),true);
        // var_dump($tzjd);
        $tzname = $tzjd['timezoneId'];
        $offset = $tzjd['gmtOffset'];
        $yomsunrise = $tzjd['dates'][0]['sunrise'];
        $yomsunset = $tzjd['dates'][0]['sunset'];
        $yomsunrisedatetime = strtotime($yomsunrise);
        $yomsunsetdatetime = strtotime($yomsunset);
        // echo("<br>" . $yomsunrise . "<br>" .  $yomsunset . "<br>");
        // echo("br");
        // echo("<br>" . $yomsunrisedatetime . "<br>" . $yomsunsetdatetime . "<br>");
        $sunrisesec = $yomsunrisedatetime+$offset;
        $sunsetsec = $yomsunsetdatetime+$offset;
        // echo("<br>Offset: " . $offset . "<br>");
        // echo("<br>Sunrise Sec: " . $sunrisesec . "<br>");
        // echo("<br>Sunset Sec: " . $sunsetsec . "<br>");

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